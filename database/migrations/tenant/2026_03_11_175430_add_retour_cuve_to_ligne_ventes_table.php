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
        Schema::table('ligne_ventes', function (Blueprint $table) {
            $table->decimal('retour_cuve', 15, 2)->default(0)->after('index_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_ventes', function (Blueprint $table) {
            $table->dropColumn('retour_cuve');
        });
    }
};