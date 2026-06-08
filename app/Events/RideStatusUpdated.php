<?php
namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RideStatusUpdated implements ShouldBroadcast
{
    public $ride;

    public function __construct(RideRequest $ride)
    {
        $this->ride = $ride;
    }

    public function broadcastOn()
    {
        // private channel per ride
        return new Channel('ride.' . $this->ride->id);
    }

    public function broadcastAs()
    {
        return 'ride.status.updated';
    }
}