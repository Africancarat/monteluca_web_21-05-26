<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('consultant_slots')->restrictOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->string('event_type'); // engagement, bespoke, etc.
            $table->enum('booking_type', ['virtual', 'store_visit'])->default('virtual');
            $table->string('store_location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('google_event_id')->nullable();
            $table->timestamp('starts_at');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
