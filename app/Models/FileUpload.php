<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $guarded = [];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}