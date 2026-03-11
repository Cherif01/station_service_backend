<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Supprimer la clé étrangère de id_station si elle existe
        if (Schema::hasColumn('cuves', 'id_station')) {
            $foreignKey = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'cuves'
                  AND COLUMN_NAME = 'id_station'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");

            if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                Schema::table('cuves', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                });
            }
        }

        // 2. Supprimer les colonnes inutiles si elles existent
        Schema::table('cuves', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach ([
                'reference',
                'id_station',
                'type_cuve',
                'qt_initial',
                'qt_actuelle',
                'pu_unitaire',
                'status',
            ] as $column) {
                if (Schema::hasColumn('cuves', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuves', function (Blueprint $table) {
            if (! Schema::hasColumn('cuves', 'reference')) {
                $table->string('reference')->nullable()->after('libelle');
            }

            if (! Schema::hasColumn('cuves', 'id_station')) {
                $table->unsignedBigInteger('id_station')->nullable()->after('reference');
            }

            if (! Schema::hasColumn('cuves', 'type_cuve')) {
                $table->string('type_cuve')->nullable()->after('id_station');
            }

            if (! Schema::hasColumn('cuves', 'qt_initial')) {
                $table->decimal('qt_initial', 15, 2)->default(0)->after('type_cuve');
            }

            if (! Schema::hasColumn('cuves', 'qt_actuelle')) {
                $table->decimal('qt_actuelle', 15, 2)->default(0)->after('qt_initial');
            }

            if (! Schema::hasColumn('cuves', 'pu_unitaire')) {
                $table->decimal('pu_unitaire', 15, 2)->default(0)->after('pu_vente');
            }

            if (! Schema::hasColumn('cuves', 'status')) {
                $table->boolean('status')->default(true)->after('pu_unitaire');
            }
        });

        // Recréer la clé étrangère si id_station existe
        if (Schema::hasColumn('cuves', 'id_station')) {
            $foreignKey = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'cuves'
                  AND COLUMN_NAME = 'id_station'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");

            if (! $foreignKey) {
                Schema::table('cuves', function (Blueprint $table) {
                    $table->foreign('id_station')
                        ->references('id')
                        ->on('stations')
                        ->cascadeOnDelete();
                });
            }
        }
    }
};