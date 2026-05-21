<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('items', 'pdp_shape_variants')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            $table->json('pdp_shape_variants')->nullable()->after('pdp_metal_variants');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('items', 'pdp_shape_variants')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('pdp_shape_variants');
        });
    }
};
