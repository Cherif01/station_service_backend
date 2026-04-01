# API — Versements Direction

**Base URL :** `{BASE_URL}/api/v1/caisse`
**Auth :** `Authorization: Bearer {token}`

---

## 1. Comptes Direction

---

### GET `/comptes-direction`
> Liste tous les comptes direction.
> **Rôles :** `admin`, `super_admin`

**Réponse 200**
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "numero": "DIR-001",
      "libelle": "Caisse Centrale Direction",
      "commentaire": null,
      "solde_initial": 0.00,
      "solde_actuel": 1500000.00,
      "created_by": "Administrateur",
      "modify_by": null,
      "created_at": "2026-04-01 08:00:00",
      "updated_at": "2026-04-01 08:00:00"
    }
  ]
}
```

---

### GET `/comptes-direction/{id}`
> Détail d'un compte direction.
> **Rôles :** `admin`, `super_admin`

**Réponse 200**
```json
{
  "status": 200,
  "data": {
    "id": 1,
    "numero": "DIR-001",
    "libelle": "Caisse Centrale Direction",
    "commentaire": null,
    "solde_initial": 0.00,
    "solde_actuel": 1500000.00,
    "created_by": "Administrateur",
    "modify_by": null,
    "created_at": "2026-04-01 08:00:00",
    "updated_at": "2026-04-01 08:00:00"
  }
}
```

**Réponse 404**
```json
{ "status": 404, "message": "Compte direction introuvable." }
```

---

### POST `/comptes-direction`
> Créer un compte direction.
> **Rôles :** `admin`, `super_admin`

**Body**
```json
{
  "numero": "DIR-001",            // requis — unique
  "libelle": "Caisse Direction",  // optionnel
  "commentaire": "",              // optionnel
  "solde_initial": 0              // optionnel — défaut : 0
}
```

**Réponse 200**
```json
{
  "status": 200,
  "message": "Compte direction créé avec succès.",
  "data": {
    "id": 1,
    "numero": "DIR-001",
    "libelle": "Caisse Direction",
    "commentaire": null,
    "solde_initial": 0.00,
    "solde_actuel": 0.00,
    "created_by": "Administrateur",
    "modify_by": null,
    "created_at": "2026-04-01 08:00:00",
    "updated_at": "2026-04-01 08:00:00"
  }
}
```

---

### PUT `/comptes-direction/{id}`
> Modifier un compte direction.
> **Rôles :** `admin`, `super_admin`

**Body** — tous les champs sont optionnels
```json
{
  "numero": "DIR-001",
  "libelle": "Caisse Centrale Direction",
  "commentaire": "Compte principal",
  "solde_initial": 0
}
```

**Réponse 200**
```json
{
  "status": 200,
  "message": "Compte direction mis à jour.",
  "data": { ... }
}
```

---

### DELETE `/comptes-direction/{id}`
> Supprimer un compte direction.
> **Rôles :** `admin`, `super_admin`

**Réponse 200**
```json
{ "status": 200, "message": "Compte direction supprimé." }
```

**Réponse 409** — versements en attente liés
```json
{ "status": 409, "message": "Impossible de supprimer : des versements en attente sont liés à ce compte." }
```

---

## 2. Versements Direction

---

### GET `/versements-direction`
> Liste les versements direction.
> **Rôles :** tous — `admin`/`super_admin` voient toutes les stations, `gerant`/`superviseur` voient uniquement leur station.

**Réponse 200**
```json
{
  "status": 200,
  "data": [
    {
      "id": 12,
      "reference": "OPC-20260401-X7K2MP",
      "montant": 500000.00,
      "mode": "especes",
      "status": "en_attente",
      "commentaire": "Versement semaine du 28/03",
      "source": {
        "id": 3,
        "numero": "CPT-001",
        "libelle": "Caisse Station A",
        "station": { "id": 1, "libelle": "Station Total Kipé" }
      },
      "compte_direction": {
        "id": 1,
        "numero": "DIR-001",
        "libelle": "Caisse Centrale Direction"
      },
      "type_operation": { "id": 2, "libelle": "Transfert", "nature": 2 },
      "created_by": "Mamadou Balde",
      "modify_by": null,
      "created_at": "2026-04-01 09:15:00",
      "updated_at": "2026-04-01 09:15:00"
    }
  ]
}
```

---

### GET `/versements-direction/periode?date_debut=&date_fin=`
> Liste les versements filtrés par période.
> **Rôles :** tous (même visibilité que ci-dessus)

**Paramètres query**

| Paramètre | Format | Exemple |
|-----------|--------|---------|
| `date_debut` | YYYY-MM-DD | `2026-04-01` |
| `date_fin` | YYYY-MM-DD | `2026-04-30` |

**Réponse 200** — même structure que `/versements-direction`

---

### POST `/versements-direction/initier`
> Le gérant initie un versement vers la Direction. Le solde n'est **pas encore débité** (statut `en_attente`).
> **Rôles :** `gerant`, `superviseur`, `admin`

**Body**
```json
{
  "id_source": 3,               // requis — ID du compte station (source)
  "id_compte_direction": 1,     // requis — ID du compte direction (destination)
  "montant": 500000,            // requis — doit être > 0 et ≤ solde disponible
  "mode": "especes",            // requis — banque | especes | virement | chéque
  "commentaire": "Versement semaine du 28/03"  // optionnel
}
```

**Réponse 200**
```json
{
  "status": 200,
  "message": "Versement initié, en attente de validation par la Direction.",
  "reference": "OPC-20260401-X7K2MP"
}
```

**Réponse 403** — gérant tente de verser depuis une autre station
```json
{ "status": 403, "message": "Vous ne pouvez verser que depuis le compte de votre station." }
```

**Réponse 409** — solde insuffisant
```json
{ "status": 409, "message": "Solde insuffisant sur le compte source." }
```

---

### POST `/versements-direction/confirmer`
> L'admin valide le versement. Le solde station est **débité** et le compte direction est **crédité**.
> **Rôles :** `admin`, `super_admin` uniquement

**Body**
```json
{
  "reference": "OPC-20260401-X7K2MP"   // requis
}
```

**Réponse 200**
```json
{ "status": 200, "message": "Versement validé avec succès." }
```

**Réponse 403** — rôle insuffisant
```json
{ "status": 403, "message": "Seul un administrateur peut valider un versement." }
```

**Réponse 404** — référence introuvable ou déjà traitée
```json
{ "status": 404, "message": "Versement introuvable ou déjà traité." }
```

**Réponse 409** — solde insuffisant au moment de la confirmation
```json
{ "status": 409, "message": "Solde insuffisant sur le compte source au moment de la confirmation." }
```

---

### POST `/versements-direction/annuler`
> L'admin rejette un versement en attente. Aucun impact sur les soldes.
> **Rôles :** `admin`, `super_admin` uniquement

**Body**
```json
{
  "reference": "OPC-20260401-X7K2MP"   // requis
}
```

**Réponse 200**
```json
{ "status": 200, "message": "Versement annulé." }
```

**Réponse 403** — rôle insuffisant
```json
{ "status": 403, "message": "Seul un administrateur peut annuler un versement." }
```

**Réponse 404** — référence introuvable ou déjà traitée
```json
{ "status": 404, "message": "Versement introuvable ou déjà traité." }
```

---

## 3. Valeurs de référence

### `status`
| Valeur | Signification | Soldes impactés |
|--------|--------------|-----------------|
| `en_attente` | Initié, en attente de validation | Non |
| `effectif` | Validé par l'admin | Oui |
| `annule` | Rejeté | Non |

### `mode`
| Valeur | Libellé |
|--------|---------|
| `banque` | Dépôt bancaire |
| `especes` | Remise en espèces |
| `virement` | Virement bancaire |
| `chéque` | Paiement par chèque |

---

## 4. Codes d'erreur

| Code | Signification |
|------|--------------|
| `200` | Succès |
| `403` | Accès refusé (rôle ou station incorrecte) |
| `404` | Ressource introuvable |
| `409` | Conflit (solde insuffisant, versement déjà traité…) |
| `422` | Champs invalides |
| `500` | Erreur serveur |

---

*SPA Technology — 01/04/2026*
