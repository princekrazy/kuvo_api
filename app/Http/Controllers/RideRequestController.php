<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RideRequest;
use App\Events\RideStatusUpdated;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Str;

class RideRequestController extends Controller
{
    /**
     * POST create_ride
     * requires: user_id, amount
     */
    public function acceptRide(Request $request)
{
    $ride = RideRequest::findOrFail($request->ride_id);

    $ride->update([
        'status' => 'accepted',
        'driver_id' => $request->driver_id,
    ]);

    // 🔥 THIS triggers real-time update
    event(new RideStatusUpdated($ride));

    return response()->json([
        'message' => 'Ride accepted',
        'ride' => $ride
    ]);
}
public function cancelRide(Request $request)
{
    $ride = RideRequest::findOrFail($request->ride_id);

    $ride->update([
        'driver_id' => null,
        'status' => 'cancelled',
    ]);

    event(new RideStatusUpdated($ride));

    return response()->json([
        'message' => 'Ride cancelled',
        'ride' => $ride
    ]);
}
    public function createRide(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'lat' => 'required|integer',
            'long' => 'required|integer',
            'size' => 'required'

        ]);

        $ride = RideRequest::create([
            'user_id' => $data['user_id'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'size' => $data['size'],
            'amount' => $data['amount'],
            'ride_id' => Str::uuid(), // auto generate
        ]);

        return response()->json([
            'message' => 'Ride created',
            'ride' => $ride
        ]);
    }

    /**
     * POST driver_found
     * requires: ride_id, driver_id
     */
    public function driverFound(Request $request)
    {
        $data = $request->validate([
            'ride_id' => 'required|string',
            'driver_id' => 'required|integer',
        ]);

        $ride = RideRequest::where('ride_id', $data['ride_id'])->firstOrFail();

        $ride->update([
            'driver_id' => $data['driver_id'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Driver assigned',
            'ride' => $ride
        ]);
    }

    /**
     * POST complete_ride
     * requires: ride_id
     */
    public function completeRide(Request $request)
    {
        $data = $request->validate([
            'ride_id' => 'required|integer',
        ]);

        $ride = RideRequest::where('id', $data['ride_id'])->firstOrFail();
        $driver_id = $ride->driver_id;
        $driver = Driver::where('id', $driver_id)->firstOrFail();
        $commission = round($ride->fare * 0.10, 2);
        $driver->decrement('wallet_balance', $commission);

        // optional: mark complete before delete
        $ride->update([
            'status' => 'complete',
        ]);

        // delete immediately
        // $ride->delete();

        return response()->json([
            'message' => 'Ride completed and removed.',
            'commission' => $commission,
            'wallet' => $driver->wallet_balance
        ]);
    }
    public function currentRide(string $ride_id)
    {
       

        $ride = RideRequest::where('id', $ride_id)->firstOrFail();
        $user_id = $ride->driver_id;
        $user = User::where('id', $user_id)->firstOrFail();
        $driver = Driver::where('user_id', $user_id)->firstOrFail();
        return response()->json([
            'driver_lng' => $ride->driver_lng,
            'driver_lat' => $ride->driver_lat,
            'status' => $ride->status,
            'name' => $user->name,
            'phone'=> $user->phone_number,
            'vehicle'=>$driver->vehicle_description,
            'license'=>$driver->license_number,
            'size' => $driver->size,
        ]);
    }
     public function getRider(string $user_id)
    {
       

        $user = User::where('id', $user_id)->firstOrFail();
        return response()->json([
            'name' => $user->name,
            'phone' => $user->phone_number
        ]);
    }
    public function availableRides(Request $request)
    {
         $data = $request->validate([
            'size' => 'required', 
        ]);
        $rides = RideRequest::orderBy('created_at', 'desc')
    ->where('status', 'pending')
    ->where('size', $data['size'])
    ->get();

        return response()->json([
            // 'count' => $rides->count(),
            'rides' => $rides
        ]);
    }
}
