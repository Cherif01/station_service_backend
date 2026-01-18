<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('init_ventes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_client')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            $table->foreignId('id_affectation')
                ->constrained('affectations')
                ->nullable()
                ->cascadeOnDelete();

            $table->string('reference')->unique();

            // 0 = en attente, 1 = validée, 2 = annulée
            $table->tinyInteger('status')->default(0);

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
        Schema::dropIfExists('init_ventes');
    }
};
