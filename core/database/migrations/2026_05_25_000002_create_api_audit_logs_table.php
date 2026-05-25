<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 2048);
            $table->string('method', 10);
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('latency_ms');
            $table->char('request_hash', 64)->nullable();
            $table->timestamp('at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_audit_logs');
    }
};
