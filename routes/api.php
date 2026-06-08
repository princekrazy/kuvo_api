<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Driver;
use App\Models\RideRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\TravelTimeController;
use App\Http\Controllers\RideRequestController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\MapsController;
Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder']);
Route::post('/maps/resolve', [MapsController::class, 'resolve']);
Route::post('/travel-time', [TravelTimeController::class, 'calculate']);
Route::post('/available_rides', [RideRequestController::class, 'availableRides']);
Route::post('/create_ride', [RideRequestController::class, 'createRide']);
Route::post('/driver_found', [RideRequestController::class, 'driverFound']);
Route::post('/complete-ride', [RideRequestController::class, 'completeRide']);
Route::post('/ride/accept', [RideRequestController::class, 'acceptRide']);
Route::post('/ride/cancel', [RideRequestController::class, 'cancelRide']);
Route::get('/currentRide/{ride_id}', [RideRequestController::class, 'currentRide']);
Route::get('/rider/{user_id}', [RideRequestController::class, 'getRider']);

Route::post('/rides', function (Request $request) {
    $request->validate([
        'user_id' => 'required',
        'origin_lat' => 'required|numeric',
        'origin_lng' => 'required|numeric',
        'destination_lat' => 'required|numeric',
        'destination_lng' => 'required|numeric',
        'distance_km' => 'required|numeric',
        'estimated_minutes' => 'required|numeric',
        'size' => 'required',
    ]);

    // Pricing settings
  $rideType = $request->size;

$baseFare = $rideType === 'normal' ? 5.00 : 12.00;
$pricePerKm = 0.50;
$pricePerMinute = 0.05;

$distanceKm = $request->distance_km;
$estimatedMinutes = $request->estimated_minutes;

$fareAmount =
    $baseFare +
    ($distanceKm * $pricePerKm) +
    ($estimatedMinutes * $pricePerMinute);

$fareAmount = max($fareAmount, 3.00);

$fareAmount = round($fareAmount, 2);
   

    // Save ride
    $ride = RideRequest::create([
        'user_id' => $request->user_id,
        'origin_lat' => $request->origin_lat,
        'origin_lng' => $request->origin_lng,
        'size' => $request->size,
        'destination_lat' => $request->destination_lat,
        'destination_lng' => $request->destination_lng,
        'distance_km' => $distanceKm,
        'estimated_minutes' => $estimatedMinutes,
        'fare' => $fareAmount,
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Ride created successfully',
        'ride' => $ride,
        'fare' => $fareAmount,
    ], 201);
});



Route::post('/driver/register', function (Request $request) {

    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'phone_number' => 'required|unique:users',
        'password' => 'required|min:6',
        'id_number' => 'required|unique:drivers',
        'vehicle_description' => 'required|string',
        'license_number' => 'required|unique:drivers',
        'size' => 'required',

    ]);

    // 1️⃣ Create user record
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => Hash::make($request->password),
    ]);

    // 2️⃣ Create driver record linked to user
    $driver = Driver::create([
        'user_id' => $user->id,
        'id_number' => $request->id_number,
        'license_number' => $request->license_number ?? null,
        'vehicle_description' => $request->vehicle_description ?? null,
        'size' => $request->size ?? null,
    ]);

    return response()->json([
        'message' => 'Driver registered successfully',
        'driver' => $driver,
        'user' => $user,
    ], 201);
});

// ----------------------
// DRIVER LOGIN
// ----------------------
Route::post('/driver/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    // Check user exists and password is correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Check if user is a driver
    if (!$user->driver) {
        return response()->json(['message' => 'This account is not a driver'], 403);
    }

    $token = $user->createToken('driver_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'driver_id' => $user->id,
    ]);
});

// ----------------------
// USER SIGNUP / REGISTRATION
// ----------------------
Route::post('/user/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'phone_number' => 'required|unique:users',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => Hash::make($request->password),
    ]);
    $driver = Driver::create([
        'user_id' => $user->id,
        'id_number' => $user->id,
        'license_number' => null,
        'vehicle_description' => null,
    ]);

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        'driver' => $driver
    ], 201);
});

// ----------------------
// USER LOGIN
// ----------------------
Route::post('/driver/location', function (Request $request){
    $ride = RideRequest::find($request->ride_id);

    $ride->update([
        'driver_lat' => $request->lat,
        'driver_lng' => $request->lng,
    ]);

    return response()->json([
        'message' => 'Location updated successfully',
        'ride' => $ride
    ]);
});
Route::post('/accept-ride', function (Request $request){
    $ride = RideRequest::find($request->ride_id);

    $ride->update([
        'driver_id' => $request->driver_id,
        'status' => 'accepted',
    ]);

    return response()->json([
        'message' => 'Ride taken',
        'ride' => $ride
    ]);
});
Route::post('/cancel-ride', function (Request $request){
    $ride = RideRequest::find($request->ride_id);

    $ride->update([
        'driver_id' => null,
        'status' => 'canceled',
    ]);

    return response()->json([
        'message' => 'you have canceled on this ride',
        'ride' => $ride
    ]);
});
Route::post('/user/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('user_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user_id' => $user->id
    ]);
});
// Get driver details
Route::middleware('auth:sanctum')->get('/driver/me', function (Request $request) {
    $user = $request->user();

    // Make sure user is a driver
    if (!$user->driver) {
        return response()->json(['message' => 'Not a driver'], 403);
    }

    $driver = $user->driver; // Eager loaded driver info
    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'phone_number' => $user->phone_number,
        'id_number' => $driver->id_number,
        'vehicle_description' => $driver->vehicle_description,
        'license_number' => $driver->license_number,
        'wallet_balance' => $driver->wallet_balance,
        'size' => $driver->size
    ]);
});
// Update vehicle/license
Route::middleware('auth:sanctum')->post('/driver/update', function (Request $request) {
    $user = $request->user();

    if (!$user->driver) {
        return response()->json(['message' => 'Not a driver'], 403);
    }

    $validator = Validator::make($request->all(), [
        'vehicle_description' => 'required|string',
        'license_number' => 'required|string',
        'size' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $driver = $user->driver;
    $driver->vehicle_description = $request->vehicle_description;
    $driver->license_number = $request->license_number;
    $driver->size = $request->size;
    $driver->save();

    return response()->json([
        'message' => 'Driver info updated successfully',
        'driver' => $driver,
    ]);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
        'phone_number' => $request->user()->phone_number,
    ]);
});
