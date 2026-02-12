<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Daftar Role yang tersedia
     */
    const ROLES = [
        'Auditor', // ROLE UTAMA (SUPER USER UNTUK FILE)
        'KTT',
        'Pengelola Sistem',
        'Audit Internal',
        'Pengelola Risiko',
        'Pengelola Legal',
        'Pengelola K3 & Lingk.',
        'Pengel. SDM & Diklat',
        'Pengawas Operasional',
        'Bag. K3 & KO Pertamb.',
        'PJO',
        'Pengawas Oper. PJO',
        'Pengawas Teknik PJO',
        'Bag. K3 & KO PJO',
    ];

    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // Helper untuk cek role
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}