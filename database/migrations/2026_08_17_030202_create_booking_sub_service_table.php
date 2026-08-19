<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_sub_service', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->foreignId('sub_service_id')
                ->constrained('sub_services')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'booking_id',
                'sub_service_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_sub_service');
    }
};