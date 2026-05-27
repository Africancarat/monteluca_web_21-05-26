<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('referral_code')->unique();
            $table->decimal('discount_percent', 8, 2)->default(5);
            $table->decimal('cashback_percent', 8, 2)->default(5);
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('total_usage')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};
