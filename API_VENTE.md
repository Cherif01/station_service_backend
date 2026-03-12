# API Documentation — Module Vente

> **Préfixe** : `/api/v1/vente/`
> **Auth** : `Authorization: Bearer {token}` requis sur toutes les routes
> **Headers** : `Accept: application/json` · `Content-Type: application/json`

---

## GET /v1/vente/carburant/stock-journalier
Stock des cuves entre deux dates.

**Query params :**
| Paramètre | Type | | Description |
|---|---|---|---|
| `date_debut` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |
| `date_fin` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id_cuve": 1,
      "libelle": "Cuve Gasoil",
      "type_produit": "gasoil",
      "stock_debut": 8000.0,
      "stock_fin": 5500.0,
      "variation": -2500.0
    }
  ]
}
```

---

## GET /v1/vente/journalier/carburant
Ventes journalières de carburant entre deux dates.

**Query params :**
| Paramètre | Type | | Description |
|---|---|---|---|
| `date_debut` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |
| `date_fin` | string | ❌ | Format `Y-m-d` (défaut : aujourd'hui) |

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "date": "2026-03-12",
      "volume_total": 1200.0,
      "montant_total": 600000.0
    }
  ]
}
```

---

## GET /v1/vente/releve-pompes
Relevé des pompes — liste complète avec détail par pompe et affectation.

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "id_cuve": 2,
      "id_affectation": 3,
      "id_pompiste": 5,
      "index_debut": 10000.0,
      "index_fin": 10450.0,
      "qte_vendu": 450.0,
      "retour_cuve": 0.0,
      "status": true,
      "created_at": "2026-03-12T07:00:00Z"
    }
  ]
}
```

---

## POST /v1/vente/retour-cuves
Enregistrer un retour de carburant vers une cuve.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | ID de la cuve |
| `qte_appro` | numeric | ✅ | Quantité retournée (> 0) |
| `pu_unitaire` | numeric | ✅ | Prix unitaire |
| `id_fournisseur` | integer | ❌ | ID fournisseur |
| `commentaire` | string | ❌ | Commentaire |

**Réponse :**
```json
{
  "status": 201,
  "message": "Retour cuve enregistré avec succès",
  "data": {
    "id": 10,
    "id_cuve": 1,
    "qte_appro": 200.0,
    "pu_unitaire": 580.0
  }
}
```

---

## Approvisionnements — `appro`

### GET /v1/vente/appro
Liste tous les approvisionnements.

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "id_cuve": 1,
      "qte_appro": 5000.0,
      "pu_unitaire": 575.0,
      "id_fournisseur": 2,
      "commentaire": null,
      "created_at": "2026-03-01T08:00:00Z"
    }
  ]
}
```

### POST /v1/vente/appro
Créer un approvisionnement.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | ID de la cuve |
| `qte_appro` | numeric | ✅ | Quantité approvisionnée (> 0) |
| `pu_unitaire` | numeric | ✅ | Prix unitaire |
| `id_fournisseur` | integer | ❌ | ID fournisseur |
| `commentaire` | string | ❌ | Commentaire |

**Réponse :**
```json
{
  "status": 201,
  "message": "Approvisionnement créé avec succès",
  "data": {
    "id": 5,
    "id_cuve": 1,
    "qte_appro": 5000.0,
    "pu_unitaire": 575.0
  }
}
```

### GET /v1/vente/appro/{id}
Détail d'un approvisionnement.

### PUT /v1/vente/appro/{id}
Modifier un approvisionnement (mêmes champs que POST).

### DELETE /v1/vente/appro/{id}
Supprimer un approvisionnement.

---

## Mesures cuves — `mesure-cuves`

### GET /v1/vente/mesure-cuves
Liste toutes les mesures de cuves.

