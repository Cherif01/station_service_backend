<?php
namespace App\Modules\Settings\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoleFilterService
{
    /**
     * Applique un filtrage basé UNIQUEMENT sur les relations métier
     *
     * Options :
     * - station_relation : relation vers Station (ex: 'station')
     * - pompiste_column  : colonne user (ex: 'id', 'id_pompiste')
     */
    public static function apply(Builder $query, array $options = []): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $stationRelation = $options['station_relation'] ?? 'station';
        $pompisteColumn  = $options['pompiste_column'] ?? 'id_pompiste';

        switch ($user->role) {

            /**
                 * 🔥 SUPER ADMIN
                 * - aucune restriction
                 */
            case 'super_admin':
                return $query;

            /**
                 * 🔵 SUPERVISEUR
                 * - voit tout ce qui se passe dans SA VILLE
                 * - filtrage via relation station → ville
                 */
            case 'superviseur':
                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id_ville', $user->station->id_ville);
                });

            /**
                 * 🟡 ADMIN / GERANT
                 * - voit uniquement SA STATION
                 */
            case 'admin':
            case 'gerant':
                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id', $user->id_station);
                });

            /**
                 * 🔴 POMPISTE
                 * - voit uniquement SES DONNÉES
                 */
            case 'pompiste':
                // 🔒 Le pompiste ne voit aucune station
                return $query->whereRaw('1 = 0');

            /**
                 * ❌ AUTRES
                 */
            default:
                return $query->whereRaw('1 = 0');
        }
    }
}
