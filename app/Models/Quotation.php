<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;
class Quotation extends Model
{
    protected $fillable = [
        'id_user',
        'id_client',
        'id_ticket',
        'quot_id',
        'reff_requisition',
        'equipment',
        'total',
        'status',
        'rev',
        'terms_conditions',
        'disc',
        'disc_type',
        'valid_until',
        'currency'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    public function client() {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id');
    }

    public function quotDesc() {
        return $this->hasMany(Quotdesc::class, 'id_quot', 'id');
    }
}
