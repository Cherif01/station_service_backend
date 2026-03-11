<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_charges', function (Blueprint $table) {

            $table->id();

            $table->foreignId('id_station')
                ->constrained('stations')
                ->cascadeOnDelete();

            $table->foreignId('id_charge_category')
                ->constrained('charge_categories')
                ->cascadeOnDelete();

            $table->foreignId('id_compte')
                ->constrained('comptes')
                ->cascadeOnDelete();

            $table->decimal('montant',15,2);

            $table->text('commentaire')->nullable();

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
        Schema::dropIfExists('operation_charges');
    }
};