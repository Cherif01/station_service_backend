<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::dropIfExists('creances');

        Schema::create('creances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_station')
                ->constrained('stations')
                ->cascadeOnDelete();

            $table->foreignId('id_client')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('id_pompe')
                ->nullable()
                ->constrained('pompes')
                ->nullOnDelete();

            $table->decimal('qte', 15, 2);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('montant', 15, 2);

            $table->text('commentaire')->nullable();

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

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        Schema::dropIfExists('creances');
    }
};
