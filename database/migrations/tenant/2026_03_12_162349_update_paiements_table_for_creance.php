<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('paiements')->truncate();

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['id_init_vente']);
            $table->dropColumn('id_init_vente');

            $table->foreignId('id_creance')
                ->after('id')
                ->constrained('creances')
                ->cascadeOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['id_creance']);
            $table->dropColumn('id_creance');

            $table->foreignId('id_init_vente')
                ->after('id')
                ->constrained('init_ventes')
                ->cascadeOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
