# Documentation API — Nouvelles Fonctionnalités
## Station Service Backend — SPA Technology

---

## Informations générales

Toutes les requêtes nécessitent les **deux en-têtes obligatoires** suivants :

```
Authorization: Bearer {token}
code: {code_entreprise}
```

Le `token` est obtenu après connexion via `POST /v1/admin/login`.  
Le `code` est le code de l'entreprise/tenant (ex: `SPA001`).

---

## BESOIN 1 — Historique du solde de caisse

### Problème résolu
Avant, le solde affiché était toujours le solde **d'aujourd'hui**, même si on consultait des données passées. Maintenant on peut obtenir le solde à une date précise.

### Endpoint existant (inchangé)
```
GET /v1/caisse/comptes
GET /v1/caisse/comptes/{id}
```

### Nouveau paramètre optionnel
```
GET /v1/caisse/comptes?date=2025-12-31
GET /v1/caisse/comptes/{id}?date=2025-12-31
```

| Paramètre | Type | Obligatoire | Description |
|---|---|---|---|
| `date` | string (YYYY-MM-DD) | Non | Date à laquelle calculer le solde historique |

### Réponse — sans paramètre `date` (comportement inchangé)
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "libelle": "Caisse principale",
      "numero": "CPT-001",
      "commentaire": null,
      "station": {
        "id": 1,
        "libelle": "Station Kaloum",
        "dernier_gerant": {
          "name": "Mamadou Diallo",
          "email": "m.diallo@spa.com",
          "telephone": "621000001",
          "adresse": "Kaloum"
        }
      },
      "solde_initial": 500000,
      "total_creances": 150000,
      "solde_actuel": 2350000,
      "solde_a_date": null,
      "created_by": "Admin",
      "modify_by": null,
      "created_at": "2026-01-01 08:00:00",
      "updated_at": "2026-07-04 10:00:00"
    }
  ]
}
```

### Réponse — avec paramètre `?date=2025-12-31`
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "libelle": "Caisse principale",
      "solde_initial": 500000,
      "total_creances": 150000,
      "solde_actuel": 2350000,
      "solde_a_date": 1200000,
      "...": "..."
    }
  ]
}
```

> **Note** : `solde_actuel` est toujours le solde d'aujourd'hui. `solde_a_date` est le solde calculé à la date demandée. Si aucune date n'est passée, `solde_a_date` vaut `null`.

### Utilisation recommandée au frontend
- Afficher `solde_actuel` par défaut
- Quand l'utilisateur choisit une date dans un calendrier → refaire l'appel avec `?date=YYYY-MM-DD` et afficher `solde_a_date`

---

## BESOIN 2 — Mouvement de stock (correction)

### Problème résolu
Le calcul du stock théorique incluait les ventes **annulées**, ce qui faussait l'écart affiché. Cette correction est transparente — aucun changement d'endpoint, les données retournées sont maintenant correctes.

### Endpoint concerné (inchangé)
```
GET /v1/vente/carburant/stock-journalier
```

### Réponse
```json
{
  "status": 200,
  "data": [
    {
      "date": "2026-07-04",
      "cuve": {
        "id": 1,
        "libelle": "Gasoil",
        "pu_vente": 12000
      },
      "stock_matin": 15000,
      "entrees": 5000,
      "retour_cuve": 0,
      "sorties": 8500,
      "stock_theorique": 11500,
      "stock_physique": 11200,
      "ecart": -300
    }
  ]
}
```

> **Règle métier** : `sorties` = uniquement les ventes réellement validées (status=true). Les ventes annulées ou en cours ne sont plus comptées.

---

## BESOIN 3 — Vérification du stock avant validation d'une vente

### Problème résolu
Avant, une vente pouvait être validée même si la cuve n'avait pas assez de stock. Maintenant, le système bloque si le stock est insuffisant.

### Endpoint concerné (inchangé)
```
PUT /v1/vente/initiation/{id}
```

### Corps de la requête (inchangé)
```json
{
  "index_fin": 12500,
  "retour_cuve": 0,
  "id_pompiste": 5,
  "mode": "especes"
}
```

### Réponse — succès (inchangée)
```json
{
  "status": 200,
  "message": "Vente validée avec succès."
}
```

### Nouveau cas d'erreur — stock insuffisant
```json
{
  "status": 409,
  "message": "Stock insuffisant. Disponible : 200 L, demandé : 500 L."
}
```

