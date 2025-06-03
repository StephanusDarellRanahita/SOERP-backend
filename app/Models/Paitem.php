<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pa;

class Paitem extends Model
{
    protected $fillable = [
        'id_pa',
        'item',
        'propose_price',
        'approve_price'
    ];

    public function pa()
    {
        return $this->belongsTo(Pa::class, 'id_pa', 'id');
    }
}
