<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Invoice;

class Pa extends Model
{
    protected $fillable = [
        'applicant',
        'ref_inv',
        'pa_id',
        'bank',
        'bank_account',
        'desc',
        'category',
        'project',
        'operation_device',
        'remark',
        'total',
        'currency',
        'rev',
        'status'
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'ref_inv', 'id');
    }

    public function paitem()
    {
        return $this->hasMany(Paitem::class, 'id_pa', 'id');
    }

    public function paatt()
    {
        return $this->hasMany(Paatt::class, 'id_pa', 'id');
    }
}
