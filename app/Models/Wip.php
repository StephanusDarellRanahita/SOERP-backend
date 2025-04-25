<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket;

class Wip extends Model
{
    protected $fillable = [
        'id_ticket',
        'wip_id',
        'status',
        'bast',
        'job_report',
        'quotation',
        'delivery_note',
        'others'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id');
    }

    public function wipAtt()
    {
        return $this->hasMany(WipAtt::class, 'id_wip', 'id');
    }
}
