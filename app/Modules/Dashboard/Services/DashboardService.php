<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Vente\Models\Cuve;
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
            // 'repartition_carburant'  => $this->getRepartitionCarburant(),
            // 'volume_par_pompe'       => $this->getVolumeParPompe(),
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

        $ventesQuery = LigneVente::visible()
            ->where('status', true)
            ->whereDate('created_at', $today);

        return [
            'ventes_du_jour'   => (clone $ventesQuery)->count(),
            'recettes_du_jour' => 0.0, // caisse volontairement non branchée
            'volume_vendu'     => (float) (clone $ventesQuery)->sum('qte_vendu'),
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
            ->selectRaw('DATE(created_at) as date, SUM(qte_vendu) as volume')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'    => $row->date,
                'montant' => 0.0,
                'volume'  => (float) $row->volume,
            ])
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
    public function getVolumeParPompe(): array
    {
        return LigneVente::visible()
            ->where('status', true)
            ->whereHas('affectation.pompe')
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
    // private function getApprovisionnements30Jours(): array
    // {
    //     return ApprovisionnementCuve::visible()
    //         ->where('created_at', '>=', Carbon::now()->subDays(30))
    //         ->selectRaw('DATE(created_at) as date, SUM(qte_appro) as volume')
    //         ->groupByRaw('DATE(created_at)')
    //         ->orderBy('date')
    //         ->get()
    //         ->map(fn ($row) => [
    //             'date'   => $row->date,
    //             'volume' => (float) $row->volume,
    //         ])
    //         ->toArray();
    // }

    public function getApprovisionnements30Jours(): array
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


 public function getDernierPrixApprovisionnement(?int $idCuve): float
    {
        if (! $idCuve) {
            return 0.0;
        }

        $puAppro = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->where('type_appro', 'approvisionnement')
            ->orderByDesc('created_at')
            ->value('pu_unitaire');

        if ($puAppro !== null) {
            return (float) $puAppro;
        }

        return (float) Cuve::visible()
            ->where('id', $idCuve)
            ->value('pu_vente') ?? 0.0;
    }


}
