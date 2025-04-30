<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\File;

class Folder extends Model
{
    protected $fillable = [
        'id_folder',
        'name',
        'folder',
        'company'
    ];

    public function file()
    {
        return $this->hasMany(File::class, 'id_folder', 'id');
    }
}
