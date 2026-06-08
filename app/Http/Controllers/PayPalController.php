<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayPalService;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Models\Driver;
class PayPalController extends Controller
{
    public function createOrder(Request $request, PayPalService $paypal)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $paypalRequest = new OrdersCreateRequest();

        $paypalRequest->prefer('return=representation');

        $paypalRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => number_format($request->amount, 2, '.', '')
                ]
            ]]
        ];

        try {
            $response = $paypal->client()->execute($paypalRequest);

            return response()->json([
                'orderID' => $response->result->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


  public function captureOrder(Request $request, PayPalService $paypal)
{
    $request->validate([
        'orderID' => 'required|string',
        'id' => 'required|integer'
    ]);

    try {
        $captureRequest = new OrdersCaptureRequest(
            $request->orderID
        );

        $captureRequest->prefer('return=representation');

        $response = $paypal->client()->execute($captureRequest);

        if ($response->result->status !== 'COMPLETED') {
            return response()->json([
                'success' => false
            ], 400);
        }

        $capture =
            $response->result
                ->purchase_units[0]
                ->payments
                ->captures[0];

        $amount = $capture->amount->value;

        // VERY IMPORTANT:
        // Update wallet ONLY after successful capture


         $driver = Driver::findOrFail($request->id);

        $driver->increment('wallet_balance', $amount);
        $wallet = $driver->wallet_balance;

        // Save transaction history
        // Transaction::create([...]);

        return response()->json([
            'success' => true,
            'amount' => $wallet,
            'transaction_id' => $capture->id
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}