<?php

use App\Models\Order;
use App\Services\CheckoutService;
use Chargily\ChargilyPay\Elements\CheckoutElement;
use Chargily\ChargilyPay\Elements\WebhookElement;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    /*
     * Mockery::mock(CheckoutService::class)->makePartial(): Creates a "partial mock" of our service.
     * A partial mock allows the original class methods to be called unless they are explicitly overridden
     * This is useful for testing our controller's logic while mocking only the external API calls.
    */
    $this->checkoutService = Mockery::mock(CheckoutService::class)->makePartial();

    /*
     * $this->app->instance(...): This binds our mocked service instance into Laravel's service container.
     * When the CheckoutController is resolved, it will receive this mock instead of the real service.
     */
    $this->app->instance(CheckoutService::class, $this->checkoutService);
});

describe('checkout method', function () {
    test('successfully creates checkout and redirects to payment URL', function () {
        $checkoutId = 'checkout_'.str()->random(5);
        $redirect_url = 'https://pay.chargily.com/test-checkout-url';

        $checkoutElementMock = Mockery::mock(CheckoutElement::class);
        $checkoutElementMock->shouldReceive('getUrl')
            ->once()
            ->andReturn($redirect_url);
        $checkoutElementMock->shouldReceive('getId')
            ->once()
            ->andReturn($checkoutId);

        /*
         * Mocking the CheckoutService class to avoid making a real HTTP request to the Chargily Pay API.
         *
         * - $this->checkoutService->shouldReceive('createCheckout'): Sets up the expected method to be called.
         * - once(): We expect this method to be called exactly once. The test will fail if it's called more or fewer times.
         * - andReturn($checkoutElementMock): Returns the mocked CheckoutElement instance when `createCheckout` is called in the controller,
         * simulating a successful API response.
        */
        $this->checkoutService
            ->shouldReceive('createCheckout')
            ->once()
            ->andReturn($checkoutElementMock);

        $response = $this->post(route('checkout'), [
            'product_name' => 'Test Product',
            'amount' => 5000,
        ]);

        $response->assertRedirect();
        $response->assertRedirectContains($redirect_url);

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'checkout_id' => $checkoutId,
            'amount' => 5000,
        ]);
    });

    test('throws validation exception when checkout creation fails', function () {
        /*
         * Mocking the CheckoutService to simulate a failure scenario from the Chargily Pay API.
         *
         * - shouldReceive('createCheckout'): Sets up the expected method to be called.
         * - once(): We expect this method to be called exactly once.
         * - andReturn(null): Simulates a failed API call by returning null, which the controller should handle as an error.
        */
        $this->checkoutService
            ->shouldReceive('createCheckout')
            ->once()
            ->andReturn(null);

        $this->post(route('checkout'), [
            'product_name' => 'Test Product',
            'amount' => 5000,
        ])->assertSessionHasErrors(['default']);
    });
});

describe('webhook method', function () {
    test('processes webhook successfully for paid status', function () {
        $checkoutId = 'checkout_'.str()->random(5);

        Order::create([
            'checkout_id' => $checkoutId,
            'amount' => 5000,
        ]);

        $checkoutElementMock = Mockery::mock(CheckoutElement::class);
        $checkoutElementMock->shouldReceive('getId')
            ->andReturn($checkoutId);
        $checkoutElementMock->shouldReceive('getStatus')
            ->andReturn('paid');
        $checkoutElementMock->shouldReceive('getUpdatedAt')
            ->andReturn(now());

        $webhookElementMock = Mockery::mock(WebhookElement::class);
        $webhookElementMock->shouldReceive('getData')
            ->once()
            ->andReturn($checkoutElementMock);

        /*
         * Mocking the CheckoutService to simulate receiving a valid webhook from Chargily Pay.
         *
         * - shouldReceive('getWebhook'): Sets up the expected method to be called.
         * - once(): We expect the method to be called exactly once.
         * - andReturn($webhookElementMock): Returns a mocked WebhookElement, simulating a valid, signed request from Chargily Pay.
        */
        $this->checkoutService
            ->shouldReceive('getWebhook')
            ->once()
            ->andReturn($webhookElementMock);

        $response = $this->post(route('chargilypay.webhook'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Payment has been completed',
        ]);

        // Verify order was updated in database
        $this->assertDatabaseHas('orders', [
            'checkout_id' => $checkoutId,
            'status' => 'paid',
        ]);
    });

    test('returns 403 for invalid webhook request', function () {
        /*
         * Mocking the CheckoutService to simulate an invalid webhook request.
         *
         * - shouldReceive('getWebhook'): Sets up the expected method.
         * - once(): We expect the method to be called exactly once.
         * - andReturn(null): Simulates a failure in webhook validation, by making the method return null.
        */
        $this->checkoutService
            ->shouldReceive('getWebhook')
            ->once()
            ->andReturn(null);

        $response = $this->post(route('chargilypay.webhook'));

        $response->assertStatus(403);
        $response->assertJson([
            'status' => false,
            'message' => 'Invalid Webhook request',
        ]);
    });
});
