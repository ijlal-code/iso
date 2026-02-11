<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;

class SmkpSeeder extends Seeder
{
    public function run(): void
    {
        // Data Struktur SMKP (Saya ambil sebagian contoh dari data Anda untuk mempersingkat, logika sama)
        $structure = [
            'I' => [
                'name' => 'KEBIJAKAN',
                'subs' => [
                    'I.1' => 'Penyusunan Kebijakan',
                    'I.2' => 'Isi Kebijakan',
                    'I.3' => 'Penetapan Kebijakan',
                    'I.4' => 'Komunikasi Kebijakan',
                    'I.5' => 'Tinjauan Kebijakan',
                ]
            ],
            'II' => [
                'name' => 'PERENCANAAN',
                'subs' => [
                    'II.1' => 'Penelaahan Awal',
                    'II.2' => [
                        'name' => 'Manajemen Risiko',
                        'subs' => [
                            'II.2.1' => 'Komunikasi dan konsultasi risiko',
                            'II.2.2' => 'Penetapan konteks risiko',
                            'II.2.3' => 'Identifikasi bahaya',
                            'II.2.4' => 'Penilaian dan pengendalian risiko',
                            'II.2.5' => 'Pemantauan dan peninjauan',
                        ]
                    ],
                    'II.3' => 'Identifikasi dan Kepatuhan Terhadap Ketentuan...',
                    'II.4' => 'Penetapan Tujuan, Sasaran, dan Program',
                    'II.5' => 'Rencana Kerja dan Anggaran Keselamatan Pertambangan',
                ]
            ],
             // ... Tambahkan Bab III sampai VII dengan pola yang sama di sini
        ];

        $this->createFolders($structure, null);
    }

    private function createFolders($nodes, $parentId)
    {
        foreach ($nodes as $code => $data) {
            $name = is_array($data) ? $data['name'] : $data;
            
            $folder = Folder::create([
                'code' => $code,
                'name' => $name,
                'parent_id' => $parentId
            ]);

            if (is_array($data) && isset($data['subs'])) {
                $this->createFolders($data['subs'], $folder->id);
            }
        }
    }
}