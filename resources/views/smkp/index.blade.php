@extends('layouts.app')

@section('content')

    <style>
        /* 1. ANIMASI FOLDER CARD */
        .folder-card-wrapper {
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 0.5rem;
            background: white;
            border: 1px solid rgba(0,0,0,0.08);
            z-index: 1;
        }

        .folder-card-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #ffc107;
            z-index: 50;
        }

        .folder-card-wrapper.is-active-dropdown {
            z-index: 100 !important;
            border-color: #ffc107;
        }

        .folder-card-wrapper:hover .icon-folder,
        .folder-card-wrapper.is-active-dropdown .icon-folder {
            transform: scale(1.1) rotate(-3deg);
            filter: drop-shadow(0 4px 3px rgba(0,0,0,0.1));
            transition: all 0.3s ease;
        }

        /* Link Area */
        .folder-link {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 1.25rem 1rem;
            padding-right: 3.5rem;
            border-radius: 0.5rem; 
        }

        /* 2. TOMBOL OPSI (TITIK TIGA) */
        .folder-options {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 101;
        }

        .btn-options {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 50%;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-options:hover, .dropdown.show .btn-options {
            background: #000;
            color: #fff;
            border-color: #000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* 3. DROPDOWN MENU */
        .custom-dropdown-menu {
            border: 0;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            padding: 0.5rem;
            min-width: 200px;
            z-index: 1000; 
        }
        
        .custom-dropdown-menu .dropdown-item {
            border-radius: 4px;
            padding: 8px 12px;
            font-weight: 500;
            color: #333;
            transition: background 0.2s;
        }

        .custom-dropdown-menu .dropdown-item:hover {
            background-color: #f0f0f0;
            color: #000;
        }

        .custom-dropdown-menu .dropdown-item.text-danger:hover {
            background-color: #fff5f5;
            color: #dc3545;
        }
    </style>

    {{-- BREADCRUMB NAVIGATION --}}
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

    {{-- ALERT DISMISSIBLE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 border-start border-5 border-success shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
            <button class="btn btn-danger shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                <i class="bi bi-cloud-upload me-2"></i> Upload File
            </button>

            <button class="btn btn-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                <i class="bi bi-folder-plus me-2"></i> Tambah Folder
            </button>
        </div>
    </div>

    {{-- FOLDER LIST --}}
    @if($folders->count() > 0)
        <div class="d-flex align-items-center mb-3">
            <h6 class="text-black fw-bold m-0 border-bottom border-dark border-2 pb-1 pe-3">
                <i class="bi bi-folder-fill text-warning me-2"></i> FOLDER
            </h6>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
            @foreach($folders as $folder)
            <div class="col">
                <div class="folder-card-wrapper h-100">
                    
                    <a href="{{ route('smkp.index', $folder->id) }}" class="folder-link">
                        <div class="d-flex align-items-center" style="border-left: 4px solid #000; padding-left: 12px;">
                            <i class="bi bi-folder-fill icon-folder fs-1 me-3"></i>
                            <div style="overflow: hidden;">
                                <div class="fw-bold text-dark">{{ $folder->code }}</div>
                                <div class="small text-secondary lh-sm text-truncate">
                                    {{ $folder->name }}
                                </div>
                            </div>
                        </div>
                    </a>

                    <div class="folder-options">
                        <div class="dropdown">
                            <button class="btn btn-options shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
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
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- FILE LIST --}}
    @if($files->count() > 0)
        <div class="d-flex align-items-center mb-3">
            <h6 class="text-danger fw-bold m-0 border-bottom border-danger border-2 pb-1 pe-3">
                <i class="bi bi-file-earmark-text-fill me-2"></i> DOKUMEN ARSIP
            </h6>
        </div>

        <div class="card shadow-sm border-0 rounded-2">
            <div class="list-group list-group-flush">
                @foreach($files as $file)
                    <div class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center p-3 gap-3">
                        <div class="d-flex align-items-center overflow-hidden">
                            @php
                                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                                $iconClass = match($ext) {
                                    'pdf' => 'bi-file-earmark-pdf-fill icon-pdf',
                                    'doc', 'docx' => 'bi-file-earmark-word-fill icon-word',
                                    'xls', 'xlsx' => 'bi-file-earmark-excel-fill icon-excel',
                                    default => 'bi-file-earmark-text-fill text-secondary'
                                };

                                // --- LOGIC UNTUK TOMBOL "LIHAT" ---
                                // Membuat URL File yang bisa diakses publik
                                $publicUrl = asset('storage/' . $file->file_path);
                                
                                // Default Viewer URL (Browser bawaan)
                                $viewerUrl = $publicUrl;

                                // Jika PDF, DOC, DOCX, XLS, XLSX -> Gunakan Google Viewer
                                if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
                                    $viewerUrl = 'https://docs.google.com/viewer?url=' . urlencode($publicUrl) . '&embedded=false';
                                }
                            @endphp
                            
                            <i class="bi {{ $iconClass }} fs-2 me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">{{ $file->name }}</h6>
                                <div class="small text-muted">
                                    {{ strtoupper($ext) }} &bull; {{ $file->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                        
                        {{-- GROUP TOMBOL AKSI --}}
                        <div class="d-flex gap-2 ms-auto">
                            {{-- Tombol LIHAT (BARU) --}}
                            <a href="{{ $viewerUrl }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Lihat
                            </a>

                            {{-- Tombol UNDUH --}}
                            <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                <i class="bi bi-download me-1"></i> Unduh
                            </a>

                            {{-- Tombol HAPUS --}}
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                    onclick="openDeleteModal('{{ route('smkp.delete_file', $file->id) }}', '{{ $file->name }}', 'Dokumen')">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($folders->count() == 0)
        <div class="text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
            <span class="text-muted">Folder ini kosong.</span>
        </div>
    @endif

    {{-- MODAL CREATE FOLDER --}}
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

    {{-- MODAL EDIT FOLDER --}}
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
        function openEditModal(id, code, name) {
            let form = document.getElementById('editFolderForm');
            let baseUrl = "{{ route('smkp.update_folder', 'placeholder_id') }}";
            form.action = baseUrl.replace('placeholder_id', id);

            document.getElementById('editFolderCode').value = code;
            document.getElementById('editFolderName').value = name;

            var myModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
            myModal.show();
        }

        function openDeleteModal(url, name, type) {
            let form = document.getElementById('deleteForm');
            form.action = url;
            
            document.getElementById('deleteName').innerText = name;
            document.getElementById('deleteType').innerText = type;

            var myModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            myModal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(function (dropdown) {
                dropdown.addEventListener('show.bs.dropdown', function () {
                    var card = this.closest('.folder-card-wrapper');
                    if (card) card.classList.add('is-active-dropdown');
                });

                dropdown.addEventListener('hide.bs.dropdown', function () {
                    var card = this.closest('.folder-card-wrapper');
                    if (card) card.classList.remove('is-active-dropdown');
                });
            });
        });
    </script>

@endsection