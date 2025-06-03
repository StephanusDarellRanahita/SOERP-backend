<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pa;

class Paatt extends Model
{
    protected $fillable = [
        'id_pa',
        'path',
        'name'
    ];

    public function pa()
    {
        return $this->belongsTo(Pa::class, 'id_pa', 'id');
    }
}
