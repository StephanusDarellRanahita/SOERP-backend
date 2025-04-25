<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $fillable = [
        'id_user',
        'company',
        'action'
    ];
}
