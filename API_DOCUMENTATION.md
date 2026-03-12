# API Documentation — SPA Station Service

> **Version** : 1.0
> **Stack** : Laravel 12 / Sanctum
> **Base URL** : `http://votre-domaine/api`

---

## Headers requis

Toutes les routes protégées nécessitent :

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## Format de réponse standard

**Succès :**
```json
{ "status": 200, "message": "...", "data": { ... } }
```

**Erreur validation (422) :**
```json
{ "status": "error", "message": "Erreur de validation", "errors": { "champ": ["message"] } }
```

**Légende** : ✅ Obligatoire · ❌ Optionnel

---

## Table des matières

1-backoffice-master-admin)
2. [Administration (Tenant)](#2-administration-tenant)
3. [Settings (Paramétrage)](#3-settings-paramétrage)
4. [Vente](#4-vente)
5. [Caisse](#5-caisse)
6. [Dashboard](#6-dashboard)

---

---


# 2. ADMINISTRATION (Tenant)

> Préfixe : `/v1/admin` · Middleware : `station.db`

---

## POST /v1/admin/login
Connexion utilisateur tenant (admin, gérant, superviseur, pompiste).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `telephone` | string | ✅ | max 30 |
| `password` | string | ✅ | min 6 |

**Réponse :**
```json
{
  "status": 200,
  "token": "1|abc...",
  "user": {
    "id": 5,
    "name": "Jean Dupont",
    "role": "admin",
    "telephone": "77000000",
    "id_station": 1
  }
}
```

---

## UTILISATEURS — `/v1/admin/users` *(auth)*

### GET /v1/admin/users
Liste de tous les utilisateurs de la station.

### GET /v1/admin/users/{id}
Détail d'un utilisateur.

### POST /v1/admin/users
Créer un utilisateur.

> **Content-Type :** `multipart/form-data` si envoi d'image

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `name` | string | ✅ | max 100 |
| `role` | string | ✅ | `super_admin`, `admin`, `gerant`, `superviseur`, `pompiste` |
| `telephone` | string | ❌ | Unique, max 30 |
| `email` | string | ❌ | Unique, max 100 |
| `password` | string | ❌ | min 6 |
| `adresse` | string | ❌ | max 150 |
| `image` | file | ❌ | jpg/jpeg/png/webp, max 2 Mo |
| `id_station` | integer | ❌ | |
| `id_ville` | integer | ❌ | |
| `status` | boolean | ❌ | |

### PUT /v1/admin/users/{id}
Modifier un utilisateur. (mêmes champs, tous optionnels)

### DELETE /v1/admin/users/{id}
Supprimer un utilisateur.

---

## GET /v1/admin/pompiste-dispo *(auth)*
Liste des pompistes disponibles (sans affectation active).

---


---

# 3. SETTINGS (Paramétrage)

> Préfixe : `/v1/settings` · Middleware : `station.db`, `auth:sanctum`, `active.st`

---

## PAYS — `/v1/settings/pays`

### GET /v1/settings/pays · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | Nom du pays |
| `code` | string | ❌ | Code ISO |
| `status` | boolean | ❌ | |

---

## VILLES — `/v1/settings/villes`

### GET /v1/settings/villes · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | |
| `id_pays` | integer | ❌ | |
| `status` | boolean | ❌ | |

---

## STATIONS — `/v1/settings/stations`

### GET /v1/settings/stations
Liste des stations.

### GET /v1/settings/stations/{id}
Détail d'une station.

### POST /v1/settings/stations
Créer une station.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | Unique, max 150 |
| `code` | string | ❌ | Unique, max 50 |
| `adresse` | string | ❌ | max 255 |
| `latitude` | numeric | ❌ | entre -90 et 90 |
| `longitude` | numeric | ❌ | entre -180 et 180 |
| `id_ville` | integer | ❌ | |
| `status` | boolean | ❌ | |

### PUT /v1/settings/stations/{id}
Modifier une station. (mêmes champs, tous optionnels)

### DELETE /v1/settings/stations/{id}
Supprimer une station.

---

## GET /v1/settings/activate/stations/{id}
Activer une station (définit la station active pour l'utilisateur courant).

---

## POMPES — `/v1/settings/pompes`

### GET /v1/settings/pompes · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | max 150 |
| `id_station` | integer | ✅ | |
| `type_pompe` | string | ✅ | `essence` ou `gasoil` |
| `reference` | string | ❌ | Unique, max 50 |
| `index_initial` | numeric | ❌ | Index de départ |
| `status` | boolean | ❌ | |

---

## GET /v1/settings/pompes-dispo
Pompes disponibles (sans affectation active en cours).

---

## TYPE CARBURANT (CUVES) — `/v1/settings/type-carburant`

> CRUD des cuves depuis le module Settings.

### GET /v1/settings/type-carburant · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | Nom de la cuve |
| `type_cuve` | string | ❌ | Type de carburant |
| `qt_initial` | numeric | ❌ | Quantité initiale |
| `pu_vente` | numeric | ❌ | Prix unitaire de vente |
| `pu_unitaire` | numeric | ❌ | Prix unitaire d'achat |
| `status` | boolean | ❌ | |

---

## FOURNISSEURS — `/v1/settings/fournisseurs`

### GET /v1/settings/fournisseurs · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `raison_sociale` | string | ✅ | max 255 |
| `nom_complet` | string | ❌ | max 255 |
| `telephone` | string | ❌ | Unique, 8–15 chiffres |
| `email` | string | ❌ | Unique, max 255 |
| `adresse` | string | ❌ | max 255 |
| `status` | boolean | ❌ | |

---

## PARAMÉTRAGE STATION — `/v1/settings/params`

### GET /v1/settings/params · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_station` | integer | ✅ | |
| `h_ouvert` | string | ❌ | Heure ouverture `HH:MM` |
| `h_ferme` | string | ❌ | Heure fermeture `HH:MM` |

---

## CONFIGURATIONS — `/v1/settings/configs`

### GET /v1/settings/configs · POST

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `settings` | object | ✅ | Clé/valeur : `{ "cle": "valeur", ... }` |

---

---

# 4. VENTE

> Préfixe : `/v1/vente` · Middleware : `station.db`, `auth:sanctum`, `active.st`

---

## 4.1 CUVES — Stock

### GET /v1/vente/carburant/stock-journalier
Stock de toutes les cuves, filtrable par période.

**Query params :**
| Param | | Description |
|---|---|---|
| `date_debut` | ❌ | Format `Y-m-d` |
| `date_fin` | ❌ | Format `Y-m-d` |

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "reference": "CUV-001",
      "libelle": "Cuve Gasoil",
      "status": true,
      "type": "gasoil",
      "qt_initial": 10000.0,
      "qt_actuelle": 7500.0,
      "stock_physique_actuel": 7200.0,
      "date_derniere_mesure": "2026-03-10 17:00:00",
      "pu_vente": 650.0,
      "pu_unitaire": 600.0,
      "station": { "id": 1, "libelle": "Station Centrale" },
      "created_at": "2026-01-01 00:00:00"
    }
  ]
}
```

---

## 4.2 APPROVISIONNEMENTS CUVES — `/v1/vente/appro`

### GET /v1/vente/appro
Liste des approvisionnements.

### GET /v1/vente/appro/{id}
Détail d'un approvisionnement.

### POST /v1/vente/appro
Enregistrer un approvisionnement de carburant.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | |
| `qte_appro` | numeric | ✅ | Quantité approvisionnée (> 0) |
| `pu_unitaire` | numeric | ✅ | Prix unitaire d'achat |
| `id_fournisseur` | integer | ❌ | |
| `commentaire` | string | ❌ | |

### PUT /v1/vente/appro/{id}
Modifier un approvisionnement. (mêmes champs)

### DELETE /v1/vente/appro/{id}
Supprimer un approvisionnement.

**Réponse (objet) :**
```json
{
  "id": 1,
  "qte_appro": 5000,
  "type": "approvisionnement",
  "pu_unitaire": 600.0,
  "commentaire": null,
  "cuve": { "id": 1, "libelle": "Cuve Gasoil" },
  "fournisseur": { "id": 2, "raison_sociale": "Total", "nom_complet": "...", "telephone": "..." },
  "created_by": "Admin",
  "created_at": "2026-03-01 09:00:00"
}
```

---

## 4.3 RETOUR CUVE

### POST /v1/vente/retour-cuves
Enregistrer un retour de carburant vers la cuve.

**Body :** (identique à POST /appro)
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | |
| `qte_appro` | numeric | ✅ | Quantité retournée |
| `pu_unitaire` | numeric | ✅ | |
| `id_fournisseur` | integer | ❌ | |
| `commentaire` | string | ❌ | |

---

## 4.4 MESURES CUVES (JAUGEAGE) — `/v1/vente/mesure-cuves`

> Relevé physique du niveau réel d'une cuve.

### GET /v1/vente/mesure-cuves · GET /{id}
Liste / détail des mesures.

### POST /v1/vente/mesure-cuves
Enregistrer une mesure de niveau.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | |
| `qte_vendu` | numeric | ✅ | Volume mesuré (> 0.001) |
| `volume` | numeric | ❌ | Volume alternatif |
| `commentaire` | string | ❌ | max 255 |

### DELETE /v1/vente/mesure-cuves/{id}
Supprimer une mesure.

---

## 4.5 PERTES CUVES — `/v1/vente/perte-cuves`

### GET /v1/vente/perte-cuves · GET /{id}
Liste / détail des pertes.

### POST /v1/vente/perte-cuves
Enregistrer une perte de carburant.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | |
| `quantite_perdue` | numeric | ✅ | Quantité perdue (> 0) |
| `commentaire` | string | ❌ | max 255 |

### DELETE /v1/vente/perte-cuves/{id}
Supprimer une perte.

---

## 4.6 LIGNES DE VENTE (RELEVÉ POMPES) — `/v1/vente/initiation`

> Vente carburant d'un pompiste sur une pompe — saisie des index compteur.

### GET /v1/vente/initiation
Liste complète des lignes de vente.

### GET /v1/vente/releve-pompes
Relevé résumé par pompe.

### GET /v1/vente/journalier/carburant
Ventes journalières carburant.

**Query params :**
| Param | | Description |
|---|---|---|
| `date_debut` | ❌ | Format `Y-m-d` |
| `date_fin` | ❌ | Format `Y-m-d` |

### GET /v1/vente/initiation/{id}
Détail d'une ligne de vente.

### POST /v1/vente/initiation
Ouvrir un nouveau relevé (initialiser une ligne de vente).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_station` | integer | ✅ | |

### PUT /v1/vente/initiation/{id}
Mettre à jour une ligne de vente (saisir les index, quantités).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_pompiste` | integer | ✅ | ID de l'utilisateur pompiste |
| `id_station` | integer | ❌ | |
| `id_cuve` | integer | ❌ | |
| `id_affectation` | integer | ❌ | |
| `index_debut` | numeric | ❌ | Index compteur au début |
| `index_fin` | numeric | ❌ | Index compteur à la fin |
| `qte_vendu` | numeric | ❌ | Quantité vendue |
| `retour_cuve` | numeric | ❌ | Retour de carburant vers cuve |
| `commentaire` | string | ❌ | |
| `status` | boolean | ❌ | |

### DELETE /v1/vente/initiation/{id}
Supprimer une ligne de vente.

**Réponse (objet) :**
```json
{
  "id": 1,
  "index_debut": 1000.0,
  "index_fin": 1050.0,
  "qte_vendu": 50.0,
  "status": "en cours",
  "commentaire": null,
  "station": { "id": 1, "libelle": "Station Centrale" },
  "pompe": { "id": 2, "libelle": "Pompe A", "reference": "P001" },
  "pompiste": { "id": 5, "name": "Jean", "email": "jean@mail.com", "telephone": "77000000" },
  "created_by": "Admin",
  "created_at": "2026-03-10 07:00:00",
  "updated_at": "2026-03-10 17:00:00"
}
```

> **Valeurs `status`** : `"en cours"` · `"validée"`

---



### GET /v1/vente/validation · GET /{id}
Liste / détail des validations.

### POST /v1/vente/validation
Valider une vente.


*
## 4.10 CLIENTS — `/v1/vente/clients`

### GET /v1/vente/clients · GET /{id}
Liste / détail d'un client (avec ses ventes).

### POST /v1/vente/clients
Créer un client.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `nom_complet` | string | ✅ | max 150 |
| `telephone` | string | ✅ | Unique, max 20 |
| `email` | string | ❌ | Unique |
| `adresse` | string | ❌ | max 255 |
| `status` | boolean | ❌ | |

### PUT /v1/vente/clients/{id}
Modifier un client. (mêmes champs, tous optionnels)

### DELETE /v1/vente/clients/{id}
Supprimer un client.

**Réponse (objet) :**
```json
{
  "id": 3,
  "nom_complet": "Client A",
  "telephone": "77000001",
  "email": "client@email.com",
  "adresse": "Dakar",
  "status": true,
  "id_station": 1,
  "station": { "id": 1, "libelle": "Station Centrale" },
  "created_at": "2026-01-15 00:00:00"
}
```

---

## 4.11 CRÉANCES — `/v1/vente/creances`

> Carburant ou produit vendu à crédit à un client.

### GET /v1/vente/creances · GET /{id}
Liste / détail d'une créance avec état de paiement.

### POST /v1/vente/creances
Créer une créance.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_client` | integer | ✅ | |
| `qte` | numeric | ✅ | Quantité (> 0) |
| `prix_unitaire` | numeric | ✅ | Prix unitaire |
| `id_pompe` | integer | ❌ | |
| `commentaire` | string | ❌ | max 500 |

### PUT /v1/vente/creances/{id}
Modifier une créance. (mêmes champs)

### DELETE /v1/vente/creances/{id}
Supprimer une créance.

**Réponse (objet) :**
```json
{
  "id": 1,
  "commentaire": null,
  "client": { "id": 3, "nom_complet": "Client A", "telephone": "77000001" },
  "pompe": { "id": 2, "reference": "P001" },
  "facturation": {
    "total_creance": 13000.0,
    "total_paye": 5000.0,
    "reste_a_payer": 8000.0,
    "etat": "partiel"
  },
  "paiements": [
    { "id": 1, "montant_payer": 5000.0, "mode_paiement": "espèces", "created_at": "..." }
  ],
  "created_by": "Admin",
  "created_at": "2026-03-05 00:00:00"
}
```

> **Valeurs `etat`** : `non_paye` · `partiel` · `paye`

---

## 4.12 PAIEMENTS — `/v1/vente/paiements`

> Règlement d'une créance depuis un compte caisse.

### GET /v1/vente/paiements · GET /{id}
Liste / détail d'un paiement.

### POST /v1/vente/paiements
Enregistrer un paiement.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_creance` | integer | ✅ | ID de la créance à régler |
| `id_compte` | integer | ✅ | ID du compte caisse |
| `montant_payer` | numeric | ✅ | Montant payé (> 0) |
| `mode_paiement` | string | ❌ | Ex : `espèces`, `virement`, max 50 |

### PUT /v1/vente/paiements/{id}
Modifier un paiement.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `montant_payer` | numeric | ❌ | |
| `mode_paiement` | string | ❌ | |

### DELETE /v1/vente/paiements/{id}
Supprimer un paiement.

---

---

# 5. CAISSE

> Préfixe : `/v1/caisse` · Middleware : `station.db`, `auth:sanctum`, `active.st`

---

## 5.1 COMPTES — `/v1/caisse/comptes`

### GET /v1/caisse/comptes · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_station` | integer | ✅ | |
| `libelle` | string | ✅ | max 100 |
| `numero` | string | ✅ | Numéro de compte, max 100 |
| `solde_initial` | numeric | ✅ | Solde de départ (≥ 0) |
| `commentaire` | string | ❌ | max 255 |

---

## 5.2 TYPES D'OPÉRATION — `/v1/caisse/type-operations`

### GET /v1/caisse/type-operations · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | max 100 |
| `commentaire` | string | ✅ | max 255 |
| `nature` | integer | ✅ | `0` = sortie · `1` = entrée · `2` = transfert |

---

## 5.3 OPÉRATIONS COMPTE — `/v1/caisse/operations`

### GET /v1/caisse/operations · GET /{id}
Liste / détail des opérations.

### POST /v1/caisse/operations
Enregistrer une opération (entrée ou sortie).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_compte` | integer | ✅ | |
| `id_type_operation` | integer | ✅ | |
| `montant` | numeric | ✅ | > 0 |
| `reference` | string | ❌ | max 100 |
| `commentaire` | string | ❌ | max 255 |

### DELETE /v1/caisse/operations/{id}
Supprimer une opération.

---

## 5.4 TRANSFERT INTER-COMPTES

### POST /v1/caisse/operations/transfert
Initier un transfert entre deux comptes.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_source` | integer | ✅ | Compte source |
| `id_destination` | integer | ✅ | Compte destination (≠ source) |
| `montant` | numeric | ✅ | > 0 |
| `reference` | string | ❌ | max 100 |
| `commentaire` | string | ❌ | max 255 |

### POST /v1/caisse/operations/transfert/confirm
Confirmer un transfert.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `reference` | string | ✅ | Référence du transfert à confirmer |

### POST /v1/caisse/operations/transfert/cancel
Annuler un transfert.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `reference` | string | ✅ | Référence du transfert à annuler |

### GET /v1/caisse/transfert
Liste de tous les transferts.

---

## 5.5 OPÉRATIONS PAR PÉRIODE

### GET /v1/caisse/operations-comptes/periode
Opérations filtrées par période.

**Query params :**
| Param | | Description |
|---|---|---|
| `date_debut` | ❌ | Format `Y-m-d` |
| `date_fin` | ❌ | Format `Y-m-d` |

### GET /v1/caisse/transfert-intercomptes/periode
Transferts filtrés par période. (mêmes query params)

---

## 5.6 RÉSUMÉ MENSUEL

### GET /v1/caisse/resume-mensuel
Résumé mensuel des opérations par mois.

**Query params :**
| Param | | Description |
|---|---|---|
| `annee` | ❌ | Année (défaut : année courante) |

---

## 5.7 CATÉGORIES DE CHARGES — `/v1/caisse/categori-charges`

### GET /v1/caisse/categori-charges · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | max 150 |
| `is_fixed` | boolean | ❌ | Charge fixe (`true`) ou variable (`false`) |
| `status` | boolean | ❌ | |

---

## 5.8 OPÉRATIONS CHARGES — `/v1/caisse/operations-charges`

### GET /v1/caisse/operations-charges · GET /{id} · POST · PUT /{id} · DELETE /{id}

**Body POST :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_compte` | integer | ✅ | |
| `id_charge_category` | integer | ✅ | |
| `montant` | numeric | ✅ | > 0 |
| `commentaire` | string | ❌ | max 255 |
| `status` | boolean | ❌ | |

---

---

# 6. DASHBOARD

> Préfixe : `/v1/dashboard` · Middleware : `station.db`, `auth:sanctum`, `active.st`

---

## GET /v1/dashboard
Dashboard principal — données du jour courant.

**Réponse :**
```json
{
  "status": 200,
  "data": {
    "kpis": {
      "ventes_du_jour": 12,
      "recettes_du_jour": 450000.0,
      "volume_vendu": 900.0,
      "pompes_actives": { "actives": 3, "total": 4 }
    },
    "progression_7_jours": [
      { "date": "2026-03-06", "montant": 320000.0, "volume": 640.0 },
      { "date": "2026-03-07", "montant": 410000.0, "volume": 820.0 }
    ],
    "repartition_carburant": [
      { "type_pompe": "gasoil", "volume": 600.0 },
      { "type_pompe": "essence", "volume": 300.0 }
    ],
    "volume_par_pompe": [
      { "pompe": "Pompe 1", "volume": 400.0 },
      { "pompe": "Pompe 2", "volume": 200.0 }
    ],
    "approvisionnements_30j": [
      { "date": "2026-03-01", "volume": 5000.0 },
      { "date": "2026-03-08", "volume": 3000.0 }
    ]
  }
}
```

---

## POST /v1/dashboard/rapport
Générer un rapport filtré par période.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `type_rapport` | string | ✅ | `ventes`, `stock`, ou `pompes` |
| `date_debut` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |
| `date_fin` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |
| `id_pompe` | integer | ❌ | Filtre pompe — rapport `ventes` uniquement |
| `id_pompiste` | integer | ❌ | Filtre pompiste — rapports `ventes` et `pompes` |
| `id_cuve` | integer | ❌ | Filtre cuve — rapport `stock` uniquement |

**Réponse — `type_rapport: "ventes"` :**
```json
{
  "status": 200,
  "message": "Rapport ventes généré avec succès.",
  "data": [
    {
      "pompe": "Pompe 1",
      "pompiste": "Jean Dupont",
      "telephone": "77000000",
      "cuve": "Cuve Gasoil",
      "quantite": 20.0,
      "prix_unitaire": 650.0,
      "montant": 13000.0,
      "date": "2026-03-10 08:30:00"
    }
  ]
}
```

**Réponse — `type_rapport: "stock"` :**
```json
{
  "status": 200,
  "message": "Rapport stock généré avec succès.",
  "data": [
    { "cuve": "Cuve Gasoil", "stock": 3200.0, "date": "2026-03-10 18:00:00" }
  ]
}
```

**Réponse — `type_rapport: "pompes"` :**
```json
{
  "status": 200,
  "message": "Rapport pompes généré avec succès.",
  "data": [
    {
      "pompiste": "Jean Dupont",
      "telephone": "77000000",
      "volume": 200.0,
      "montant": 130000.0,
      "ventes": 10
    }
  ]
}
```
