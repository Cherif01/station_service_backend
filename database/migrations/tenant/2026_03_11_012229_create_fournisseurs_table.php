<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {

            $table->id();

            $table->string('raison_sociale');

            $table->string('nom_complet')->nullable();

            $table->string('telephone')->nullable();

            $table->string('email')->nullable();

            $table->text('adresse')->nullable();

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};