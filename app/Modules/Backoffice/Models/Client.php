<?php

namespace App\Modules\Backoffice\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'code',
        'name',
        'email',
        'telephone',
        'adresse',
        'status',
        'database',
        'is_created',
        'is_active',
    ];

    public function licences()
    {
        return $this->hasMany(Licence::class, 'client_id');
    }
}
