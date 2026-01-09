<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Settings\Models\Pompe;
use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use Carbon\Carbon;

class DashboardService
{
    /**
     * =================================================
     * 🔹 DASHBOARD PRINCIPAL
     * =================================================
     */
    public function getDashboard(): array
    {
        return [
            'kpis'                   => $this->getKpis(),
            'progression_7_jours'    => $this->getProgression7Jours(),
            'repartition_carburant'  => $this->getRepartitionCarburant(),
            'volume_par_pompe'       => $this->getVolumeParPompe(),
            'approvisionnements_30j' => $this->getApprovisionnements30Jours(),
        ];
    }

    /**
     * =================================================
     * 🔹 KPIs DU JOUR
     * =================================================
     */
    private function getKpis(): array
    {
        $today = Carbon::today();

        $ventes = LigneVente::visible()
            ->where('status', true)
            ->whereDate('created_at', $today)
            ->get();

        $recettes = $ventes->sum(function ($vente) {
            $pu = $this->getDernierPrixApprovisionnement($vente->id_cuve);
            return $vente->qte_vendu * $pu;
        });

        return [
            'ventes_du_jour'   => $ventes->count(),
            'recettes_du_jour' => (float) $recettes,
            'volume_vendu'     => (float) $ventes->sum('qte_vendu'),
            'pompes_actives'   => [
                'actives' => Pompe::visible()->where('status', true)->count(),
                'total'   => Pompe::visible()->count(),
            ],
        ];
    }

    /**
     * =================================================
     * 🔹 PROGRESSION DES VENTES (7 JOURS)
     * =================================================
     */
    private function getProgression7Jours(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();

        return LigneVente::visible()
            ->where('status', true)
            ->where('created_at', '>=', $start)
            ->get()
            ->groupBy(fn ($v) => $v->created_at->toDateString())
            ->map(fn ($group, $date) => [
                'date'    => $date,
                'montant' => (float) $group->sum(function ($v) {
                    $pu = $this->getDernierPrixApprovisionnement($v->id_cuve);
                    return $v->qte_vendu * $pu;
                }),
                'volume'  => (float) $group->sum('qte_vendu'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 RÉPARTITION PAR CARBURANT
     * =================================================
     */
    private function getRepartitionCarburant(): array
    {
        return LigneVente::visible()
            ->where('status', true)
            ->whereHas('affectation.pompe', function ($q) {
                $q->whereNotNull('type_pompe');
            })
            ->with('affectation.pompe:id,type_pompe')
            ->get()
            ->groupBy(fn ($vente) => $vente->affectation->pompe->type_pompe)
            ->map(fn ($group, $type) => [
                'type_pompe' => $type,
                'volume'     => (float) $group->sum('qte_vendu'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 VOLUME PAR POMPE
     * =================================================
     */
    private function getVolumeParPompe(): array
    {
        return LigneVente::visible()
            ->where('status', true)
            ->with('affectation.pompe:id,libelle')
            ->get()
            ->groupBy(fn ($vente) => $vente->affectation->pompe->libelle)
            ->map(fn ($group, $pompe) => [
                'pompe'  => $pompe,
                'volume' => (float) $group->sum('qte_vendu'),
            ])
            ->sortByDesc('volume')
            ->values()
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 APPROVISIONNEMENTS (30 JOURS)
     * =================================================
     */
    private function getApprovisionnements30Jours(): array
    {
        return ApprovisionnementCuve::visible()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(qte_appro) as volume')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'   => $row->date,
                'volume' => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 PRIX UNITAIRE : DERNIER APPROVISIONNEMENT
     * =================================================
     */
    private function getDernierPrixApprovisionnement(?int $idCuve): float
    {
        if (! $idCuve) {
            return 0.0;
        }

        // 🔹 Dernier prix d’approvisionnement
        $puAppro = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->where('type_appro', 'approvisionnement')
            ->orderByDesc('created_at')
            ->value('pu_unitaire');

        if ($puAppro !== null) {
            return (float) $puAppro;
        }

        // 🔹 Fallback : prix de vente de la cuve
        return (float) Cuve::visible()
            ->where('id', $idCuve)
            ->value('pu_vente') ?? 0.0;
    }
}
