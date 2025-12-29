# Integrate Chargily Pay with Laravel

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Tutorial](https://img.shields.io/badge/Read-Full%20Tutorial-indigo)](https://developerbytes.net/posts/laravel-algerian-payment-gateway-integration-chargily-pay/)

This repository contains the complete source code for the tutorial: **"Integrate Chargily Pay with Laravel"**.

In this project, we implement a full payment flow to accept **CIB and Edahabia cards** using the Chargily Pay V2 gateway, featuring a modern stack with Laravel 12 and Inertia.js (Vue 3).

## Full Tutorial

## **For a detailed step-by-step tutorial, visit the official post:** 👉 [Integrate Chargily Pay with Laravel to accept CIB and Edahabia cards](https://developerbytes.net/posts/laravel-algerian-payment-gateway-integration-chargily-pay/)

## Installation

### 1. Clone & Install

```bash
git clone git@github.com:amino6/chargily-pay-tutorial.git
cd chargily-pay-tutorial
composer install
npm install
```

### 2. Environment Setup

Create your .env file and add your Chargily API keys:

```bash
cp .env.example .env
php artisan key:generate
```

Edit your **.env**:

```env
CHARGILY_PAY_MODE=test
CHARGILY_PAY_PUBLIC_KEY=your_public_key_here
CHARGILY_PAY_SECRET_KEY=your_secret_key_here
```

### 3. Database & Migrations

```bash
php artisan migrate
```

### 4. Local Development

```bash
composer dev
```

### 5. Webhook Testing (Locally)

To test webhooks locally, you must use a tool like **Ngrok** to expose your local server:

1. Run **ngrok http 8000**.
2. Update your webhook URL in the **Chargily** Dashboard to https://your-ngrok-url.ngrok-free.app/chargilypay/webhook.
3. For more details on Ngrok setup, check the blog post.

## License

This project is licensed under the MIT License.
