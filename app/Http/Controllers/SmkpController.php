<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FileUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SmkpController extends Controller
{
    public function index(Request $request, $folderId = null)
    {
        $user = Auth::user();
        $units = []; 
        $panduanFolders = collect(); // Inisialisasi koleksi kosong agar tidak error di view
        $folders = collect();

        // --- 1. LOGIKA FOLDER & BREADCRUMBS ---
        if (!$folderId) {
            // === POSISI ROOT (HALAMAN UTAMA) ===
            
            // Ambil Folder Utama (Main)
            $folders = Folder::whereNull('parent_id')
                             ->where('type', 'main') // Default type
                             ->get();

            // Ambil Folder Panduan
            $panduanFolders = Folder::whereNull('parent_id')
                                    ->where('type', 'panduan')
                                    ->get();

            $currentFolder = null;
            $breadcrumbs = [];
            
            // Query dasar file di root (file tanpa folder)
            $fileQuery = FileUpload::whereNull('folder_id');

        } else {
            // === POSISI DI DALAM FOLDER (SUB-FOLDER) ===
            $currentFolder = Folder::with('children')->findOrFail($folderId);
            
            // Di dalam sub-folder, kita hanya menampilkan anak-anak folder tersebut
            $folders = $currentFolder->children;
            
            $breadcrumbs = [];
            $temp = $currentFolder;
            while($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }

            // Query dasar file di folder ini
            $fileQuery = $currentFolder->files()->getQuery();     
        }

        // --- 2. LOGIKA FILTER & PENCARIAN ---
        
        // A. Filter Dropdown Unit
        if ($request->has('unit') && $request->unit != '') {
            $fileQuery->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->unit);
            });
        }

        // B. Pencarian Text Global
        if ($request->has('q') && $request->q != '') {
            $search = $request->q;
            
            $fileQuery->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($u) use ($search) {
                          $u->where('name', 'like', '%' . $search . '%') 
                            ->orWhere('role', 'like', '%' . $search . '%'); 
                      });
            });
        }

        // --- 3. LOGIKA AKSES ROLE (VISIBILITY) ---
        if ($user->role === 'Auditor') {
            // AUDITOR: Melihat semua file
            $files = $fileQuery->with('user')->latest()->get();

        } else {
            // USER BIASA
            
            // Cek apakah user sedang berada di dalam Folder Panduan
            // (Kita cek currentFolder type, atau parentnya jika deep nested, 
            //  tapi untuk simplifikasi kita cek type folder saat ini)
            $isPanduanArea = $currentFolder && $currentFolder->type === 'panduan';

            if ($isPanduanArea) {
                // Di area PANDUAN: User biasa boleh melihat SEMUA file (Read Only)
                $files = $fileQuery->with('user')->latest()->get();
            } else {
                // Di area UTAMA/ROOT: Hanya file milik sendiri
                $fileQuery->where('user_id', $user->id);
                $files = $fileQuery->get();
            }
        }

        return view('smkp.index', compact('folders', 'panduanFolders', 'files', 'currentFolder', 'breadcrumbs', 'units'));
    }

    public function upload(Request $request, $folderId = null)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'name' => 'required|string|max:255', 
        ]);

        // CEK HAK AKSES FOLDER PANDUAN
        if ($folderId) {
            $folder = Folder::findOrFail($folderId);
            // Jika folder ini adalah tipe 'panduan', User biasa DILARANG upload
            if ($folder->type === 'panduan' && Auth::user()->role !== 'Auditor') {
                abort(403, 'Hanya Auditor yang dapat mengunggah dokumen di folder Panduan.');
            }
        }

        $file = $request->file('file');
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId,
            'user_id'   => Auth::id(),
            'name'      => $request->name,
            'file_path' => str_replace('public/', '', $path),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }

    // --- FOLDER MANAGEMENT (HANYA AUDITOR) ---

    public function createFolder(Request $request, $parentId = null)
    {
        if (Auth::user()->role !== 'Auditor') {
            abort(403, 'Hanya Auditor yang dapat membuat folder.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'in:main,panduan', // Validasi input type
        ]);

        // Menentukan Tipe Folder
        $type = 'main'; // Default

        if ($parentId) {
            // Jika Sub-Folder, tipe mengikuti Parent-nya
            $parent = Folder::findOrFail($parentId);
            $type = $parent->type;
        } else {
            // Jika Root, tipe diambil dari input form (Main / Panduan)
            $type = $request->input('type', 'main');
        }

        Folder::create([
            'name' => $request->name,
            'code' => $request->code,
            'parent_id' => $parentId,
            'type' => $type // Simpan tipe folder
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function updateFolder(Request $request, $id)
    {
        if (Auth::user()->role !== 'Auditor') {
            abort(403, 'Hanya Auditor yang dapat mengubah folder.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $folder = Folder::findOrFail($id);
        $folder->update([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return back()->with('success', 'Folder berhasil diperbarui.');
    }

    public function deleteFolder($id)
    {
        if (Auth::user()->role !== 'Auditor') {
            abort(403, 'Hanya Auditor yang dapat menghapus folder.');
        }

        $folder = Folder::findOrFail($id);
        $parentId = $folder->parent_id;
        
        $folder->delete();

        if($parentId) {
            return to_route('smkp.index', $parentId)->with('success', 'Folder berhasil dihapus.');
        }
        return to_route('smkp.index')->with('success', 'Folder berhasil dihapus.');
    }
    
    // --- FILE ACTIONS ---

    public function download($id)
    {
        $file = FileUpload::with('folder')->findOrFail($id);
        $user = Auth::user();

        // Logic Izin Download:
        // 1. Auditor BOLEH.
        // 2. Pemilik File BOLEH.
        // 3. Jika File ada di dalam folder 'panduan', SEMUA USER BOLEH.
        
        $isPanduanFile = $file->folder && $file->folder->type === 'panduan';

        if ($user->role !== 'Auditor' && $file->user_id !== $user->id && !$isPanduanFile) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }

    public function deleteFile($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        // Hapus File: Hanya Auditor atau Pemilik File yang boleh
        // (User biasa tidak bisa hapus file Panduan karena user_id panduan pasti milik Auditor)
        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus file ini.');
        }

        if (Storage::exists('public/' . $file->file_path)) {
            Storage::delete('public/' . $file->file_path);
        }

        $file->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}