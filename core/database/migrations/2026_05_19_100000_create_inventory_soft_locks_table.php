<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_soft_locks')) {
            return;
        }

        Schema::create('inventory_soft_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('variant_key', 128)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 128);
            $table->unsignedInteger('qty')->default(1);
            $table->string('status', 32)->default('reserved');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('item_id');
            $table->index('session_id');
            $table->index('expires_at');
            $table->index('status');
            $table->index(['item_id', 'variant_key', 'status']);

            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_soft_locks');
    }
};
