# API Caisse

**Base URL :** `/api/v1/caisse`
**Auth :** `Authorization: Bearer {token}` requis sur toutes les routes.

---

## 1. COMPTES

| Méthode | URL | Action |
|---|---|---|
| GET | `/comptes` | Liste |
| POST | `/comptes` | Créer |
| GET | `/comptes/{id}` | Détail |
| PUT | `/comptes/{id}` | Modifier |
| DELETE | `/comptes/{id}` | Supprimer |

**POST payload :**
```json
{
  "id_station": 1,
  "libelle": "Caisse principale",
  "numero": "CPT-001",
  "solde_initial": 500000,
  "commentaire": "..."
}
```

**Réponse :**
```json
{
  "status": 201,
  "data": {
    "id": 1,
    "libelle": "Caisse principale",
    "numero": "CPT-001",
    "solde_initial": 500000.0,
    "solde_actuel": 500000.0,
    "station": {
      "id": 1,
      "libelle": "Station Centrale",
      "dernier_gerant": {
        "name": "Ibrahima Sow",
        "email": "ibra@spa.sn",
        "telephone": "771234567"
      }
    },
    "created_by": "Admin",
    "created_at": "2026-03-22 08:00:00"
  }
}
```

---

## 2. CATÉGORIES DE CHARGES

| Méthode | URL | Action |
|---|---|---|
| GET | `/categori-charges` | Liste |
| POST | `/categori-charges` | Créer |
| GET | `/categori-charges/{id}` | Détail |
| PUT | `/categori-charges/{id}` | Modifier |
| DELETE | `/categori-charges/{id}` | Supprimer |

**POST payload :**
```json
{
  "libelle": "Carburant véhicules",
  "is_fixed": false,
  "status": true
}
```

---

## 3. OPÉRATIONS DE CHARGES

| Méthode | URL | Action |
|---|---|---|
| GET | `/operations-charges` | Liste |
| POST | `/operations-charges` | Créer |
| GET | `/operations-charges/{id}` | Détail |
| PUT | `/operations-charges/{id}` | Modifier |
| DELETE | `/operations-charges/{id}` | Supprimer |

**POST payload :**
```json
{
  "id_compte": 1,
  "id_charge_category": 2,
  "montant": 25000,
  "commentaire": "Achat fournitures",
  "status": true
}
```

> `id_station` est injecté automatiquement depuis la station active.

**PUT payload (tous optionnels) :**
```json
{
  "id_compte": 2,
  "id_charge_category": 3,
  "montant": 30000,
  "commentaire": "Mise à jour",
  "status": false
}
```

**Réponse :**
```json
{
  "status": 200,
  "data": {
    "id": 1,
    "station": { "id": 1, "libelle": "Station Centrale" },
    "categorie": "Carburant véhicules",
    "compte": "Caisse principale",
    "montant": 25000.0,
    "commentaire": "Achat fournitures",
    "status": true,
    "created_by": "Admin",
    "created_at": "2026-03-22 09:00:00"
  }
}
```

---

## 4. TYPES D'OPÉRATIONS

| Méthode | URL | Action |
|---|---|---|
| GET | `/type-operations` | Liste |
| POST | `/type-operations` | Créer |
| GET | `/type-operations/{id}` | Détail |
| PUT | `/type-operations/{id}` | Modifier |
| DELETE | `/type-operations/{id}` | Supprimer |

> `nature` : `0` = sortie · `1` = entrée · `2` = transfert inter-station

---

## 5. OPÉRATIONS COMPTE (entrée / sortie)

| Méthode | URL | Action |
|---|---|---|
| GET | `/operations` | Liste (hors transferts) |
| POST | `/operations` | Créer |
| GET | `/operations/{id}` | Détail |
| PUT | `/operations/{id}` | Modifier |
| DELETE | `/operations/{id}` | Supprimer |

**POST payload :**
```json
{
  "id_compte": 1,
  "id_type_operation": 2,
  "montant": 100000,
  "reference": "REF-001",
  "commentaire": "Versement"
}
```

> Si `nature = 0` (sortie) et `montant > solde_actuel` → **409** Solde insuffisant.
> Si `nature = 2` (transfert) → **409** Utiliser la route `/operations/transfert`.

**GET par période :** `/operations-comptes/periode?date_debut=2026-03-01&date_fin=2026-03-22`

---

## 6. TRANSFERTS INTER-COMPTES

### Initier — POST `/operations/transfert`

```json
{
  "id_source": 1,
  "id_destination": 2,
  "montant": 50000,
  "commentaire": "Transfert vers agence"
}
```

**Réponse :**
```json
{
  "status": 201,
  "message": "Transfert envoyé et en attente de confirmation.",
  "reference": "TRF-XXXX"
}
```

> Statut = `en_attente` — le solde n'est pas encore débité.

---

### Confirmer — POST `/operations/transfert/confirm`

```json
{ "reference": "TRF-XXXX" }
```

> Solde débité de la source et crédité sur la destination. Statut → `effectif`.

---

### Annuler — POST `/operations/transfert/cancel`

```json
{ "reference": "TRF-XXXX" }
```

> Statut → `annule`. Solde non impacté.

---

### Liste transferts

| URL | Description |
|---|---|
| GET `/transfert` | Tous les transferts |
| GET `/transfert-intercomptes/periode?date_debut=...&date_fin=...` | Par période |

---

## 7. RÉSUMÉ MENSUEL

**GET** `/resume-mensuel?annee=2026`

**Réponse :**
```json
{
  "status": 200,
  "data": [
    {
      "mois": "janvier",
      "ventes_directes": 1200000.0,
      "paiements_creances": 300000.0,
      "total_ventes": 1500000.0,
      "total_depenses": 200000.0,
      "benefice": 1300000.0
    }
    // ... 12 mois
  ]
}
```

---

## Codes d'erreur

| Code | Signification |
|---|---|
| 400 | Station active non détectée |
| 404 | Ressource introuvable |
| 409 | Conflit métier (solde insuffisant, même compte source/destination...) |
| 422 | Erreur de validation |
| 500 | Erreur serveur |
