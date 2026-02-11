<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Folder extends Model
{
    protected $guarded = [];

    // Relasi ke sub-folder (anak)
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // Relasi ke parent folder
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // Relasi ke file
    public function files(): HasMany
    {
        return $this->hasMany(FileUpload::class);
    }
}