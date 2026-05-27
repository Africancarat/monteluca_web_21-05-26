<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_codes', function (Blueprint $table) {
            if (Schema::hasColumn('referral_codes', 'title')) {
                $table->dropColumn('title');
            }
            if (! Schema::hasColumn('referral_codes', 'total_earned_cashback')) {
                $table->double('total_earned_cashback')->default(0)->after('total_usage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referral_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('referral_codes', 'title')) {
                $table->string('title')->nullable();
            }
            if (Schema::hasColumn('referral_codes', 'total_earned_cashback')) {
                $table->dropColumn('total_earned_cashback');
            }
        });
    }
};
