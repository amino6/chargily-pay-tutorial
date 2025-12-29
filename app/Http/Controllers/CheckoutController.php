<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'product_name' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        try {
            $result = $this->checkoutService->initiateCheckout([
                'amount' => $data['amount'],
                'description' => $data['product_name'],
                'locale' => 'ar',
                'currency' => 'dzd',
                'success_url' => route('success'),
                'failure_url' => route('failure'),
            ]);

            return inertia()->location($result['checkout']->getUrl());
        } catch (\Exception $e) {
            Log::error('Failed to initiate checkout', [
                'error' => $e,
            ]);

            throw ValidationException::withMessages([
                'default' => 'Something went wrong!',
            ]);
        }
    }

    public function success(Request $request)
    {
        $checkoutId = $request->input('checkout_id');

        if (! $checkoutId) {
            return '<h1 style="color: red;">Invalid request!</h1>';
        }

        $order = Order::where('checkout_id', $checkoutId)->firstOrFail();

        if ($order->status === 'paid') {
            return '<h1>Success!</h1>';
        }

        return '<h1 style="color: red;">Order not paid!</h1>';
    }

    public function failure()
    {
        return '<h1 style="color: red;">failure</h1>';
    }

    public function webhook()
    {
        try {
            $result = $this->checkoutService->processWebhook();

            return response()->json([
                'status' => $result['success'],
                'message' => $result['message'],
            ], $result['status_code']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Webhook request',
            ], 403);
        }
    }
}
