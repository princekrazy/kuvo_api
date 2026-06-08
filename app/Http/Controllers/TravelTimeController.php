<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;


class TravelTimeController extends Controller
{
    public function fakecalculate(Request $request)
    {
        $request->validate([
            'big_bags' => 'required|numeric',
            'small_bags' => 'required|numeric',
            'price' => 'required|numeric',
            'no_passengers' => 'required|numeric',
        ]);
        return response()->json([
            'message' => 'Searching for Driver',
        ], 201);
    }
    public function calculate(Request $request)
    {
        $request->validate([
            'origin.lat' => 'required|numeric',
            'origin.lng' => 'required|numeric',
            'destination.lat' => 'required|numeric',
            'destination.lng' => 'required|numeric',
        ]);

        $apiKey = "AIzaSyB4Rh266gQDzI9eLAuW476g6vMGfED1g9U";

        $response = Http::withoutVerifying()->withHeaders([
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'routes.duration,routes.distanceMeters'
        ])->post(
            'https://routes.googleapis.com/directions/v2:computeRoutes',
            [
                'origin' => [
                    'location' => [
                        'latLng' => [
                            'latitude' => $request->origin['lat'],
                            'longitude' => $request->origin['lng'],
                        ],
                    ],
                ],
                'destination' => [
                    'location' => [
                        'latLng' => [
                            'latitude' => $request->destination['lat'],
                            'longitude' => $request->destination['lng'],
                        ],
                    ],
                ],
                'travelMode' => 'DRIVE',
                'routingPreference' => 'TRAFFIC_AWARE',
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Google API error',
                'details' => $response->json()
            ], 500);
        }

        $data = $response->json();

        return response()->json([
            'duration' => $data['routes'][0]['duration'],
            'distanceMeters' => $data['routes'][0]['distanceMeters'],
        ]);
    }
}
