@extends('layouts.app')

@section('content')

    <style>
        .list-group-item {
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }
        .list-group-item:hover {
            background-color: #f9fafe; 
            border-left: 4px solid #ffc107; 
            transform: translateX(4px);
            z-index: 10;
        }
        .folder-icon { transition: transform 0.2s; }
        .list-group-item:hover .folder-icon { transform: scale(1.15); }
        .custom-dropdown-menu {
            border: 0; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem; padding: 0.5rem; min-width: 200px; z-index: 1000; 
        }
        .custom-dropdown-menu .dropdown-item {
            border-radius: 4px; padding: 8px 12px; font-weight: 500; color: #333; transition: background 0.2s;
        }
        .custom-dropdown-menu .dropdown-item:hover { background-color: #f0f0f0; color: #000; }
        .custom-dropdown-menu .dropdown-item.text-danger:hover { background-color: #fff5f5; color: #dc3545; }
        
        .unit-header {
            background-color: #f8f9fa;
            border-left: 5px solid #dc3545;
        }
        
        /* Style Tabs */
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link:hover {
            color: #dc3545;
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #dc3545; /* Merah untuk Utama */
            border-bottom: 3px solid #dc3545;
            font-weight: bold;
        }
        .nav-tabs .nav-link#panduan-tab.active {
            color: #198754; /* Hijau untuk Panduan */
            border-bottom: 3px solid #198754;
        }
    </style>

    {{-- BREADCRUMB --}}
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="{{ route('smkp.index') }}" class="text-decoration-none text-danger fw-bold">
                            <i class="bi bi-house-door-fill"></i> Home
                        </a>
                    </li>
                    @if(isset($breadcrumbs))
                        @foreach($breadcrumbs as $crumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active text-dark fw-bold' : '' }}">
                                @if(!$loop->last)
                                    <a href="{{ route('smkp.index', $crumb->id) }}" class="text-decoration-none text-danger">
                                        {{ $crumb->code }} {{ $crumb->name }}
                                    </a>
                                @else
                                    {{ $crumb->code }} {{ $crumb->name }}
                                @endif
                            </li>
                        @endforeach
                    @endif
                </ol>
            </nav>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 border-start border-5 border-success shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- LOGIKA TOMBOL KEMBALI & UPLOAD --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            @if($currentFolder)
                @php
                    $backLink = $currentFolder->parent_id ? route('smkp.index', $currentFolder->parent_id) : route('smkp.index');
                @endphp
                <a href="{{ $backLink }}" class="btn btn-light border shadow-sm px-4">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            @endif
        </div>

        <div class="d-flex gap-2">
            {{-- 
               LOGIKA TOMBOL UPLOAD:
               1. Auditor: Selalu boleh upload.
               2. User Biasa: Boleh upload JIKA BUKAN di folder 'panduan'.
            --}}
            @php
                $canUpload = false;
                if (Auth::user()->role === 'Auditor') {
                    $canUpload = true;
                } elseif (!$currentFolder) {
                    $canUpload = true; // Di Root (Utama) boleh
                } elseif ($currentFolder && $currentFolder->type !== 'panduan') {
                    $canUpload = true; // Di Folder biasa boleh
                }
            @endphp

            @if($canUpload)
            <button class="btn btn-danger shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                <i class="bi bi-cloud-upload me-2"></i> Upload File
            </button>
            @endif

            {{-- Tombol Tambah Folder (Hanya muncul di Sub-Folder untuk Auditor) --}}
            {{-- Jika di Root, tombol ada di dalam Tab masing-masing --}}
            @if(Auth::user()->role === 'Auditor' && $currentFolder)
            <button class="btn btn-success shadow-sm px-4 fw-bold" onclick="openCreateModal('{{ $currentFolder->type }}')">
                <i class="bi bi-folder-plus me-2"></i> Tambah Sub-Folder
            </button>
            @endif
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAMPILAN FOLDER (LOGIKA TABS VS SUB-FOLDER) --}}
    {{-- ========================================================= --}}

    @if(!$currentFolder)
        {{-- === POSISI DI ROOT: GUNAKAN TABS === --}}
        
        <ul class="nav nav-tabs mb-3" id="rootTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="utama-tab" data-bs-toggle="tab" data-bs-target="#utama" type="button" role="tab">
                    <i class="bi bi-folder-fill me-2"></i> FOLDER UTAMA
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="panduan-tab" data-bs-toggle="tab" data-bs-target="#panduan" type="button" role="tab">
                    <i class="bi bi-book-half me-2"></i> PANDUAN & PROSEDUR
                </button>
            </li>
        </ul>

        <div class="tab-content" id="rootTabsContent">
            
            {{-- TAB 1: FOLDER UTAMA --}}
            <div class="tab-pane fade show active" id="utama" role="tabpanel">
                @if(Auth::user()->role === 'Auditor')
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-outline-danger btn-sm fw-bold" onclick="openCreateModal('main')">
                            <i class="bi bi-plus-lg"></i> Folder Utama Baru
                        </button>
                    </div>
                @endif

                @if($folders->count() > 0)
                    <div class="card shadow-sm border-0 rounded-2 mb-4">
                        <div class="list-group list-group-flush">
                            @foreach($folders as $folder)
                                {{-- Gunakan include/partial atau loop langsung --}}
                                @include('smkp.folder_item', ['folder' => $folder])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small border rounded bg-light mb-4">
                        Belum ada folder utama.
                    </div>
                @endif
            </div>

            {{-- TAB 2: PANDUAN --}}
            <div class="tab-pane fade" id="panduan" role="tabpanel">
                <div class="alert alert-info border-0 shadow-sm small py-2">
                    <i class="bi bi-info-circle-fill me-2"></i> 
                    Area ini berisi dokumen panduan resmi. 
                    @if(Auth::user()->role !== 'Auditor')
                        (Akses: Read-Only / Hanya Lihat)
                    @endif
                </div>

                @if(Auth::user()->role === 'Auditor')
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-outline-success btn-sm fw-bold" onclick="openCreateModal('panduan')">
                            <i class="bi bi-plus-lg"></i> Folder Panduan Baru
                        </button>
                    </div>
                @endif

                @if($panduanFolders->count() > 0)
                    <div class="card shadow-sm border-0 rounded-2 mb-4">
                        <div class="list-group list-group-flush">
                            @foreach($panduanFolders as $folder)
                                @include('smkp.folder_item', ['folder' => $folder])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small border rounded bg-light mb-4">
                        Belum ada folder panduan.
                    </div>
                @endif
            </div>
        </div>

    @else
        {{-- === POSISI DI SUB-FOLDER: TAMPILAN STANDARD === --}}
        
        {{-- Header Sub Folder --}}
        <div class="d-flex align-items-center mb-3">
            <h6 class="text-black fw-bold m-0 border-bottom border-dark border-2 pb-1 pe-3">
                @if($currentFolder->type == 'panduan')
                    <i class="bi bi-book-half text-success me-2"></i> PANDUAN: {{ $currentFolder->name }}
                @else
                    <i class="bi bi-folder-fill text-warning me-2"></i> FOLDER: {{ $currentFolder->name }}
                @endif
            </h6>
        </div>

        @if($folders->count() > 0)
            <div class="card shadow-sm border-0 rounded-2 mb-5">
                <div class="list-group list-group-flush">
                    @foreach($folders as $folder)
                        @include('smkp.folder_item', ['folder' => $folder])
                    @endforeach
                </div>
            </div>
        @endif

    @endif

    {{-- ========================================================= --}}
    {{-- TAMPILAN MONITORING (AUDITOR) --}}
    {{-- ========================================================= --}}
    
    @if(Auth::user()->role === 'Auditor' && $currentFolder && $folders->count() == 0)
        {{-- ... (KODE MONITORING AUDITOR TETAP SAMA, TIDAK PERLU DIUBAH) ... --}}
        {{-- Saya persingkat bagian ini karena tidak ada perubahan logika, 
             tapi pastikan Anda menyalin kode monitoring/table per unit dari file sebelumnya ke sini 
             jika ingin fitur monitoring tetap ada. --}}
             
        {{-- FITUR SEARCH & FILTER (Simulasi kode singkat untuk konteks) --}}
        <div class="card border-0 shadow-sm bg-light mb-4">
            <div class="card-body p-3">
                <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                    {{-- ... Input Filter Unit & Search (sama seperti sebelumnya) ... --}}
                    <div class="col-md-4">
                        <select name="unit" class="form-select" onchange="this.form.submit()">
                            <option value="">- Semua Unit -</option>
                            {{-- ... Options ... --}}
                        </select>
                    </div>
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Cari..." value="{{ request('q') }}"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-danger w-100">Cari</button></div>
                </form>
            </div>
        </div>

        {{-- Loop Monitoring Unit (Sama seperti sebelumnya) --}}
        {{-- ... --}}
        @php
             // Pastikan variable targetRoles ada
             $targetRoles = ['KTT', 'Pengelola Sistem', 'Audit Internal', 'Pengelola Risiko', 'Pengelola Legal', 'Pengelola K3 & Lingk.', 'Pengel. SDM & Diklat', 'Pengawas Operasional', 'Bag. K3 & KO Pertamb.', 'PJO', 'Pengawas Oper. PJO', 'Pengawas Teknik PJO', 'Bag. K3 & KO PJO'];
        @endphp
        
        {{-- Render Tabel Unit (Sama seperti kode asli Anda) --}}
        <div class="row">
        @foreach($targetRoles as $roleName)
             {{-- ... Logika render tabel per unit ... --}}
             {{-- Saya skip detail panjangnya agar fokus pada perubahan Folder Panduan --}}
        @endforeach
        </div>

    @else
        
        {{-- ========================================================= --}}
        {{-- TAMPILAN FILE (LIST STANDARD / DOKUMEN SAYA / PANDUAN) --}}
        {{-- ========================================================= --}}

        @if($files->count() > 0)
            <div class="d-flex align-items-center mb-3 mt-4">
                <h6 class="text-secondary fw-bold m-0 border-bottom border-secondary border-2 pb-1 pe-3">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> DAFTAR DOKUMEN
                </h6>
            </div>

            <div class="card shadow-sm border-0 rounded-2">
                <div class="list-group list-group-flush">
                    @foreach($files as $file)
                        <div class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center p-3 gap-3 file-item">
                            <div class="d-flex align-items-center overflow-hidden flex-grow-1">
                            @php
                                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                                $iconClass = match($ext) {
                                    'pdf' => 'bi-file-earmark-pdf-fill text-danger',
                                    'doc', 'docx' => 'bi-file-earmark-word-fill text-primary',
                                    'xls', 'xlsx' => 'bi-file-earmark-excel-fill text-success',
                                    'ppt', 'pptx' => 'bi-file-earmark-ppt-fill text-warning',
                                    'jpg', 'jpeg', 'png' => 'bi-file-earmark-image-fill text-info',
                                    default => 'bi-file-earmark-text-fill text-secondary'
                                };
                            @endphp
                                <i class="bi {{ $iconClass }} fs-2 me-3 file-icon"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-dark text-break">{{ $file->name }}</h6>
                                    <div class="small text-muted">
                                        {{ strtoupper($ext) }} &bull; 
                                        <i class="bi bi-person me-1"></i> {{ $file->user->name ?? '-' }} &bull;
                                        {{ $file->created_at->format('d M Y') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 ms-auto">
                                {{-- Tombol Download: Semua boleh --}}
                                <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i> Unduh
                                </a>

                                {{-- Tombol Hapus: 
                                     1. Auditor: Boleh.
                                     2. User Biasa: Boleh JIKA BUKAN file Panduan (cek currentFolder type). 
                                --}}
                                @php
                                    $canDelete = false;
                                    if(Auth::user()->role === 'Auditor') {
                                        $canDelete = true;
                                    } elseif ($file->user_id === Auth::id()) {
                                        // User hanya bisa hapus file sendiri
                                        // DAN tidak sedang di area Panduan
                                        if (!$currentFolder || $currentFolder->type !== 'panduan') {
                                            $canDelete = true;
                                        }
                                    }
                                @endphp

                                @if($canDelete)
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="openDeleteModal('{{ route('smkp.delete_file', $file->id) }}', '{{ $file->name }}', 'Dokumen')">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($folders->count() == 0 && $files->count() == 0)
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <span class="text-muted">Folder ini kosong.</span>
            </div>
        @endif

    @endif

    {{-- MODAL CREATE FOLDER --}}
    @if(Auth::user()->role === 'Auditor')
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('smkp.create_folder', $currentFolder ? $currentFolder->id : null) }}" method="POST" class="w-100">
                @csrf
                {{-- Input Hidden Type: Diisi oleh JavaScript --}}
                <input type="hidden" name="type" id="folderTypeInput" value="main">

                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-black text-white">
                        <h5 class="modal-title fw-bold">Buat Folder Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">TIPE FOLDER</label>
                            <input type="text" id="displayType" class="form-control-plaintext fw-bold text-uppercase" readonly value="UTAMA">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">KODE</label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: I.1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">NAMA FOLDER</label>
                            <input type="text" name="name" class="form-control" required placeholder="Nama Bab / Sub-bab">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="modal fade" id="editFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editFolderForm" action="" method="POST" class="w-100">
                @csrf
                @method('PUT')
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold">Edit Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">KODE</label>
                            <input type="text" name="code" id="editFolderCode" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">NAMA FOLDER</label>
                            <input type="text" name="name" id="editFolderName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link btn-black text-white text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UPLOAD FILE --}}
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('smkp.upload', $currentFolder ? $currentFolder->id : null) }}" method="POST" enctype="multipart/form-data" class="w-100">
                @csrf
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">Upload Dokumen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border-start border-danger border-4 small mb-3">
                            Upload ke: <strong>{{ $currentFolder ? $currentFolder->name : 'Home / Root' }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">NAMA DOKUMEN</label>
                            <input type="text" name="name" class="form-control" required placeholder="Nama file yang akan tampil...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">FILE</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4">Upload File</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form id="deleteForm" action="" method="POST" class="w-100">
                @csrf
                @method('DELETE')
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 text-danger">
                            <i class="bi bi-exclamation-circle fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Hapus <span id="deleteType">Item</span>?</h5>
                        <p class="text-muted small mb-4">
                            "<span id="deleteName" class="fw-bold"></span>"<br>
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light w-50" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger w-50">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk Set Tipe Folder saat Buka Modal
        function openCreateModal(type) {
            // Set value hidden input
            document.getElementById('folderTypeInput').value = type;
            
            // Set display text agar user tahu mau buat folder apa
            let label = (type === 'panduan') ? 'PANDUAN & PROSEDUR' : 'FOLDER UTAMA';
            document.getElementById('displayType').value = label;

            var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
            myModal.show();
        }

        @if(Auth::user()->role === 'Auditor')
        function openEditModal(id, code, name) {
            let form = document.getElementById('editFolderForm');
            let baseUrl = "{{ route('smkp.update_folder', 'placeholder_id') }}";
            form.action = baseUrl.replace('placeholder_id', id);

            document.getElementById('editFolderCode').value = code;
            document.getElementById('editFolderName').value = name;

            var myModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
            myModal.show();
        }
        @endif

        function openDeleteModal(url, name, type) {
            let form = document.getElementById('deleteForm');
            form.action = url;
            
            document.getElementById('deleteName').innerText = name;
            document.getElementById('deleteType').innerText = type;

            var myModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            myModal.show();
        }
    </script>

@endsection

{{-- 
    UNTUK MEMUDAHKAN, SAYA BUAT POTONGAN KODE ITEM FOLDER DI BAWAH INI.
    ANDA BISA MENYALINNYA KE DALAM VIEW (DI TEMPAT @include tadi) 
    ATAU MEMBUAT FILE TERPISAH resources/views/smkp/folder_item.blade.php
--}}
{{-- START: folder_item.blade.php CONTENT (Optional) --}}
{{-- 
<div class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('smkp.index', $folder->id) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
        @if($folder->type == 'panduan')
             <i class="bi bi-book-half text-success fs-2 me-3 folder-icon"></i>
        @else
             <i class="bi bi-folder-fill text-warning fs-2 me-3 folder-icon"></i>
        @endif
        
        <div>
            <div class="fw-bold fs-6 text-break">
                <span class="badge bg-light text-dark border border-secondary me-1">{{ $folder->code }}</span>
                {{ $folder->name }}
            </div>
            <div class="small text-muted mt-1">
                <i class="bi bi-calendar-event me-1"></i> {{ $folder->created_at->format('d M Y') }}
            </div>
        </div>
    </a>

    @if(Auth::user()->role === 'Auditor')
    <div class="dropdown ms-3">
        <button class="btn btn-light btn-sm rounded-circle border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu">
            <li><h6 class="dropdown-header small text-uppercase text-muted">Aksi Folder</h6></li>
            <li>
                <button class="dropdown-item" 
                        onclick="openEditModal('{{ $folder->id }}', '{{ $folder->code }}', '{{ $folder->name }}')">
                    <i class="bi bi-pencil-square text-warning me-2"></i> Edit Nama
                </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteModal('{{ route('smkp.delete_folder', $folder->id) }}', '{{ $folder->name }}', 'Folder')">
                    <i class="bi bi-trash-fill me-2"></i> Hapus Folder
                </button>
            </li>
        </ul>
    </div>
    @endif
</div>
--}}