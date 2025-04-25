<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'address',
        'office_phone',
        'business',
        'npwp',
        'website',
        'pic_1',
        'rule_1',
        'email_1',
        'phone_1',
        'pic_2',
        'rule_2',
        'email_2'
    ];

    public function ticket() {
        return $this->hasMany(Ticket::class, 'id_client', 'id');
    }
}
