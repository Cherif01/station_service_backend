<?php

namespace App\Modules\Settings\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RoleFilterService
{
    /**
     * Applique un filtrage sécurisé basé sur le rôle
     *
     * Options possibles :
     * - station_relation : string|null  (ex: 'station')
     * - pompiste_column  : string|null  (ex: 'id_pompiste')
     */
    public static function apply(Builder $query, array $options = []): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $table = $model->getTable();

        $stationRelation = $options['station_relation'] ?? null;
        $pompisteColumn  = $options['pompiste_column']  ?? null;

        switch ($user->role) {

            /**
             * 🔥 SUPER ADMIN
             * - aucune restriction
             */
            case 'super_admin':
                return $query;

            /**
             * 🔵 SUPERVISEUR
             * - filtrage via relation station → ville
             */
            case 'superviseur':

                if (
                    ! $stationRelation ||
                    ! method_exists($model, $stationRelation) ||
                    ! $user->station
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id_ville', $user->station->id_ville);
                });

            /**
             * 🟡 ADMIN / GERANT
             * - uniquement sa station
             */
            case 'admin':
            case 'gerant':

                if (
                    ! $stationRelation ||
                    ! method_exists($model, $stationRelation) ||
                    ! $user->id_station
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id', $user->id_station);
                });

            /**
             * 🔴 POMPISTE
             * - UNIQUEMENT ses données personnelles
             * - jamais sur stations
             */
            case 'pompiste':

                if (
                    ! $pompisteColumn ||
                    ! Schema::hasColumn($table, $pompisteColumn)
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where($pompisteColumn, $user->id);

            /**
             * ❌ AUTRES RÔLES
             */
            default:
                return $query->whereRaw('1 = 0');
        }
    }
}
