<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapsController extends Controller
{
    public function resolve(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $response = Http::withOptions([
                'allow_redirects' => [
                    'track_redirects' => true,
                ],
            ])->get($request->url);

            $finalUrl = $response->effectiveUri();

            return response()->json([
                'final_url' => (string) $finalUrl,
                'coordinates' => $this->extractCoordinates((string) $finalUrl),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to resolve link',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function extractCoordinates(string $url)
    {
        if (
            preg_match(
                '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
                $url,
                $matches
            )
        ) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        if (
            preg_match(
                '/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/',
                $url,
                $matches
            )
        ) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        return null;
    }
}