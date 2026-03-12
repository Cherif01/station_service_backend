# API Documentation — Module Caisse

> **Préfixe** : `/api/v1/caisse/`
> **Auth** : `Authorization: Bearer {token}` requis sur toutes les routes
> **Headers** : `Accept: application/json` · `Content-Type: application/json`

---

## Comptes — `comptes`

### GET /v1/caisse/comptes
Liste tous les comptes de caisse.

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "libelle": "Caisse principale",
      "numero": "CPT-001",
      "solde_initial": 100000.0,
      "solde_actuel": 250000.0,
      "commentaire": null
    }
  ]
}
```

### POST /v1/caisse/comptes
Créer un compte de caisse.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_station` | integer | ✅ | ID de la station |
| `libelle` | string | ✅ | Libellé du compte (max 100) |
| `numero` | string | ✅ | Numéro du compte (max 100) |
| `solde_initial` | numeric | ✅ | Solde initial (≥ 0) |
| `commentaire` | string | ❌ | Commentaire (max 255) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Compte créé avec succès",
  "data": {
    "id": 2,
    "libelle": "Caisse secondaire",
    "numero": "CPT-002",
    "solde_initial": 50000.0
  }
}
```

### GET /v1/caisse/comptes/{id}
Détail d'un compte.

### PUT /v1/caisse/comptes/{id}
Modifier un compte.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ❌ | Libellé (max 100) |
| `commentaire` | string | ❌ | Commentaire (max 255) |

### DELETE /v1/caisse/comptes/{id}
Supprimer un compte.

---

## Catégories de charges — `categori-charges`

### GET /v1/caisse/categori-charges
Liste toutes les catégories de charges.

### POST /v1/caisse/categori-charges
Créer une catégorie de charge.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | Libellé de la catégorie (max 150) |
| `is_fixed` | boolean | ❌ | Charge fixe ou variable (défaut : `false`) |
| `status` | boolean | ❌ | Actif/inactif (défaut : `true`) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Catégorie créée avec succès",
  "data": {
    "id": 3,
    "libelle": "Carburant véhicules",
    "is_fixed": false,
    "status": true
  }
}
```

### GET /v1/caisse/categori-charges/{id}
Détail d'une catégorie.

### PUT /v1/caisse/categori-charges/{id}
Modifier une catégorie (mêmes champs que POST, tous optionnels).

### DELETE /v1/caisse/categori-charges/{id}
Supprimer une catégorie.

---

## Opérations de charges — `operations-charges`

### GET /v1/caisse/operations-charges
Liste toutes les opérations de charges.

### POST /v1/caisse/operations-charges
Enregistrer une opération de charge.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_compte` | integer | ✅ | ID du compte débité |
| `id_charge_category` | integer | ✅ | ID de la catégorie de charge |
| `montant` | numeric | ✅ | Montant (> 0) |
| `commentaire` | string | ❌ | Commentaire (max 255) |
| `status` | boolean | ❌ | Statut de l'opération |

**Réponse :**
```json
{
  "status": 201,
  "message": "Opération de charge enregistrée avec succès",
  "data": {
    "id": 5,
    "id_compte": 1,
    "id_charge_category": 3,
    "montant": 15000.0,
    "commentaire": null
  }
}
```

### GET /v1/caisse/operations-charges/{id}
Détail d'une opération de charge.

### PUT /v1/caisse/operations-charges/{id}
Modifier une opération de charge.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_compte` | integer | ❌ | ID du compte |
| `id_charge_category` | integer | ❌ | ID catégorie de charge |
| `montant` | numeric | ❌ | Montant (> 0) |
| `commentaire` | string | ❌ | Commentaire (max 255) |
| `status` | boolean | ❌ | Statut |

### DELETE /v1/caisse/operations-charges/{id}
Supprimer une opération de charge.

---

## Types d'opérations — `type-operations`

### GET /v1/caisse/type-operations
Liste tous les types d'opérations.

### POST /v1/caisse/type-operations
Créer un type d'opération.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ✅ | Libellé (max 100) |
| `commentaire` | string | ✅ | Description (max 255) |
| `nature` | string | ✅ | `0` = sortie · `1` = entrée · `2` = transfert |

**Réponse :**
```json
{
  "status": 201,
  "message": "Type d'opération créé avec succès",
  "data": {
    "id": 4,
    "libelle": "Virement entrant",
    "commentaire": "Réception de fonds",
    "nature": "1"
  }
}
```

