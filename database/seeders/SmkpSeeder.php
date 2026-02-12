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
        // Daftar Role sesuai permintaan
        $roles = [
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

        $unitCounter = 1;

        foreach ($roles as $roleName) {
            // Logika Penamaan:
            // Jika role adalah 'Audit Internal', nama = 'Auditor'
            // Selain itu, nama = 'Unit X' (X nambah terus)
            
            if ($roleName === 'auditor') {
                $name = 'auditor';
            } else {
                $name = 'Unit ' . $unitCounter;
                $unitCounter++;
            }

            User::create([
                'name' => $name,          // Nama: Unit 1, Unit 2... atau Auditor
                'role' => $roleName,      // Role: KTT, Pengelola Sistem...
                'password' => Hash::make('password'),
            ]);
        }

        // 3. BUAT STRUKTUR FOLDER
        $structure = [
            [
                'code' => 'I', 'name' => 'KEBIJAKAN',
                'subs' => [
                    ['code' => 'I.1', 'name' => 'Penyusunan Kebijakan'],
                    ['code' => 'I.2', 'name' => 'Isi Kebijakan'],
                    ['code' => 'I.3', 'name' => 'Penetapan Kebijakan'],
                    ['code' => 'I.4', 'name' => 'Komunikasi Kebijakan'],
                    ['code' => 'I.5', 'name' => 'Tinjauan Kebijakan'],
                ]
            ],
            [
                'code' => 'II', 'name' => 'PERENCANAAN',
                'subs' => [
                    ['code' => 'II.1', 'name' => 'Penelaahan Awal'],
                    ['code' => 'II.2', 'name' => 'Manajemen Risiko', 'subs' => [
                        ['code' => 'II.2.1', 'name' => 'Komunikasi dan konsultasi risiko'],
                        ['code' => 'II.2.2', 'name' => 'Penetapan konteks risiko'],
                        ['code' => 'II.2.3', 'name' => 'Identifikasi bahaya'],
                        ['code' => 'II.2.4', 'name' => 'Penilaian dan pengendalian risiko'],
                        ['code' => 'II.2.5', 'name' => 'Pemantauan dan peninjauan'],
                    ]],
                    ['code' => 'II.3', 'name' => 'Identifikasi dan Kepatuhan Terhadap Ketentuan Peraturan Perundang-undangan'],
                    ['code' => 'II.4', 'name' => 'Penetapan Tujuan, Sasaran, dan Program'],
                    ['code' => 'II.5', 'name' => 'Rencana Kerja dan Anggaran Keselamatan Pertambangan'],
                ]
            ],
            [
                'code' => 'III', 'name' => 'ORGANISASI DAN PERSONEL',
                'subs' => [
                    ['code' => 'III.1', 'name' => 'Penyusunan dan Penetapan Struktur Organisasi, Tugas, Tanggung Jawab, dan Wewenang'],
                    ['code' => 'III.2', 'name' => 'Penunjukan KTT, Kepala Tambang Bawah Tanah, dan/atau Kepala Kapal Keruk', 'subs' => [
                        ['code' => 'III.2.1', 'name' => 'Penunjukan KTT'],
                        ['code' => 'III.2.2', 'name' => 'Penunjukan Kepala Tambang Bawah Tanah'],
                        ['code' => 'III.2.3', 'name' => 'Penunjukan Kepala Kapal Keruk'],
                    ]],
                    ['code' => 'III.3', 'name' => 'Penunjukan PJO Untuk Perusahaan Jasa Pertambangan'],
                    ['code' => 'III.4', 'name' => 'Pembentukan dan Penetapan Bagian K3 Pertambangan dan KO Pertambangan'],
                    ['code' => 'III.5', 'name' => 'Penunjukan Pengawas Operasional dan Pengawas Teknik'],
                    ['code' => 'III.6', 'name' => 'Penunjukan Tenaga Teknik Khusus Pertambangan'],
                    ['code' => 'III.7', 'name' => 'Pembentukan dan Penetapan Komite Keselamatan Pertambangan'],
                    ['code' => 'III.8', 'name' => 'Penunjukan Tim Tanggap Darurat'],
                    ['code' => 'III.9', 'name' => 'Seleksi dan Penempatan Personel'],
                    ['code' => 'III.10', 'name' => 'Penyelenggaraan dan Pelaksanaan Pendidikan dan Pelatihan Serta Kompetensi Kerja', 'subs' => [
                        ['code' => 'III.10.1', 'name' => 'Pendidikan dan pelatihan pekerja tambang'],
                        ['code' => 'III.10.2', 'name' => 'Kompetensi Kerja'],
                    ]],
                    ['code' => 'III.11', 'name' => 'Penyusunan, Penetapan, dan Penerapan Komunikasi Keselamatan Pertambangan'],
                    ['code' => 'III.12', 'name' => 'Pengelolaan Administrasi Keselamatan Pertambangan', 'subs' => [
                        ['code' => 'III.12.1', 'name' => 'Buku tambang'],
                        ['code' => 'III.12.2', 'name' => 'Buku daftar kecelakaan tambang'],
                        ['code' => 'III.12.3', 'name' => 'Pelaporan pengelolaan Keselamatan Pertambangan'],
                        ['code' => 'III.12.4', 'name' => 'Dokumentasi Kejadian Berbahaya dan penyakit akibat kerja'],
                        ['code' => 'III.12.5', 'name' => 'Dokumen dan Laporan Pemenuhan Kompetensi dan Persyaratan Lainnya'],
                    ]],
                    ['code' => 'III.13', 'name' => 'Penyusunan, Penerapan, dan Pendokumentasian Prosedur SMKP Minerba'],
                ]
            ],
            [
                'code' => 'IV', 'name' => 'IMPLEMENTASI',
                'subs' => [
                    ['code' => 'IV.1', 'name' => 'Pelaksanaan Pengelolaan Operasional', 'subs' => [
                        ['code' => 'IV.1.1', 'name' => 'Prosedur Operasi / Kerja'],
                        ['code' => 'IV.1.2', 'name' => 'Izin kerja khusus'],
                        ['code' => 'IV.1.3', 'name' => 'Alat pelindung diri dan alat keselamatan'],
                    ]],
                    ['code' => 'IV.2', 'name' => 'Pelaksanaan Pengelolaan Lingkungan Kerja', 'subs' => [
                        ['code' => 'IV.2.1', 'name' => 'Bahaya Debu'],
                        ['code' => 'IV.2.2', 'name' => 'Bahaya Kebisingan'],
                        ['code' => 'IV.2.3', 'name' => 'Bahaya Getaran'],
                        ['code' => 'IV.2.4', 'name' => 'Bahaya Pencahayaan'],
                        ['code' => 'IV.2.5', 'name' => 'Kuantitas dan Kualitas Udara Kerja'],
                        ['code' => 'IV.2.6', 'name' => 'Iklim Kerja'],
                        ['code' => 'IV.2.7', 'name' => 'Bahaya Radiasi'],
                        ['code' => 'IV.2.8', 'name' => 'Faktor Kimia'],
                        ['code' => 'IV.2.9', 'name' => 'Faktor Biologi'],
                        ['code' => 'IV.2.10', 'name' => 'Kebersihan Lingkungan Kerja'],
                    ]],
                    ['code' => 'IV.3', 'name' => 'Pelaksanaan Pengelolaan Kesehatan Kerja', 'subs' => [
                        ['code' => 'IV.3.1', 'name' => 'Pemeriksaan Kesehatan'],
                        ['code' => 'IV.3.2', 'name' => 'Pelayanan Kesehatan Kerja'],
                        ['code' => 'IV.3.3', 'name' => 'Pertolongan Pertama pada Kecelakaan'],
                        ['code' => 'IV.3.4', 'name' => 'Pengelolaan Kelelahan Kerja (Fatigue)'],
                        ['code' => 'IV.3.5', 'name' => 'Pengelolaan Pekerja pada Tempat Risiko Kesehatan Tinggi'],
                        ['code' => 'IV.3.6', 'name' => 'Pengelolaan Rekaman Data Kesehatan Kerja'],
                        ['code' => 'IV.3.7', 'name' => 'Pengelolaan Higiene dan Sanitasi'],
                        ['code' => 'IV.3.8', 'name' => 'Pengelolaan Ergonomi'],
                        ['code' => 'IV.3.9', 'name' => 'Pengelolaan Makanan, Minuman dan Gizi Pekerja'],
                        ['code' => 'IV.3.10', 'name' => 'Diagnosis dan Pemeriksaan Penyakit Akibat Kerja'],
                    ]],
                    ['code' => 'IV.4', 'name' => 'Pelaksanaan Pengelolaan KO Pertambangan', 'subs' => [
                        ['code' => 'IV.4.1', 'name' => 'Sistem dan pelaksanaan pemeliharaan sarana prasarana'],
                        ['code' => 'IV.4.2', 'name' => 'Pengamanan instalasi'],
                        ['code' => 'IV.4.3', 'name' => 'Kelayakan sarana, prasarana, instalasi, dan peralatan'],
                        ['code' => 'IV.4.4', 'name' => 'Kompetensi tenaga teknik'],
                        ['code' => 'IV.4.5', 'name' => 'Evaluasi Laporan Hasil Kajian Teknis Pertambangan'],
                    ]],
                    ['code' => 'IV.5', 'name' => 'Pelaksanaan Pengelolaan Bahan Peledak dan Peledakan', 'subs' => [
                        ['code' => 'IV.5.1', 'name' => 'Gudang bahan peledak'],
                        ['code' => 'IV.5.2', 'name' => 'Penyimpanan bahan peledak'],
                        ['code' => 'IV.5.3', 'name' => 'Pengangkutan bahan peledak'],
                        ['code' => 'IV.5.4', 'name' => 'Pekerjaan peledakan'],
                    ]],
                    ['code' => 'IV.6', 'name' => 'Penetapan Sistem Perancangan dan Rekayasa', 'subs' => [
                        ['code' => 'IV.6.1', 'name' => 'Perancangan dan rekayasa'],
                        ['code' => 'IV.6.2', 'name' => 'Perubahan'],
                    ]],
                    ['code' => 'IV.7', 'name' => 'Penetapan Sistem Pembelian'],
                    ['code' => 'IV.8', 'name' => 'Pemantauan dan Pengelolaan Perusahaan Jasa Pertambangan', 'subs' => [
                        ['code' => 'IV.8.1', 'name' => 'Persyaratan, seleksi dan penetapan'],
                        ['code' => 'IV.8.2', 'name' => 'Tanggung jawab, pemantauan dan pelaporan'],
                        ['code' => 'IV.8.3', 'name' => 'Evaluasi perusahaan jasa pertambangan'],
                    ]],
                    ['code' => 'IV.9', 'name' => 'Pengelolaan Keadaan Darurat'],
                    ['code' => 'IV.10', 'name' => 'Penyediaan dan Penyiapan P3K'],
                    ['code' => 'IV.11', 'name' => 'Pelaksanaan keselamatan di luar pekerjaan (off the job safety)'],
                ]
            ],
            [
                'code' => 'V', 'name' => 'PEMANTAUAN, EVALUASI DAN TINDAK LANJUT',
                'subs' => [
                    ['code' => 'V.1', 'name' => 'Pemantauan dan pengukuran kinerja', 'subs' => [
                        ['code' => 'V.1.1', 'name' => 'Pencapaian Tujuan, Sasaran, dan program'],
                        ['code' => 'V.1.2', 'name' => 'Kinerja Pengelolaan lingkungan kerja'],
                        ['code' => 'V.1.3', 'name' => 'Kinerja Pengelolaan kesehatan kerja'],
                        ['code' => 'V.1.4', 'name' => 'Kinerja Pengelolaan Keselamatan Operasi'],
                        ['code' => 'V.1.5', 'name' => 'Kinerja Pengelolaan Bahan Peledak dan Peledakan'],
                    ]],
                    ['code' => 'V.2', 'name' => 'Inspeksi Pelaksanaan Keselamatan Pertambangan'],
                    ['code' => 'V.3', 'name' => 'Evaluasi kepatuhan Terhadap Ketentuan Peraturan'],
                    ['code' => 'V.4', 'name' => 'Penyelidikan Kecelakaan dan Kejadian Berbahaya'],
                    ['code' => 'V.5', 'name' => 'Evaluasi Pengelolaan Administrasi Keselamatan Pertambangan', 'subs' => [
                        ['code' => 'V.5.1', 'name' => 'Buku tambang'],
                        ['code' => 'V.5.2', 'name' => 'Buku daftar kecelakaan tambang'],
                        ['code' => 'V.5.3', 'name' => 'Pelaporan pengelolaan keselamatan pertambangan'],
                        ['code' => 'V.5.4', 'name' => 'Dokumentasi Kejadian Berbahaya dan penyakit akibat kerja'],
                        ['code' => 'V.5.5', 'name' => 'Dokumentasi dan Laporan pemenuhan Kompetensi'],
                    ]],
                    ['code' => 'V.6', 'name' => 'Audit Internal Penerapan SMKP Minerba'],
                    ['code' => 'V.7', 'name' => 'Rencana Perbaikan dan Tindak Lanjut'],
                ]
            ],
            [
                'code' => 'VI', 'name' => 'DOKUMENTASI',
                'subs' => [
                    ['code' => 'VI.1', 'name' => 'Manual SMKP Minerba'],
                    ['code' => 'VI.2', 'name' => 'Prosedur pengendalian Dokumen Keselamatan Pertambangan'],
                    ['code' => 'VI.3', 'name' => 'Prosedur pengendalian Rekaman Keselamatan Pertambangan'],
                    ['code' => 'VI.4', 'name' => 'Penetapan Jenis Dokumen dan Rekaman'],
                ]
            ],
            [
                'code' => 'VII', 'name' => 'TINJAUAN MANAJEMEN DAN PENINGKATAN KINERJA',
                'subs' => [
                    ['code' => 'VII.1', 'name' => 'Pelaksanaan Tinjauan Manajemen'],
                    ['code' => 'VII.2', 'name' => 'Pendokumentasian Catatan Hasil Tinjauan Manajemen'],
                    ['code' => 'VII.3', 'name' => 'Keluaran dari Tinjauan Manajemen'],
                    ['code' => 'VII.4', 'name' => 'Pencatatan, Pendokumentasian, dan Pelaporan Hasil'],
                    ['code' => 'VII.5', 'name' => 'Pelaksanaan Peningkatan Kinerja'],
                    ['code' => 'VII.6', 'name' => 'Penggunaan Tinjauan Hasil dari Tindak Lanjut'],
                ]
            ],
        ];

        $this->createRecursive($structure, null);
    }

    private function createRecursive($items, $parentId)
    {
        foreach ($items as $item) {
            $folder = Folder::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'parent_id' => $parentId
            ]);

            if (isset($item['subs'])) {
                $this->createRecursive($item['subs'], $folder->id);
            }
        }
    }
}