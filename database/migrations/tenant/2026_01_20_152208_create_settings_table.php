<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            /**
             * =================================================
             * 🔹 PORTÉE DU PARAMÈTRE
             * =================================================
             * null  = paramètre global
             * value = paramètre spécifique à une station
             */
            $table->foreignId('id_station')
                ->nullable()
                ->constrained('stations')
                ->cascadeOnDelete();

            /**
             * =================================================
             * 🔹 CLÉ / VALEUR
             * =================================================
             */
            $table->string('cle', 100);
            $table->string('valeur')->nullable();
            $table->text('description')->nullable();

            /**
             * =================================================
             * 🔹 AUDIT
             * =================================================
             */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /**
             * =================================================
             * 🔹 CONTRAINTE D’UNICITÉ
             * =================================================
             * Une clé ne peut exister qu’une seule fois
             * par station (ou globalement si id_station = null)
             */
            $table->unique(['id_station', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
