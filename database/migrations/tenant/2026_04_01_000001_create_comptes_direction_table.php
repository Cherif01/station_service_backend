<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes_direction', function (Blueprint $table) {
            $table->id();

            // =============================================
            // 🔹 MÉTIER
            // =============================================
            $table->string('numero', 50)->unique();
            $table->string('libelle', 100)->nullable();
            $table->string('commentaire', 255)->nullable();

            $table->decimal('solde_initial', 15, 2)->default(0);

            // =============================================
            // 🔹 AUDIT
            // =============================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes_direction');
    }
};
