<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'product_name' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        dd($data);
        // we will implement this later after setting up chargily pay.
    }

    public function success()
    {
        return '<h1>Success</h1>';
    }

    public function failure()
    {
        return '<h1 style="color: red;">failure</h1>';
    }
}
