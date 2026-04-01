<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations_comptes', function (Blueprint $table) {

            // =============================================
            // 🔹 DESTINATION DIRECTION (versement vers la Direction)
            // =============================================
            $table->foreignId('id_compte_direction')
                ->nullable()
                ->after('id_destination')
                ->constrained('comptes_direction')
                ->nullOnDelete();

            // =============================================
            // 🔹 MODE DE VERSEMENT
            // =============================================
            $table->enum('mode', ['banque', 'especes', 'virement','chéque'])
                ->nullable()
                ->after('commentaire');
        });
    }

    public function down(): void
    {
        Schema::table('operations_comptes', function (Blueprint $table) {
            $table->dropForeign(['id_compte_direction']);
            $table->dropColumn(['id_compte_direction', 'mode']);
        });
    }
};
