<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Settings\Models\Pompe;
use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\VenteLitre;
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
    public function getKpis(): array
    {
        $today = Carbon::today();

        $ventes = LigneVente::visible()
            ->where('status', true)
            ->whereDate('created_at', $today)
            ->whereHas('affectation.pompe', fn ($q) => $q->visible())
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
    public function getProgression7Jours(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();

        return LigneVente::visible()
            ->where('status', true)
            ->where('created_at', '>=', $start)
            ->whereHas('affectation.pompe', fn ($q) => $q->visible())
            ->get()
            ->groupBy(fn ($v) => $v->created_at->toDateString())
            ->map(function ($group, $date) {
                return [
                    'date'    => $date,
                    'montant' => (float) $group->sum(function ($v) {
                        $pu = $this->getDernierPrixApprovisionnement($v->id_cuve);
                        return $v->qte_vendu * $pu;
                    }),
                    'volume'  => (float) $group->sum('qte_vendu'),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 RÉPARTITION PAR CARBURANT
     * =================================================
     */
    public function getRepartitionCarburant(): array
    {
        return LigneVente::visible()
            ->where('status', true)
            ->whereHas('affectation.pompe', function ($q) {
                $q->visible()->whereNotNull('type_pompe');
            })
            ->with([
                'affectation.pompe' => fn ($q) =>
                    $q->visible()->select('id', 'type_pompe')
            ])
            ->get()
            ->filter(fn ($v) => $v->affectation && $v->affectation->pompe)
            ->groupBy(fn ($v) => $v->affectation->pompe->type_pompe)
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
            ->whereHas('affectation.pompe', fn ($q) => $q->visible())
            ->with([
                'affectation.pompe' => fn ($q) =>
                    $q->visible()->select('id', 'libelle')
            ])
            ->get()
            ->filter(fn ($v) => $v->affectation && $v->affectation->pompe)
            ->groupBy(fn ($v) => $v->affectation->pompe->libelle)
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

    /**
     * =================================================
     * 🔹 PRIX UNITAIRE : DERNIER APPROVISIONNEMENT
     * =================================================
     */
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

        return (float) (
            Cuve::visible()
                ->where('id', $idCuve)
                ->value('pu_vente')
            ?? 0.0
        );
    }

public function getRapport(array $payload): array
{
    try {

        $typeRapport = $payload['type_rapport'] ?? null;

        if (! $typeRapport) {
            return [
                'status'  => 422,
                'message' => 'Le type_rapport est requis (ventes, stock, pompes).',
                'data'    => [],
            ];
        }

        // 🔹 NORMALISATION UNIQUE DES DATES
        $dateDebut = ! empty($payload['date_debut'])
            ? $payload['date_debut'] . ' 00:00:00'
            : now()->startOfDay()->toDateTimeString();

        $dateFin = ! empty($payload['date_fin'])
            ? $payload['date_fin'] . ' 23:59:59'
            : now()->endOfDay()->toDateTimeString();

        switch ($typeRapport) {

            case 'ventes':

                $result = $this->queryRapportVentes(
                    $dateDebut,
                    $dateFin,
                    $payload['id_pompe'] ?? null,
                    $payload['id_pompiste'] ?? null
                );

                if (($result['status'] ?? null) !== 200) {
                    return $result;
                }

                return [
                    'status'  => 200,
                    'message' => 'Rapport ventes généré avec succès.',
                    'data'    => $result['data'],
                ];

            case 'stock':

                $result = $this->rapportStock(
                    $dateDebut,
                    $dateFin,
                    $payload['id_cuve'] ?? null
                );

                if (($result['status'] ?? null) !== 200) {
                    return $result;
                }

                return [
                    'status'  => 200,
                    'message' => 'Rapport stock généré avec succès.',
                    'data'    => $result['data'],
                ];

            case 'pompes':

                $result = $this->rapportPompes(
                    $dateDebut,
                    $dateFin,
                    $payload['id_pompe'] ?? null
                );

                if (($result['status'] ?? null) !== 200) {
                    return $result;
                }

                return [
                    'status'  => 200,
                    'message' => 'Rapport pompes généré avec succès.',
                    'data'    => $result['data'],
                ];

            default:
                return [
                    'status'  => 422,
                    'message' => 'type_rapport invalide. Valeurs: ventes, stock, pompes.',
                    'data'    => [],
                ];
        }

    } catch (\Throwable $e) {

        return [
            'status'  => 'error',
            'message' => 'Erreur lors de la génération du rapport.',
            'error'   => $e->getMessage(),
        ];
    }
}

public function queryRapportVentes(
    string $dateDebut,
    string $dateFin,
    ?int $idPompe = null,
    ?int $idPompiste = null
): array {
    try {

        $ventes = LigneVente::visible()
            ->where('status', true)
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->whereHas('affectation', function ($q) use ($idPompe, $idPompiste) {
                $q->visible();

                if ($idPompe) {
                    $q->where('id_pompe', $idPompe);
                }

                if ($idPompiste) {
                    $q->where('id_user', $idPompiste);
                }
            })
            ->with([
                'affectation.pompe:id,libelle',
                'affectation.user:id,name,telephone',
                'cuve:id,libelle,pu_vente',
            ])
            ->get();

        $data = $ventes->map(function ($v) {

            $qte = (float) $v->qte_vendu;

            // 🔒 PRIX EFFECTIF (priorité à la vente, sinon cuve)
            $pu = $v->prix_unitaire !== null
                ? (float) $v->prix_unitaire
                : (float) ($v->cuve->pu_vente ?? 0);

            $montant = $qte * $pu;

            return [
                'pompe'         => $v->affectation->pompe->libelle ?? null,
                'pompiste'      => $v->affectation->user->name ?? null,
                'telephone'     => $v->affectation->user->telephone ?? null,
                'cuve'          => $v->cuve->libelle ?? null,
                'quantite'      => $qte,
                'prix_unitaire' => $pu,
                'montant'       => $montant,
                'date'          => $v->created_at->format('Y-m-d H:i:s'),
            ];
        })->values()->toArray();

        return [
            'status' => 200,
            'data'   => $data,
        ];

    } catch (\Throwable $e) {

        return [
            'status'  => 'error',
            'message' => 'Erreur lors de la récupération des ventes.',
            'error'   => $e->getMessage(),
        ];
    }
}



public function rapportStock(
    string $dateDebut,
    string $dateFin,
    ?int $idCuve = null
): array {
    try {

        $query = VenteLitre::visible()
            ->where('status', true)
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->whereHas('cuve', function ($q) use ($idCuve) {
                $q->visible();

                if ($idCuve) {
                    $q->where('id', $idCuve);
                }
            })
            ->with([
                'cuve:id,libelle',
            ])
            ->orderByDesc('created_at');

        $lignes = $query->get();

        /**
         * 👉 Pour chaque cuve :
         * on prend la DERNIÈRE valeur (jaugeage)
         */
        $data = $lignes
            ->groupBy(fn ($v) => $v->cuve->id)
            ->map(function ($group) {
                $last = $group->first(); // déjà trié DESC

                return [
                    'cuve'  => $last->cuve->libelle ?? null,
                    'stock' => (float) ($last->litres ?? 0), // ou qte_vendu selon ton champ
                    'date'  => $last->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->toArray();

        return [
            'status' => 200,
            'data'   => $data,
        ];

    } catch (\Throwable $e) {

        return [
            'status'  => 'error',
            'message' => 'Erreur lors de la génération du rapport de stock.',
            'error'   => $e->getMessage(),
        ];
    }
}




public function rapportPompes(
    string $dateDebut,
    string $dateFin,
    ?int $idPompe = null
): array {
    try {

        $ventes = LigneVente::visible()
            ->where('status', true)
            ->whereBetween('created_at', [
                $dateDebut . ' 00:00:00',
                $dateFin   . ' 23:59:59',
            ])
            ->whereHas('affectation', function ($q) use ($idPompe) {
                $q->visible();

                if ($idPompe) {
                    $q->where('id_pompe', $idPompe);
                }
            })
            ->with([
                'affectation.pompe' => fn ($q) =>
                    $q->visible()->select('id', 'libelle'),
            ])
            ->get();

        $data = $ventes
            ->groupBy(fn ($v) => $v->affectation->pompe->libelle)
            ->map(fn ($group, $pompe) => [
                'pompe'   => $pompe,
                'volume'  => (float) $group->sum('qte_vendu'),
                'ventes'  => $group->count(),
            ])
            ->sortByDesc('volume')
            ->values()
            ->toArray();

        return [
            'status' => 200,
            'data'   => $data,
        ];

    } catch (\Throwable $e) {

        return [
            'status'  => 'error',
            'message' => 'Erreur lors de la génération du rapport des pompes.',
            'error'   => $e->getMessage(),
        ];
    }
}


}
