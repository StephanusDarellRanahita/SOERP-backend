<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invdesc extends Model
{
    public $fillable = [
        'id_invoice',
        'id',
        'desc',
        'parent',
        'qty',
        'unit',
        'price',
        'total',
        'remark'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'id');
    }
}
