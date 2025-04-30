<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Folder;

class File extends Model
{
    protected $fillable = [
        'id_folder',
        'name',
        'file',
        'size',
        'type',
        'company'
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'id_folder', 'id');
    }
}
