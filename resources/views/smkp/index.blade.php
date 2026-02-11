<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data SMKP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .folder-card { transition: transform 0.2s; cursor: pointer; }
        .folder-card:hover { transform: translateY(-5px); background-color: #f8f9fa; }
        .folder-icon { font-size: 3rem; color: #ffc107; }
        .file-icon { font-size: 2rem; color: #0d6efd; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('smkp.index') }}">
                <i class="bi bi-shield-lock-fill"></i> Data SMKP
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('smkp.index') }}">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <nav aria-label="breadcrumb" class="bg-white p-3 rounded shadow-sm mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('smkp.index') }}"><i class="bi bi-house-door"></i> Home</a>
                </li>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if(!$loop->last)
                                <a href="{{ route('smkp.index', $crumb->id) }}">{{ $crumb->code }}</a>
                            @else
                                {{ $crumb->code }} - {{ Str::limit($crumb->name, 30) }}
                            @endif
                        </li>
                    @endforeach
                @endif
            </ol>
        </nav>

        <h4 class="mb-3 text-secondary">
            {{ $currentFolder ? $currentFolder->code . ' ' . $currentFolder->name : 'Direktori Utama' }}
        </h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Sub-Folder / Bab</div>
                    <div class="card-body">
                        @if($folders->count() > 0)
                            <div class="row g-3">
                                @foreach($folders as $folder)
                                <div class="col-6 col-md-4">
                                    <a href="{{ route('smkp.index', $folder->id) }}" class="text-decoration-none text-dark">
                                        <div class="card folder-card h-100 border-0 text-center p-3 shadow-sm">
                                            <i class="bi bi-folder-fill folder-icon"></i>
                                            <div class="mt-2 fw-semibold">{{ $folder->code }}</div>
                                            <div class="small text-muted">{{ Str::limit($folder->name, 40) }}</div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-3">Tidak ada sub-folder di sini.</p>
                        @endif
                    </div>
                </div>

                @if($currentFolder)
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">File Tersimpan</div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($files as $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-text-fill file-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-0">{{ $file->name }}</h6>
                                            <small class="text-muted">{{ $file->created_at->format('d M Y H:i') }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('smkp.download', $file->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Unduh
                                    </a>
                                </div>
                            @empty
                                <div class="p-3 text-center text-muted">Belum ada file yang diunggah.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                @if($currentFolder)
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-cloud-upload"></i> Upload File Baru
                    </div>
                    <div class="card-body">
                        <form action="{{ route('smkp.upload', $currentFolder->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama File / Dokumen</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: SOP Kebijakan..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pilih File</label>
                                <input type="file" name="file" class="form-control" required>
                                <div class="form-text">Format: PDF, Docx, Xlsx (Max 10MB)</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </form>
                    </div>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Silakan pilih Bab/Sub-bab terlebih dahulu untuk mengunggah dokumen.
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>