### Règle métier
Le système calcule le stock disponible à partir du **dernier jaugeage** de la cuve :
```
Stock disponible = dernier jaugeage
                + approvisionnements depuis ce jaugeage
                - ventes validées depuis ce jaugeage
                - pertes depuis ce jaugeage
```

> **Important** : Si aucun jaugeage n'a jamais été fait pour cette cuve, la vérification est ignorée et la vente est acceptée. Il est donc indispensable d'effectuer au moins un jaugeage par cuve pour que le contrôle s'active.

---

## BESOIN 4 — Rapport mensuel enrichi

### Problème résolu
Le rapport mensuel retournait un chiffre d'affaires à 0 (bug) et manquait plusieurs données demandées par le client.

### Endpoint (inchangé)
```
GET /v1/caisse/resume-mensuel?annee=2026
```

| Paramètre | Type | Obligatoire | Description |
|---|---|---|---|
| `annee` | integer | Oui | Année du rapport (ex: 2026) |

### Réponse — ancienne (avant correction)
```json
{
  "status": 200,
  "data": [
    {
      "mois": "Janvier",
      "ventes_directes": 0,
      "paiements_creances": 500000,
      "total_ventes": 500000,
      "total_depenses": 200000,
      "benefice": 300000
    }
  ]
}
```

### Réponse — nouvelle (enrichie et corrigée)
```json
{
  "status": 200,
  "data": [
    {
      "mois": "Janvier",
      "ventes_directes": 12500000,
      "paiements_creances": 500000,
      "total_ventes": 13000000,
      "litres_vendus": 1042,
      "total_appro": 8000000,
      "marge_brute": 416800,
      "total_depenses": 200000,
      "total_creances": 350000,
      "resultat": 216800,
      "benefice": 12800000
    }
  ]
}
```

### Description des nouveaux champs

| Champ | Calcul | Description |
|---|---|---|
| `ventes_directes` | `SUM(qte_vendu × prix_unitaire)` | CA réel des ventes carburant (corrigé — était 0 avant) |
| `litres_vendus` | `SUM(qte_vendu)` | Total litres vendus dans le mois |
| `total_appro` | `SUM(qte_appro × pu_unitaire)` | Coût total des approvisionnements |
| `marge_brute` | `litres_vendus × 400` | Marge brute (400 GNF par litre) |
| `total_creances` | `SUM(creances.montant)` | Nouvelles créances créées dans le mois |
| `resultat` | `marge_brute - total_depenses` | Résultat net du mois |
| `benefice` | `total_ventes - total_depenses` | Bénéfice comptable du mois |

---

## BESOIN 5 — Versements Direction → Station

### Contexte
Avant, seul le sens **Station → Direction** existait. Maintenant la Direction peut envoyer de l'argent vers une Station.

---

### 5.1 — Initier un versement Direction → Station

```
POST /v1/caisse/versements-direction/initier-depuis-direction
```

**Rôles autorisés** : `admin`, `super_admin` uniquement.

#### Corps de la requête
```json
{
  "id_compte_direction": 1,
  "id_station": 2,
  "montant": 5000000,
  "mode": "virement",
  "commentaire": "Dotation mensuelle station Kaloum"
}
```

| Champ | Type | Obligatoire | Valeurs possibles |
|---|---|---|---|
| `id_compte_direction` | integer | Oui | ID du compte direction (doit exister) |
| `id_station` | integer | Oui | ID de la station destinataire (doit exister) |
| `montant` | numeric | Oui | Supérieur à 0 |
| `mode` | string | Oui | `banque`, `especes`, `virement`, `chéque` |
| `commentaire` | string | Non | Texte libre |

#### Réponse — succès
```json
{
  "status": 200,
  "message": "Versement depuis la direction initié, en attente de confirmation.",
  "reference": "OPC-20260704-A9F3KQ"
}
```

Le versement est créé avec le statut `en_attente`. Il faut ensuite le confirmer.

#### Réponses d'erreur

| Code | Message | Cause |
|---|---|---|
| 403 | Seul un administrateur peut initier un versement depuis la direction. | Rôle insuffisant |
| 404 | Compte direction introuvable. | `id_compte_direction` invalide |
| 404 | Aucun compte trouvé pour cette station. | La station n'a pas de compte caisse |
| 409 | Montant invalide. | Montant ≤ 0 |
| 409 | Solde insuffisant sur le compte direction. | Le compte direction n'a pas assez de fonds |

---

### 5.2 — Confirmer un versement (Station → Direction OU Direction → Station)

L'endpoint de confirmation est le **même** pour les deux sens.

```
POST /v1/caisse/versements-direction/confirmer
```

