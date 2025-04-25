<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Quotation;

class Quotdesc extends Model
{
    protected $fillable = [
        'id_quot',
        'id',
        'parent',
        'desc',
        'qty',
        'unit',
        'price',
        'total',
        'remark'
    ];

    public function quotation() {
        return $this->belongsTo(Quotation::class, 'id_quot', 'id');
    }
}