### POST /v1/vente/mesure-cuves
Créer une mesure de cuve (vente au litre).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | ID de la cuve |
| `qte_vendu` | numeric | ✅ | Quantité vendue (> 0) |
| `volume` | numeric | ❌ | Volume mesuré |
| `commentaire` | string | ❌ | Commentaire (max 255) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Mesure enregistrée avec succès",
  "data": {
    "id": 3,
    "id_cuve": 1,
    "qte_vendu": 50.0,
    "volume": 48.5,
    "commentaire": null
  }
}
```

### GET /v1/vente/mesure-cuves/{id}
Détail d'une mesure.

### DELETE /v1/vente/mesure-cuves/{id}
Supprimer une mesure.

---

## Pertes cuves — `perte-cuves`

### GET /v1/vente/perte-cuves
Liste toutes les pertes déclarées.

### POST /v1/vente/perte-cuves
Déclarer une perte de cuve.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_cuve` | integer | ✅ | ID de la cuve |
| `quantite_perdue` | numeric | ✅ | Quantité perdue (> 0) |
| `commentaire` | string | ❌ | Commentaire (max 255) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Perte déclarée avec succès",
  "data": {
    "id": 2,
    "id_cuve": 1,
    "quantite_perdue": 15.0,
    "commentaire": "Fuite détectée"
  }
}
```

### GET /v1/vente/perte-cuves/{id}
Détail d'une perte.

### DELETE /v1/vente/perte-cuves/{id}
Supprimer une perte.

---

## Paiements — `paiements`

### GET /v1/vente/paiements
Liste tous les paiements de créances.

### POST /v1/vente/paiements
Enregistrer un paiement de créance.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_creance` | integer | ✅ | ID de la créance |
| `id_compte` | integer | ✅ | ID du compte de caisse |
| `montant_payer` | numeric | ✅ | Montant payé (> 0) |
| `mode_paiement` | string | ❌ | Ex : `espèces`, `virement` (max 50) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Paiement enregistré avec succès",
  "data": {
    "id": 8,
    "id_creance": 3,
    "id_compte": 1,
    "montant_payer": 25000.0,
    "mode_paiement": "espèces"
  }
}
```

### GET /v1/vente/paiements/{id}
Détail d'un paiement.

### PUT /v1/vente/paiements/{id}
Modifier un paiement.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `montant_payer` | numeric | ❌ | Nouveau montant (> 0) |
| `mode_paiement` | string | ❌ | Mode de paiement (max 50) |

### DELETE /v1/vente/paiements/{id}
Supprimer un paiement.

---

## Clients — `clients`

### GET /v1/vente/clients
Liste tous les clients.

### POST /v1/vente/clients
Créer un client.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `nom_complet` | string | ✅ | Nom complet (max 150) |
| `telephone` | string | ✅ | Téléphone unique (max 20) |
| `email` | string | ❌ | Email unique |
| `adresse` | string | ❌ | Adresse (max 255) |
| `status` | boolean | ❌ | Actif/inactif (défaut : `true`) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Client créé avec succès",
  "data": {
    "id": 12,
    "nom_complet": "Aliou Diallo",
    "telephone": "771234567",
    "email": null,
    "adresse": "Dakar",
    "status": true
  }
}
```

### GET /v1/vente/clients/{id}
Détail d'un client.

### PUT /v1/vente/clients/{id}
Modifier un client (mêmes champs que POST, tous optionnels).

### DELETE /v1/vente/clients/{id}
Supprimer un client.

---

## Créances — `creances`

### GET /v1/vente/creances
Liste toutes les créances.

### POST /v1/vente/creances
Créer une créance.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_client` | integer | ✅ | ID du client |
| `qte` | numeric | ✅ | Quantité (> 0) |
| `prix_unitaire` | numeric | ✅ | Prix unitaire |
| `id_pompe` | integer | ❌ | ID de la pompe |
| `commentaire` | string | ❌ | Commentaire (max 500) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Créance créée avec succès",
  "data": {
    "id": 7,
    "id_client": 12,
    "qte": 30.0,
    "prix_unitaire": 600.0,
    "montant_total": 18000.0,
    "id_pompe": 2,
    "commentaire": null
  }
}
```

### GET /v1/vente/creances/{id}
Détail d'une créance.

### PUT /v1/vente/creances/{id}
Modifier une créance (mêmes champs que POST).

### DELETE /v1/vente/creances/{id}
Supprimer une créance.

---

## Initiations de vente — `initiation`

### GET /v1/vente/initiation
Liste toutes les lignes de vente.

### POST /v1/vente/initiation
Initier une nouvelle ligne de vente (ouvre une session de vente).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_station` | integer | ✅ | ID de la station |

**Réponse :**
```json
{
  "status": 201,
  "message": "Initiation créée avec succès",
  "data": {
    "id": 25,
    "id_station": 1,
    "status": false,
    "created_at": "2026-03-12T06:00:00Z"
  }
}
```

### GET /v1/vente/initiation/{id}
Détail d'une initiation.

### PUT /v1/vente/initiation/{id}
Mettre à jour une ligne de vente (clôture / correction).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_pompiste` | integer | ✅ | ID du pompiste |
| `id_cuve` | integer | ❌ | ID de la cuve |
| `id_affectation` | integer | ❌ | ID de l'affectation |
| `index_debut` | numeric | ❌ | Index début compteur |
| `index_fin` | numeric | ❌ | Index fin compteur |
| `qte_vendu` | numeric | ❌ | Quantité vendue |
| `retour_cuve` | numeric | ❌ | Retour cuve |
| `commentaire` | string | ❌ | Commentaire |
| `status` | boolean | ❌ | Statut de la ligne (`true` = clôturée) |

### DELETE /v1/vente/initiation/{id}
Supprimer une initiation.
