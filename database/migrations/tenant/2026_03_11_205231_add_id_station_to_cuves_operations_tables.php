<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * =============================
         * APPROVISIONNEMENT CUVES
         * =============================
         */
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {

            $table->foreignId('id_station')
                ->after('id_cuve')
                ->constrained('stations')
                ->cascadeOnDelete();
        });

        /**
         * =============================
         * PERTES CUVES
         * =============================
         */
        Schema::table('pertes_cuves', function (Blueprint $table) {

            $table->foreignId('id_station')
                ->after('id_cuve')
                ->constrained('stations')
                ->cascadeOnDelete();
        });

        /**
         * =============================
         * JAUGEAGE CUVES
         * =============================
         */
        Schema::table('jaugeage_cuves', function (Blueprint $table) {

            $table->foreignId('id_station')
                ->after('id_cuve')
                ->constrained('stations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {

            $table->dropForeign(['id_station']);
            $table->dropColumn('id_station');
        });

        Schema::table('pertes_cuves', function (Blueprint $table) {

            $table->dropForeign(['id_station']);
            $table->dropColumn('id_station');
        });

        Schema::table('jaugeage_cuves', function (Blueprint $table) {

            $table->dropForeign(['id_station']);
            $table->dropColumn('id_station');
        });
    }
};