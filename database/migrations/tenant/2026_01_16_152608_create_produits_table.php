<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_station')
                ->constrained('stations')
                ->cascadeOnDelete();

            $table->string('libelle');
            $table->string('reference')->unique();

            $table->decimal('qte_initiale', 15, 2)->default(0);
            $table->decimal('qte_actuelle', 15, 2)->default(0);

            $table->decimal('prix_unitaire', 15, 2)->default(0);
            $table->decimal('seuil_alerte', 15, 2)->default(0);

            $table->boolean('status')->default(true);

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
        Schema::dropIfExists('produits');
    }
};
