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
        Schema::create('creances', function (Blueprint $table) {

            $table->id();

            // 🔹 Liaison principale avec la vente
            $table->foreignId('id_init_vente')
                ->constrained('init_ventes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // 🔹 Données métier
            $table->date('date')->nullable();

            $table->decimal('quantite', 15, 2)->default(0);
            $table->decimal('prix_unitaire', 15, 2)->default(0);
            $table->decimal('montant', 15, 2)->default(0);

            $table->string('commentaire')->nullable();

            // 🔹 Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creances');
    }
};
