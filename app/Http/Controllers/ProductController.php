<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function display()
    {
        return inertia('products/Display');
    }
}
