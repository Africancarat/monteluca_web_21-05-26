<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gold_attributes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_id');

            $table->string('metal_type')->nullable();
            $table->string('gold_karat')->nullable();

            $table->string('certificate_number')->nullable();
            $table->string('certificate_pdf')->nullable();
            $table->string('certificate_image')->nullable();

            $table->boolean('status')->default(1);

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_attributes');
    }
};
