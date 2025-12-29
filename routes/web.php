<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('pay', [ProductController::class, 'display'])
    ->name('products.display');

Route::post('checkout', [CheckoutController::class, 'checkout'])
    ->name('checkout');

Route::get('success', [CheckoutController::class, 'success'])
    ->name('success');

Route::get('failure', [CheckoutController::class, 'failure'])
    ->name('failure');

Route::post('chargilypay/webhook', [CheckoutController::class, 'webhook'])
    ->name('chargilypay.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
});

require __DIR__.'/settings.php';
