<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ligne_ventes', function (Blueprint $table) {

            $table->decimal('prix_unitaire', 15, 2)
                ->nullable()
                ->after('qte_vendu');

        });
    }

    public function down(): void
    {
        Schema::table('ligne_ventes', function (Blueprint $table) {

            $table->dropColumn('prix_unitaire');

        });
    }
};
