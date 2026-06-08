<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ride.{id}', function ($user, $id) {
    return true; // later you can restrict access
});
