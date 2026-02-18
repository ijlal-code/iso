{{-- resources/views/smkp/folder_item.blade.php --}}
<div class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('smkp.index', $folder->id) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
        @if(isset($folder->type) && $folder->type == 'panduan')
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