<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Wip;
use App\Models\User;

class WipAtt extends Model
{
    protected $fillable = [
        'id_wip',
        'id_user',
        'photo',
        'photo_desc',
        'desc'
    ];

    public function wip()
    {
        return $this->belongsTo(Wip::class, 'id_wip', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
