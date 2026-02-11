<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data SMKP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .folder-card { 
            transition: all 0.2s; 
            cursor: pointer; 
            border: 1px solid #e9ecef;
        }
        .folder-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-color: #0d6efd;
            background-color: #f8faff;
        }
        .folder-icon { font-size: 2.5rem; color: #ffc107; }
        .file-icon { font-size: 1.5rem; color: #0d6efd; }
        .breadcrumb-item a { text-decoration: none; color: #6c757d; }
        .breadcrumb-item.active { color: #0d6efd; font-weight: 600; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('smkp.index') }}">
                <i class="bi bi-shield-check"></i> Data SMKP
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb" class="m-0">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('smkp.index') }}"><i class="bi bi-house-door-fill"></i> Home</a>
                        </li>
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $crumb)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if(!$loop->last)
                                        <a href="{{ route('smkp.index', $crumb->id) }}">
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                <i class="bi bi-folder-plus"></i> Folder Baru
            </button>

            @if($currentFolder)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                    <i class="bi bi-cloud-upload"></i> Upload File
                </button>
            @endif
        </div>

        @if($folders->count() > 0)
            <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-folder2-open"></i> Daftar Folder</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
                @foreach($folders as $folder)
                <div class="col">
                    <a href="{{ route('smkp.index', $folder->id) }}" class="text-decoration-none text-dark">
                        <div class="card folder-card h-100">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-folder-fill folder-icon me-3"></i>
                                <div>
                                    <div class="fw-bold text-primary">{{ $folder->code }}</div>
                                    <div class="small lh-sm">{{ $folder->name }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        @endif

        @if($currentFolder && $files->count() > 0)
            <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-file-earmark-text"></i> File Dokumen</h6>
            <div class="card shadow-sm border-0">
                <div class="list-group list-group-flush">
                    @foreach($files as $file)
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                @php
                                    $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);
                                    $icon = match($ext) {
                                        'pdf' => 'bi-file-earmark-pdf text-danger',
                                        'doc', 'docx' => 'bi-file-earmark-word text-primary',
                                        'xls', 'xlsx' => 'bi-file-earmark-excel text-success',
                                        default => 'bi-file-earmark-text text-secondary'
                                    };
                                @endphp
                                <i class="bi {{ $icon }} fs-3 me-3"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $file->name }}</h6>
                                    <small class="text-muted">
                                        {{ strtoupper($ext) }} &bull; {{ $file->created_at->format('d M Y, H:i') }}
                                    </small>
                                </div>
                            </div>
                            <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-download"></i> Unduh
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($currentFolder && $files->count() == 0 && $folders->count() == 0)
            <div class="text-center py-5 text-muted bg-white rounded shadow-sm border">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p>Folder ini masih kosong.</p>
            </div>
        @endif

    </div>

    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('smkp.create_folder', $currentFolder ? $currentFolder->id : '') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Folder Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Folder (Opsional)</label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: I.1.2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Folder / Bab</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Manajemen Risiko" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Buat Folder</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($currentFolder)
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('smkp.upload', $currentFolder->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload File Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Dokumen</label>
                            <input type="text" name="name" class="form-control" placeholder="Nama file yang akan tampil..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih File</label>
                            <input type="file" name="file" class="form-control" required>
                            <div class="form-text">PDF, Word, Excel (Max 50MB)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>