### GET /v1/caisse/type-operations/{id}
Détail d'un type d'opération.

### PUT /v1/caisse/type-operations/{id}
Modifier un type d'opération.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `libelle` | string | ❌ | Libellé (max 100) |
| `commentaire` | string | ❌ | Description (max 255) |
| `nature` | string | ❌ | `0`, `1` ou `2` |

### DELETE /v1/caisse/type-operations/{id}
Supprimer un type d'opération.

---

## Opérations de compte — `operations`

### GET /v1/caisse/operations
Liste toutes les opérations de compte.

### POST /v1/caisse/operations
Enregistrer une opération (entrée ou sortie).

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_compte` | integer | ✅ | ID du compte |
| `id_type_operation` | integer | ✅ | ID du type d'opération |
| `montant` | numeric | ✅ | Montant (> 0) |
| `reference` | string | ❌ | Référence de l'opération (max 100) |
| `commentaire` | string | ❌ | Commentaire (max 255) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Opération enregistrée avec succès",
  "data": {
    "id": 12,
    "id_compte": 1,
    "id_type_operation": 2,
    "montant": 30000.0,
    "reference": "OP-2026-001",
    "commentaire": null
  }
}
```

### GET /v1/caisse/operations/{id}
Détail d'une opération.

### PUT /v1/caisse/operations/{id}
Modifier une opération (mêmes champs que POST, tous optionnels).

### DELETE /v1/caisse/operations/{id}
Supprimer une opération.

---

## Transfert inter-comptes

### POST /v1/caisse/operations/transfert
Initier un transfert entre deux comptes.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `id_source` | integer | ✅ | ID du compte source |
| `id_destination` | integer | ✅ | ID du compte destination (différent de la source) |
| `montant` | numeric | ✅ | Montant à transférer (> 0) |
| `reference` | string | ❌ | Référence du transfert (max 100) |
| `commentaire` | string | ❌ | Commentaire (max 255) |

**Réponse :**
```json
{
  "status": 201,
  "message": "Transfert initié avec succès",
  "data": {
    "id": 8,
    "id_source": 1,
    "id_destination": 2,
    "montant": 50000.0,
    "reference": "TRF-2026-001",
    "statut": "en_attente"
  }
}
```

### GET /v1/caisse/transfert
Liste tous les transferts inter-comptes.

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "id": 8,
      "id_source": 1,
      "id_destination": 2,
      "montant": 50000.0,
      "reference": "TRF-2026-001",
      "statut": "en_attente",
      "created_at": "2026-03-12T10:00:00Z"
    }
  ]
}
```

### POST /v1/caisse/operations/transfert/confirm
Confirmer un transfert en attente.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `reference` | string | ✅ | Référence du transfert à confirmer |

**Réponse :**
```json
{
  "status": 200,
  "message": "Transfert confirmé avec succès"
}
```

### POST /v1/caisse/operations/transfert/cancel
Annuler un transfert en attente.

**Body :**
| Champ | Type | | Description |
|---|---|---|---|
| `reference` | string | ✅ | Référence du transfert à annuler |

**Réponse :**
```json
{
  "status": 200,
  "message": "Transfert annulé avec succès"
}
```

---

## Opérations par période

### GET /v1/caisse/operations-comptes/periode
Liste les opérations de compte filtrées par période.

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
      "id": 12,
      "id_compte": 1,
      "id_type_operation": 2,
      "montant": 30000.0,
      "reference": "OP-2026-001",
      "created_at": "2026-03-10T08:00:00Z"
    }
  ]
}
```

### GET /v1/caisse/transfert-intercomptes/periode
Liste les transferts inter-comptes filtrés par période.

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
      "id": 8,
      "id_source": 1,
      "id_destination": 2,
      "montant": 50000.0,
      "reference": "TRF-2026-001",
      "statut": "confirmé",
      "created_at": "2026-03-10T10:00:00Z"
    }
  ]
}
```

---

## GET /v1/caisse/resume-mensuel
Résumé mensuel des opérations de caisse regroupées par mois.

**Query params :**
| Paramètre | Type | | Description |
|---|---|---|---|
| `annee` | integer | ❌ | Année (défaut : année courante) |

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "mois": "2026-01",
      "total_entrees": 500000.0,
      "total_sorties": 320000.0,
      "solde_net": 180000.0
    },
    {
      "mois": "2026-02",
      "total_entrees": 620000.0,
      "total_sorties": 410000.0,
      "solde_net": 210000.0
    }
  ]
}
```
