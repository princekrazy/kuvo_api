<?php
// database/migrations/xxxx_xx_xx_create_ride_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('size')->nullable();
            $table->string('driver_lat')->nullable();
            $table->string('driver_lng')->nullable();
            $table->string('origin_lat')->nullable();
            $table->string('origin_lng')->nullable();
            $table->string('destination_lat')->nullable();
            $table->string('destination_lng')->nullable();
            $table->string('distance_km')->nullable();
            $table->string('estimated_minutes')->nullable();
            $table->string('status')->default("pending");
            $table->decimal('fare', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
