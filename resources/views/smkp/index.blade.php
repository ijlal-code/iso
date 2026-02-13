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
        
        /* Style Tambahan untuk Tabel Per Unit */
        .unit-header {
            background-color: #f8f9fa;
            border-left: 5px solid #dc3545; /* Aksen Merah di kiri header */
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

    {{-- TOMBOL UTAMA --}}
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
            {{-- Sembunyikan tombol upload jika Auditor --}}
            @if(Auth::user()->role !== 'Auditor')
            <button class="btn btn-danger shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                <i class="bi bi-cloud-upload me-2"></i> Upload File
            </button>
            @endif

            @if(Auth::user()->role === 'Auditor')
            <button class="btn btn-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                <i class="bi bi-folder-plus me-2"></i> Tambah Folder
            </button>
            @endif
        </div>
    </div>

    {{-- LIST FOLDER --}}
    @if($folders->count() > 0)
        <div class="d-flex align-items-center mb-3">
            <h6 class="text-black fw-bold m-0 border-bottom border-dark border-2 pb-1 pe-3">
                <i class="bi bi-folder-fill text-warning me-2"></i> FOLDER
            </h6>
        </div>
        
        <div class="card shadow-sm border-0 rounded-2 mb-5">
            <div class="list-group list-group-flush">
                @foreach($folders as $folder)
                <div class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between">
                    <a href="{{ route('smkp.index', $folder->id) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
                        <i class="bi bi-folder-fill text-warning fs-2 me-3 folder-icon"></i>
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
                @endforeach
            </div>
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- TAMPILAN MONITORING (AUDITOR) --}}
    {{-- ========================================================= --}}
    
    @if(Auth::user()->role === 'Auditor' && $currentFolder && $folders->count() == 0)
        
        @php
            // DEFINISIKAN ARRAY TARGET ROLES DI SINI AGAR BISA DIPAKAI DI DROPDOWN DAN LOOPING
            $targetRoles = [
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
            $hasData = false;
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="text-danger fw-bold m-0 border-bottom border-danger border-2 pb-1 pe-3">
                <i class="bi bi-grid-1x2 me-2"></i> MONITORING DOKUMEN PER UNIT
            </h6>
        </div>

        {{-- FITUR BARU: FILTER BAR --}}
        <div class="card border-0 shadow-sm bg-light mb-4">
            <div class="card-body p-3">
                <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                    
                    {{-- Filter Dropdown Unit (MENGGUNAKAN $targetRoles agar RAPI) --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-funnel"></i></span>
                            <select name="unit" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                                <option value="">- Tampilkan Semua Unit -</option>
                                @foreach($targetRoles as $roleOption)
                                    <option value="{{ $roleOption }}" {{ request('unit') == $roleOption ? 'selected' : '' }}>
                                        {{ $roleOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" 
                                   placeholder="Cari nama dokumen..." value="{{ request('q') }}">
                        </div>
                    </div>

                    {{-- Tombol Reset/Filter --}}
                    <div class="col-md-2 text-end">
                        @if(request('unit') || request('q'))
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle me-1"></i> Reset
                            </a>
                        @else
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-filter me-1"></i> Filter
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @foreach($targetRoles as $roleName)
                @php
                    // Filter file berdasarkan role saat ini
                    $roleFiles = $files->filter(function ($file) use ($roleName) {
                        return $file->user && $file->user->role === $roleName;
                    });

                    // LOGIKA TAMPILAN (DIPERBARUI):
                    
                    // 1. Jika User memilih Filter Unit tertentu:
                    //    Tampilkan HANYA unit yang dipilih, meskipun filenya kosong.
                    //    Skip unit lain.
                    if (request('unit')) {
                        if (request('unit') !== $roleName) {
                            continue; // Skip unit yang tidak dipilih
                        }
                        // Jika unit cocok, kita JANGAN continue meskipun count == 0
                        // agar tabel kosong tetap tampil.
                    }
                    
                    // 2. Jika User HANYA mencari teks (Tanpa Filter Unit):
                    //    Barulah kita sembunyikan tabel yang kosong agar tidak menuhi layar.
                    else if (request('q') && $roleFiles->count() === 0) {
                        continue;
                    }

                    // Menandakan setidaknya ada 1 tabel yang dirender
                    $hasData = true;
                @endphp

                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-2 overflow-hidden">
                        {{-- Header Tabel: Nama Unit --}}
                        <div class="card-header unit-header py-3 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold m-0 text-dark">
                                <i class="bi bi-building me-2 text-secondary"></i> {{ $roleName }}
                            </h6>
                            @if($roleFiles->count() > 0)
                                <span class="badge bg-danger rounded-pill">{{ $roleFiles->count() }} File</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">0 File</span>
                            @endif
                        </div>

                        {{-- Body: Tabel File --}}
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">
                                <thead class="bg-light border-bottom">
                                    <tr class="small text-muted text-uppercase">
                                        <th class="ps-4" style="width: 5%;">No</th>
                                        <th style="width: 25%;">Nama Pengupload</th>
                                        <th style="width: 30%;">Nama Dokumen</th>
                                        <th class="text-center" style="width: 20%;">Tanggal Upload</th>
                                        <th class="text-center" style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop file yang sudah difilter --}}
                                    @forelse($roleFiles as $file)
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
                                        <tr class="bg-white border-bottom">
                                            <td class="ps-4 fw-bold text-muted">{{ $loop->iteration }}</td> 
                                            <td class="fw-medium text-dark">
                                                {{ $file->user->name ?? 'User Terhapus' }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi {{ $iconClass }} fs-5 me-2"></i>
                                                    <span class="fw-semibold text-dark">{{ $file->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center text-muted small">
                                                {{ $file->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark" title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button onclick="openDeleteModal('{{ route('smkp.delete_file', $file->id) }}', '{{ $file->name }}', 'Dokumen')" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- JIKA BELUM ADA FILE DARI UNIT INI --}}
                                        <tr class="bg-white">
                                            <td colspan="5" class="text-center py-4 text-muted fst-italic">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                Belum ada dokumen.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- EMPTY STATE: Jika hasil pencarian Teks (tanpa filter unit) nihil --}}
        @if(!$hasData)
            <div class="alert alert-warning text-center border-0 shadow-sm py-5 mt-3">
                <i class="bi bi-search fs-1 mb-3 d-block text-warning"></i>
                <h5 class="fw-bold">Data Tidak Ditemukan</h5>
                <p class="text-muted">Tidak ada dokumen yang sesuai dengan filter atau pencarian Anda.</p>
                <a href="{{ url()->current() }}" class="btn btn-outline-dark btn-sm px-4 rounded-pill">Reset Filter</a>
            </div>
        @endif

    @else
        
        {{-- ----- 2. TAMPILAN USER BIASA (NON-AUDITOR) ATAU JIKA MASIH ADA SUBFOLDER ----- --}}

        @if($files->count() > 0)
            <div class="d-flex align-items-center mb-3">
                <h6 class="text-danger fw-bold m-0 border-bottom border-danger border-2 pb-1 pe-3">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> DOKUMEN SAYA
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

                                $publicUrl = asset('storage/' . $file->file_path);
                            @endphp
                                
                                <i class="bi {{ $iconClass }} fs-2 me-3 file-icon"></i>
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-dark text-break">{{ $file->name }}</h6>
                                    <div class="small text-muted">
                                        {{ strtoupper($ext) }} &bull; {{ $file->created_at->format('d M Y') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 ms-auto">
                                <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i> Unduh
                                </a>

                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="openDeleteModal('{{ route('smkp.delete_file', $file->id) }}', '{{ $file->name }}', 'Dokumen')">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
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
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-black text-white">
                        <h5 class="modal-title fw-bold">Buat Folder Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
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