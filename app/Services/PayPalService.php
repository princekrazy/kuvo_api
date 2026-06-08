<?php

namespace App\Services;

use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalHttp\HttpClient;
use GuzzleHttp\Client;


class PayPalService
{
    public function client()
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        $environment = new SandboxEnvironment($clientId, $secret);

        return new PayPalHttpClient($environment);
    }
}