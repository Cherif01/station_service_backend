<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_charges', function (Blueprint $table) {
            $table->dropForeign(['id_station']);
            $table->dropColumn('id_station');
        });
    }

    public function down(): void
    {
        Schema::table('operation_charges', function (Blueprint $table) {
            $table->foreignId('id_station')
                ->after('id')
                ->constrained('stations')
                ->cascadeOnDelete();
        });
    }
};
