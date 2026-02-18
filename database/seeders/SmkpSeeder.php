<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SmkpSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BERSIHKAN DATABASE
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Folder::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. BUAT AKUN PENGGUNA
        $roles = User::ROLES ?? ['Admin', 'User']; // Fallback jika konstanta belum ada
        $unitCounter = 1;

        foreach ($roles as $roleName) {
            if ($roleName === 'Auditor') {
                $name = 'Auditor';
                $email = 'auditor@example.com';
            } else {
                $name = 'Unit ' . $unitCounter . ' (' . $roleName . ')';
                $email = 'user' . $unitCounter . '@example.com';
                $unitCounter++;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'role' => $roleName,
                'password' => Hash::make('password'),
            ]);
        }

        // 3. DEFINISI STRUKTUR ISO (TANPA SMKP)
        $isoStructure = [
            [
                'name' => '9001:2015 (Mutu)',
                'subs' => [
                    ['code' => '1', 'name' => 'Ruang Lingkup'],
                    ['code' => '2', 'name' => 'Acuan Normatif'],
                    ['code' => '3', 'name' => 'Istilah dan Definisi'],
                    ['code' => '4', 'name' => 'Konteks Organisasi', 'subs' => [
                        ['code' => '4.1', 'name' => 'Memahami Organisasi dan Konteksnya'],
                        ['code' => '4.2', 'name' => 'Kebutuhan dan Harapan Pihak Berkepentingan'],
                        ['code' => '4.3', 'name' => 'Ruang Lingkup SMM'],
                        ['code' => '4.4', 'name' => 'Sistem Manajemen Mutu'],
                    ]],
                    ['code' => '5', 'name' => 'Kepemimpinan'],
                    ['code' => '6', 'name' => 'Perencanaan'],
                    ['code' => '7', 'name' => 'Dukungan'],
                    ['code' => '8', 'name' => 'Operasi'],
                    ['code' => '9', 'name' => 'Evaluasi Kinerja'],
                    ['code' => '10', 'name' => 'Perbaikan'],
                ]
            ],
            [
                'name' => '14001:2015 (Lingkungan)',
                'subs' => [
                    ['code' => '1', 'name' => 'Ruang Lingkup'],
                    ['code' => '2', 'name' => 'Acuan Normatif'],
                    ['code' => '3', 'name' => 'Istilah dan Definisi', 'subs' => [
                        ['code' => '3.1', 'name' => 'Istilah Organisasi dan Kepemimpinan'],
                        ['code' => '3.2', 'name' => 'Istilah Perencanaan'],
                        ['code' => '3.3', 'name' => 'Istilah Dukungan dan Operasi'],
                        ['code' => '3.4', 'name' => 'Istilah Evaluasi dan Perbaikan'],
                    ]],
                    ['code' => '4', 'name' => 'Konteks Organisasi'],
                    ['code' => '5', 'name' => 'Kepemimpinan'],
                    ['code' => '6', 'name' => 'Perencanaan'],
                    ['code' => '7', 'name' => 'Dukungan'],
                    ['code' => '8', 'name' => 'Operasi'],
                    ['code' => '9', 'name' => 'Evaluasi Kinerja'],
                    ['code' => '10', 'name' => 'Perbaikan'],
                ]
            ],
            [
                'name' => '45001:2018 (K3)',
                'subs' => [
                    ['code' => '1', 'name' => 'Ruang Lingkup'],
                    ['code' => '2', 'name' => 'Acuan Normatif'],
                    ['code' => '3', 'name' => 'Istilah dan Definisi'],
                    ['code' => '4', 'name' => 'Konteks Organisasi'],
                    ['code' => '5', 'name' => 'Kepemimpinan dan Partisipasi Pekerja'],
                    ['code' => '6', 'name' => 'Perencanaan'],
                    ['code' => '7', 'name' => 'Dukungan'],
                    ['code' => '8', 'name' => 'Operasi'],
                    ['code' => '9', 'name' => 'Evaluasi Kinerja'],
                    ['code' => '10', 'name' => 'Peningkatan'],
                ]
            ],
            [
                'name' => '50001:2018 (Energi)',
                'subs' => [
                    ['code' => '1', 'name' => 'Ruang Lingkup'],
                    ['code' => '2', 'name' => 'Acuan Normatif'],
                    ['code' => '3', 'name' => 'Istilah dan Definisi', 'subs' => [
                        ['code' => '3.5', 'name' => 'Istilah Terkait Energi'],
                    ]],
                    ['code' => '4', 'name' => 'Konteks Organisasi'],
                    ['code' => '5', 'name' => 'Kepemimpinan'],
                    ['code' => '6', 'name' => 'Perencanaan'],
                    ['code' => '7', 'name' => 'Dukungan'],
                    ['code' => '8', 'name' => 'Operasi'],
                    ['code' => '9', 'name' => 'Evaluasi Kinerja'],
                    ['code' => '10', 'name' => 'Peningkatan'],
                ]
            ],
            [
                'name' => '37001:2016 (Anti Penyuapan)',
                'subs' => [
                    ['code' => '1', 'name' => 'Ruang Lingkup'],
                    ['code' => '2', 'name' => 'Acuan Normatif'],
                    ['code' => '3', 'name' => 'Istilah dan Definisi'],
                    ['code' => '4', 'name' => 'Konteks Organisasi'],
                    ['code' => '5', 'name' => 'Kepemimpinan'],
                    ['code' => '6', 'name' => 'Perencanaan'],
                    ['code' => '7', 'name' => 'Dukungan'],
                    ['code' => '8', 'name' => 'Operasi'],
                    ['code' => '9', 'name' => 'Evaluasi Kinerja'],
                    ['code' => '10', 'name' => 'Peningkatan'],
                ]
            ],
        ];

        // 4. EKSEKUSI PEMBUATAN FOLDER
        // Langsung menggunakan struktur ISO tanpa digabung dengan SMKP
        $this->createRecursive($isoStructure, null);
    }

    private function createRecursive($items, $parentId)
    {
        foreach ($items as $item) {
            $folder = Folder::create([
                'code' => $item['code'] ?? null,
                'name' => $item['name'],
                'parent_id' => $parentId
            ]);

            if (isset($item['subs'])) {
                $this->createRecursive($item['subs'], $folder->id);
            }
        }
    }
}