<?php

return [
    'mode' => env('CHARGILY_PAY_MODE', 'test'),
    'public_key' => env('CHARGILY_PAY_PUBLIC_KEY'),
    'secret_key' => env('CHARGILY_PAY_SECRET_KEY'),
];
