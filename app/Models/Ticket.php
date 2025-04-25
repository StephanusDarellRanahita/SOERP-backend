<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\Wip;

class Ticket extends Model
{
    protected $fillable = [
        'id_user',
        'id_client',
        'ticket_id',
        'issue',
        'status',
        'assign',
        'type'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function assign() {
        return $this->belongsTo(User::class, 'assign', 'id');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function quotation() {
        return $this->hasMany(Quotation::class,'id_ticket', 'id');
    }

    public function wip() {
        return $this->hasOne(Wip::class, 'id_ticket', 'id');
    }
}
