<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_categories', function (Blueprint $table) {

            $table->id();

            $table->string('libelle');

            $table->boolean('is_fixed')->default(true);

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
        Schema::dropIfExists('charge_categories');
    }
};