<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();

            // Relations métier
            $table->foreignId('id_pompe')
                  ->constrained('pompes')
                  ->cascadeOnDelete();

            $table->foreignId('id_pompiste')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('id_station')
                  ->constrained('stations')
                  ->cascadeOnDelete();

            // État
            $table->boolean('status')->default(true);

            // Audit
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
        Schema::dropIfExists('affectations');
    }
};
