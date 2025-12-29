<?php

namespace App\Services;

use App\Models\Order;
use Chargily\ChargilyPay\Auth\Credentials;
use Chargily\ChargilyPay\ChargilyPay;
use Chargily\ChargilyPay\Elements\CheckoutElement;
use Chargily\ChargilyPay\Elements\WebhookElement;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    protected ChargilyPay $client;

    public function __construct()
    {
        $this->client = new ChargilyPay(new Credentials([
            'mode' => config('chargily-pay.mode'),
            'public' => config('chargily-pay.public_key'),
            'secret' => config('chargily-pay.secret_key'),
        ]));
    }

    /**
     * Initiate a checkout session and create an order.
     *
     * @param  array  $data  Checkout data.
     * @return array{checkout: CheckoutElement, order: Order}
     *
     * @throws \Exception
     */
    public function initiateCheckout(array $data): array
    {
        $checkout = $this->createCheckout($data);

        if (! $checkout) {
            throw new \Exception('Failed to create checkout session');
        }

        $order = Order::create([
            'checkout_id' => $checkout->getId(),
            'amount' => $data['amount'],
        ]);

        return [
            'checkout' => $checkout,
            'order' => $order,
        ];
    }

    /**
     * Create a new checkout.
     */
    public function createCheckout(array $data): ?CheckoutElement
    {
        return $this->client->checkouts()->create($data);
    }

    /**
     * Get a checkout by ID.
     */
    public function getCheckout(string $checkoutId): ?CheckoutElement
    {
        return $this->client->checkouts()->get($checkoutId);
    }

    /**
     * Get webhook data.
     */
    public function getWebhook(): ?WebhookElement
    {
        return $this->client->webhook()->get();
    }

    /**
     * Process webhook data and update order accordingly.
     *
     * @return array{success: bool, message: string, status_code: int}
     */
    public function processWebhook(): array
    {
        $webhook = $this->getWebhook();

        if (! $webhook) {
            return [
                'success' => false,
                'message' => 'Invalid Webhook request',
                'status_code' => 403,
            ];
        }

        $checkout = $webhook->getData();

        if (empty($checkout) || ! ($checkout instanceof CheckoutElement)) {
            return [
                'success' => false,
                'message' => 'Invalid Webhook request',
                'status_code' => 403,
            ];
        }

        $checkoutId = $checkout->getId();
        $checkoutStatus = $checkout->getStatus();
        $order = Order::where('checkout_id', $checkoutId)->first();

        if (! $order) {
            return [
                'success' => false,
                'message' => 'Invalid Webhook request',
                'status_code' => 403,
            ];
        }

        // Already processed
        if ($order->status === 'paid') {
            return [
                'success' => true,
                'message' => 'Payment has been completed',
                'status_code' => 200,
            ];
        }

        // Update order based on checkout status
        if ($checkoutStatus === 'paid') {
            Order::where('checkout_id', $checkoutId)
                ->where('status', '!=', 'paid')
                ->update([
                    'status' => 'paid',
                    'paid_at' => $checkout->getUpdatedAt() ?? now(),
                ]);

            return [
                'success' => true,
                'message' => 'Payment has been completed',
                'status_code' => 200,
            ];
        } elseif ($checkoutStatus === 'failed' || $checkoutStatus === 'canceled' || $checkoutStatus === 'expired') {
            Order::where('checkout_id', $checkoutId)
                ->where('status', '!=', $checkoutStatus)
                ->update([
                    'status' => $checkoutStatus,
                ]);

            return [
                'success' => true,
                'message' => 'Payment has been canceled',
                'status_code' => 200,
            ];
        } else {
            Log::error('Unknown payment status received from webhook.', [
                'checkout_id' => $checkoutId,
                'order_id' => $order->id,
                'checkout_status' => $checkoutStatus,
                'webhook_data' => $checkout->toArray(),
            ]);

            return [
                'success' => true,
                'message' => 'Unknown payment status',
                'status_code' => 200,
            ];
        }
    }
}
