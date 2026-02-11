@extends('layouts.app')

@section('content')

    <style>
        /* Custom Animation untuk Folder */
        .folder-card-hover {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .folder-card-hover:hover {
            transform: translateY(-5px); /* Naik sedikit */
            box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important; /* Bayangan soft */
            border-color: #ffc107 !important; /* Border jadi kuning emas saat di-hover */
        }
        .folder-card-hover:hover .icon-folder {
            transform: scale(1.1); /* Ikon membesar sedikit */
            transition: transform 0.3s ease;
        }
    </style>

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
                                        {{ $crumb->code }}
                                    </a>
                                @else
                                    {{ $crumb->code }} {{ Str::limit($crumb->name, 40) }}
                                @endif
                            </li>
                        @endforeach
                    @endif
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 border-start border-5 border-success shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
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
            <button class="btn btn-success shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                <i class="bi bi-folder-plus me-1"></i> Tambah Folder
            </button>

            @if($currentFolder)
                <button class="btn btn-red shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                    <i class="bi bi-cloud-upload me-1"></i> Upload File
                </button>
            @endif
        </div>
    </div>

    @if($folders->count() > 0)
        <div class="d-flex align-items-center mb-3">
            <h6 class="text-black fw-bold m-0 border-bottom border-dark border-2 pb-1 pe-3">
                <i class="bi bi-folder-fill text-warning me-2"></i> FOLDER
            </h6>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
            @foreach($folders as $folder)
            <div class="col">
                <a href="{{ route('smkp.index', $folder->id) }}" class="text-decoration-none text-dark">
                    <div class="card h-100 border-0 shadow-sm folder-card-hover">
                        <div class="card-body d-flex align-items-center p-3 border rounded-2" style="border-left: 4px solid #000 !important;">
                            <i class="bi bi-folder-fill icon-folder fs-1 me-3"></i>
                            <div style="overflow: hidden;">
                                <div class="fw-bold text-dark">{{ $folder->code }}</div>
                                <div class="small text-secondary lh-sm text-truncate">
                                    {{ $folder->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    @endif

    @if($currentFolder)
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
                                @endphp
                                <i class="bi {{ $iconClass }} fs-2 me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $file->name }}</h6>
                                    <div class="small text-muted">
                                        {{ strtoupper($ext) }} &bull; {{ $file->created_at->format('d M Y') }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-4 ms-auto">
                                <i class="bi bi-download"></i> Unduh
                            </a>
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
    @endif

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

    @if($currentFolder)
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('smkp.upload', $currentFolder->id) }}" method="POST" enctype="multipart/form-data" class="w-100">
                @csrf
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">Upload Dokumen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border-start border-danger border-4 small mb-3">
                            Upload ke: <strong>{{ $currentFolder->code }} - {{ $currentFolder->name }}</strong>
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
    @endif

@endsection