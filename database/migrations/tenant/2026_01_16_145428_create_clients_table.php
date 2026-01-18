<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            /**
             * =========================
             * MÉTIER
             * =========================
             */
            $table->foreignId('id_station')
                ->constrained('stations')
                ->cascadeOnDelete();

            $table->string('nom_complet');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();

            /**
             * Client actif / inactif
             */
            $table->boolean('status')->default(true);

            /**
             * =========================
             * AUDIT
             * =========================
             */
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
        Schema::dropIfExists('clients');
    }
};
