<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetActiveStation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $stationId = null;

        /**
         * =================================================
         * 🔥 SUPER ADMIN
         * → station choisie via "Accéder"
         * =================================================
         */
        if ($user->role === 'super_admin') {

            $tenantDb  = $request->attributes->get('tenant_db', 'default');
            $stationId = Cache::get('station_active_user_' . $user->id . '_' . $tenantDb);
        }

        /**
         * =================================================
         * 🔵 ADMIN / 🟡 GÉRANT / 🟣 SUPERVISEUR
         * → station via id_station ou affectation active
         * =================================================
         */
        elseif (in_array($user->role, ['admin', 'gerant', 'superviseur'])) {

            $stationId = $user->id_station
                ?? optional($user->activeAffectation())->id_station;
        }

        /**
         * =================================================
         * 🔴 POMPISTE
         * → station via affectation active
         * =================================================
         */
        elseif ($user->role === 'pompiste') {

            $stationId = optional($user->activeAffectation())->id_station;
        }

        /**
         * =================================================
         * 🔒 Injection CONTEXTUELLE
         * =================================================
         */
        $request->attributes->set('station_active_id', $stationId);

        return $next($request);
    }
}