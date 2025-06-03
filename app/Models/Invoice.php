<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;

class Invoice extends Model
{
    public $fillable = [
        'id_user',
        'id_quotation',
        'id_ticket',
        'id_client',
        'invoice_id',
        'reff_requisition',
        'equipment',
        'total',
        'status',
        'rev',
        'terms_conditions',
        'disc',
        'disc_type',
        'currency',
        'valid_until'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id');
    }

    public function invdesc()
    {
        return $this->hasMany(Invdesc::class, 'id_invoice', 'id');
    }
}
