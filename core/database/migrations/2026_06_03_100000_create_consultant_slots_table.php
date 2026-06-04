<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_slots', function (Blueprint $table) {
            $table->id();
            $table->date('slot_date');
            $table->time('slot_time');
            $table->boolean('is_booked')->default(false);
            $table->timestamps();

            $table->unique(['slot_date', 'slot_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_slots');
    }
};
