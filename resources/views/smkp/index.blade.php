<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data SMKP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #000000; /* Hitam Pekat */
            --bs-danger: #d60000;  /* Merah Menyala */
        }
        
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* Navbar Styling */
        .navbar { 
            background: linear-gradient(135deg, #000000 0%, #1c1c1c 100%); 
            border-bottom: 4px solid var(--bs-danger);
        }

        /* Folder Card Styling */
        .folder-card { 
            transition: all 0.25s ease; 
            cursor: pointer; 
            border: 1px solid #e2e2e2;
            background: white;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }
        
        /* Aksen Merah Kecil di Kiri Folder */
        .folder-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #000;
            transition: background-color 0.2s;
        }

        .folder-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: var(--bs-danger);
        }

        .folder-card:hover::before {
            background-color: var(--bs-danger);
        }

        /* Functional Icons Colors */
        .folder-icon { font-size: 2.5rem; color: #ffc107; text-shadow: 0 2px 2px rgba(0,0,0,0.1); } /* Kuning Folder */
        .icon-pdf { color: #dc3545; }
        .icon-word { color: #0d6efd; }
        .icon-excel { color: #198754; }
        .icon-default { color: #6c757d; }

        /* Breadcrumb Styling */
        .breadcrumb-item a { text-decoration: none; color: var(--bs-danger); font-weight: 500; }
        .breadcrumb-item a:hover { text-decoration: underline; }
        .breadcrumb-item.active { color: #000; font-weight: 700; }

        /* Buttons */
        .btn-black { background-color: #000; color: #fff; border: 1px solid #000; }
        .btn-black:hover { background-color: #333; color: #fff; border-color: #333; }
        
        .btn-red { background-color: var(--bs-danger); color: #fff; border: none; }
        .btn-red:hover { background-color: #b00000; color: #fff; }

        .btn-outline-back { border: 1px solid #ced4da; color: #495057; background: white; }
        .btn-outline-back:hover { background-color: #e9ecef; color: #000; }

        /* Mobile Adjustments */
        @media (max-width: 576px) {
            .folder-icon { font-size: 2rem; }
            .btn-responsive { width: 100%; margin-bottom: 0.5rem; }
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('smkp.index') }}">
                <i class="bi bi-shield-lock-fill text-danger fs-4"></i> 
                <div>
                    SMKP <span class="text-danger">MINERBA</span>
                </div>
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="{{ route('smkp.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
                        </li>
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $crumb)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if(!$loop->last)
                                        <a href="{{ route('smkp.index', $crumb->id) }}">
                                            {{ $crumb->code }} {{ Str::limit($crumb->name, 20) }}
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 border-start border-5 border-success shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            
            <div>
                @if($currentFolder)
                    @php
                        // Logika Link Kembali: Jika punya parent -> ke parent, jika tidak -> ke root
                        $backLink = $currentFolder->parent_id ? route('smkp.index', $currentFolder->parent_id) : route('smkp.index');
                    @endphp
                    <a href="{{ $backLink }}" class="btn btn-outline-back btn-responsive shadow-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-end w-sm-100">
                <button class="btn btn-black btn-responsive shadow-sm" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                    <i class="bi bi-folder-plus"></i> Folder Baru
                </button>

                @if($currentFolder)
                    <button class="btn btn-red btn-responsive shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="bi bi-cloud-upload"></i> Upload File
                    </button>
                @endif
            </div>
        </div>

        @if($folders->count() > 0)
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-folder2-open me-2 fs-5"></i>
                <h6 class="text-dark fw-bold m-0 border-bottom border-dark pb-1">DAFTAR FOLDER</h6>
            </div>
            
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
                @foreach($folders as $folder)
                <div class="col">
                    <a href="{{ route('smkp.index', $folder->id) }}" class="text-decoration-none text-dark">
                        <div class="card folder-card h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <i class="bi bi-folder-fill folder-icon me-3"></i>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate">{{ $folder->code }}</div>
                                    <div class="small text-secondary lh-sm" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
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
                    <i class="bi bi-file-earmark-text me-2 fs-5 text-danger"></i>
                    <h6 class="text-danger fw-bold m-0 border-bottom border-danger pb-1">FILE DOKUMEN</h6>
                </div>

                <div class="card shadow-sm border-0 rounded-2 overflow-hidden">
                    <div class="list-group list-group-flush">
                        @foreach($files as $file)
                            <div class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center p-3 gap-3">
                                <div class="d-flex align-items-center overflow-hidden">
                                    @php
                                        $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                                        $iconClass = match($ext) {
                                            'pdf' => 'bi-file-earmark-pdf-fill icon-pdf',
                                            'doc', 'docx' => 'bi-file-earmark-word-fill icon-word',
                                            'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel-fill icon-excel',
                                            'ppt', 'pptx' => 'bi-file-earmark-slides-fill text-warning',
                                            'jpg', 'jpeg', 'png' => 'bi-file-earmark-image-fill text-info',
                                            default => 'bi-file-earmark-text-fill icon-default'
                                        };
                                    @endphp
                                    <i class="bi {{ $iconClass }} fs-2 me-3"></i>
                                    <div style="min-width: 0;">
                                        <h6 class="mb-1 fw-bold text-truncate">{{ $file->name }}</h6>
                                        <div class="small text-muted d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border">{{ strtoupper($ext) }}</span>
                                            <span><i class="bi bi-clock"></i> {{ $file->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-4 ms-auto">
                                    <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Unduh</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($folders->count() == 0)
                <div class="text-center py-5 text-muted bg-white rounded shadow-sm border border-dashed">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">Folder ini belum memiliki sub-folder atau dokumen.</p>
                </div>
            @endif
        @endif

    </div>

    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('smkp.create_folder', $currentFolder ? $currentFolder->id : null) }}" method="POST" class="w-100">
                @csrf
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-black text-white">
                        <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus"></i> Buat Folder Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">KODE FOLDER</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="code" class="form-control" placeholder="Misal: I.1.2">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">NAMA FOLDER / BAB</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="Misal: Manajemen Risiko" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-black px-4">Simpan</button>
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
                        <h5 class="modal-title fw-bold"><i class="bi bi-cloud-upload"></i> Upload Dokumen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border-start border-danger border-4 small text-muted mb-3">
                            <i class="bi bi-info-circle me-1"></i> File akan disimpan di: <strong>{{ $currentFolder->name }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">NAMA DOKUMEN</label>
                            <input type="text" name="name" class="form-control" placeholder="Nama yang akan tampil di daftar..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">PILIH FILE</label>
                            <input type="file" name="file" class="form-control" required>
                            <div class="form-text small">Format: PDF, Word, Excel (Maks. 50MB)</div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>