**Rôles autorisés** : `admin`, `super_admin` uniquement.

#### Corps de la requête
```json
{
  "reference": "OPC-20260704-A9F3KQ"
}
```

#### Réponse — succès
```json
{
  "status": 200,
  "message": "Versement validé avec succès."
}
```

#### Effet sur les soldes après confirmation

**Direction → Station :**
- Solde du compte direction **diminue** du montant
- Solde du compte de la station **augmente** du montant

**Station → Direction :**
- Solde du compte de la station **diminue** du montant
- Solde du compte direction **augmente** du montant

---

### 5.3 — Annuler un versement (inchangé)

```
POST /v1/caisse/versements-direction/annuler
```

```json
{
  "reference": "OPC-20260704-A9F3KQ"
}
```

Fonctionne pour les deux sens. Le versement doit être en statut `en_attente`.

---

### 5.4 — Liste des versements (mise à jour)

```
GET /v1/caisse/versements-direction?date_debut=2026-07-01&date_fin=2026-07-04
```

La liste retourne maintenant **les deux sens** : versements Station→Direction ET Direction→Station visibles pour la station active.

---

## BESOIN 6 — Annulation d'une vente validée

### Contexte
Un admin peut annuler une vente déjà validée (clôturée par un pompiste). Cette opération :
- Retire l'argent de la caisse (reversal comptable)
- Déverrouille la vente
- Libère le pompiste pour re-saisir
- Enregistre qui a annulé et pourquoi

### Endpoint
```
DELETE /v1/vente/initiation/{id}
```

### Deux comportements selon le statut de la vente

#### Cas 1 — Vente NON encore validée (status = false)
Tout utilisateur authentifié peut supprimer la vente. Aucun corps requis.

```
DELETE /v1/vente/initiation/42
```

Réponse :
```json
{
  "status": 200,
  "message": "Vente non validée supprimée avec succès."
}
```

#### Cas 2 — Vente déjà validée (status = true)
**Rôles autorisés** : `admin`, `super_admin` uniquement.

```
DELETE /v1/vente/initiation/42
Content-Type: application/json

{
  "raison": "Erreur de saisie de l'index"
}
```

| Champ | Type | Obligatoire | Description |
|---|---|---|---|
| `raison` | string | Non | Motif de l'annulation (recommandé) |

#### Réponse — succès
```json
{
  "status": 200,
  "message": "Vente annulée avec succès."
}
```

#### Ce qui se passe en base de données

| Table | Modification |
|---|---|
| `ligne_ventes` | `status` passe de `true` à `false` |
| `operations_comptes` | Nouvelle ligne de reversal créée (retire l'argent de caisse) |
| `validation_ventes` | `commentaire` mis à jour avec la raison et la date d'annulation |
| `affectations` | `status = true`, `id_user = null` (pompiste libéré pour re-saisir) |

#### Exemple de commentaire après annulation
```
Vente validée
Volume : 500 L
Retour cuve : 0 L
PU : 12000 GNF
Montant : 6000000 GNF
Cuve : Gasoil
---
VENTE ANNULÉE LE 04/07/2026 10:30 par Jean Dupont — Raison : Erreur de saisie de l'index
```

#### Réponses d'erreur

| Code | Message | Cause |
|---|---|---|
| 403 | Seul un administrateur peut annuler une vente validée. | Rôle insuffisant |
| 404 | Ligne de vente introuvable. | ID incorrect ou non visible |
| 409 | Validation de vente introuvable. | Incohérence de données |
| 409 | Opération comptable de la vente introuvable. | Incohérence de données |

> **Important** : L'annulation est une **transaction atomique**. Si une étape échoue, tout est annulé — la caisse, la vente et l'affectation restent dans leur état d'origine.

---

## Récapitulatif des nouveaux endpoints

| Méthode | Endpoint | Besoin | Rôles |
|---|---|---|---|
| `GET` | `/v1/caisse/comptes?date=YYYY-MM-DD` | 1 | Tous |
| `GET` | `/v1/vente/carburant/stock-journalier` | 2 | Tous |
| `PUT` | `/v1/vente/initiation/{id}` | 3 | Correction existant |
| `GET` | `/v1/caisse/resume-mensuel?annee=YYYY` | 4 | Tous |
| `POST` | `/v1/caisse/versements-direction/initier-depuis-direction` | 5 | admin, super_admin |
| `POST` | `/v1/caisse/versements-direction/confirmer` | 5 | admin, super_admin |
| `DELETE` | `/v1/vente/initiation/{id}` | 6 | admin, super_admin (si validée) |
