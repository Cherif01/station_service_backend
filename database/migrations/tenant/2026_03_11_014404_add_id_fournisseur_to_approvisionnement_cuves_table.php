<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {

            $table->foreignId('id_fournisseur')
                ->nullable()
                ->after('id_cuve')
                ->constrained('fournisseurs')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {

            $table->dropForeign(['id_fournisseur']);
            $table->dropColumn('id_fournisseur');

        });
    }
};