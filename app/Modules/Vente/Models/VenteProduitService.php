<?php

namespace App\Modules\Vente\Models;

use Illuminate\Database\Eloquent\Model;

class VenteProduitService extends Model
{
    protected $table = 'vente_produits_services';

    protected $fillable = [
        'id_init_vente',
        'id_produit',
        'id_service',
        'qte_vendu',
        'prix_unitaire',
    ];

    protected $casts = [
        'qte_vendu'     => 'float',
        'prix_unitaire' => 'float',
    ];

    public function vente()
    {
        return $this->belongsTo(InitVente::class, 'id_init_vente');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'id_produit');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }
}
