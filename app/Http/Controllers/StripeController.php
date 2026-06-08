<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->amount;

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Wallet Top-Up',
                        ],
                        'unit_amount' => intval($amount * 100), // cents
                    ],
                    'quantity' => 1,
                ]
            ],
            'success_url' => env('CLIENT_URL') . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('CLIENT_URL') . '/cancel',
        ]);

        return response()->json([
            'url' => $session->url
        ]);
    }
}