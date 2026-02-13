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
        Schema::table('vente_litres', function (Blueprint $table) {

            // 🔹 volume en litres (nullable pour compatibilité)
            $table->decimal('volume', 15, 2)
                  ->nullable()
                  ->after('qte_vendu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vente_litres', function (Blueprint $table) {

            $table->dropColumn('volume');
        });
    }
};
