<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vente_produits_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_init_vente')
                ->constrained('init_ventes')
                ->cascadeOnDelete();

            $table->foreignId('id_produit')
                ->nullable()
                ->constrained('produits')
                ->nullOnDelete();

            $table->foreignId('id_service')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->decimal('qte_vendu', 15, 2)->nullable();
            $table->decimal('prix_unitaire', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_produits_services');
    }
};
