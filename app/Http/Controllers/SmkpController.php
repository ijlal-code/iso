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
    public function index($folderId = null)
    {
        $user = Auth::user();
        $units = []; // Inisialisasi variabel units

        // --- 1. LOGIKA FOLDER & BREADCRUMBS ---
        if (!$folderId) {
            // Jika di Root (Halaman Awal)
            $folders = Folder::whereNull('parent_id')->get();
            $currentFolder = null;
            $breadcrumbs = [];
            
            // Query dasar untuk file di root
            $fileQuery = FileUpload::whereNull('folder_id');
        } else {
            // Jika di dalam Folder
            $currentFolder = Folder::with('children')->findOrFail($folderId);
            $folders = $currentFolder->children;
            
            // Buat Breadcrumbs
            $breadcrumbs = [];
            $temp = $currentFolder;
            while($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }

            // Query dasar untuk file di folder ini
            $fileQuery = $currentFolder->files()->getQuery();     
        }

        // --- 2. LOGIKA FILTER BERDASARKAN ROLE ---
        if ($user->role === 'Auditor') {
            // Jika AUDITOR:
            // 1. Ambil SEMUA file di folder ini (eager load 'user' untuk performa)
            $files = $fileQuery->with('user')->get();

            // 2. Ambil semua User selain Auditor
            // Data ini dikirim ke View untuk dijadikan basis tabel monitoring per unit
            $units = User::where('role', '!=', 'Auditor')->get();

        } else {
            // Jika BUKAN Auditor (User Biasa):
            // Hanya ambil file milik user yang sedang login
            $fileQuery->where('user_id', $user->id);
            $files = $fileQuery->get();
        }

        return view('smkp.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs', 'units'));
    }

    public function upload(Request $request, $folderId = null)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'name' => 'required|string|max:255', 
        ]);

        $file = $request->file('file');
        // Simpan ke storage/app/public/smkp_files
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId,
            'user_id'   => Auth::id(),
            'name'      => $request->name,
            // Hapus prefix 'public/' agar path bisa diakses via asset() atau Storage::url()
            'file_path' => str_replace('public/', '', $path),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }

    // --- FOLDER MANAGEMENT (HANYA AUDITOR) ---

    public function createFolder(Request $request, $parentId = null)
    {
        // Pastikan hanya Auditor yang bisa akses
        if (Auth::user()->role !== 'Auditor') {
            abort(403, 'Hanya Auditor yang dapat membuat folder.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        Folder::create([
            'name' => $request->name,
            'code' => $request->code,
            'parent_id' => $parentId
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
        
        // Menghapus folder akan menghapus sub-folder & file secara cascade 
        // (pastikan setting foreign key database ON DELETE CASCADE, atau handle manual jika perlu)
        $folder->delete();

        if($parentId) {
            return to_route('smkp.index', $parentId)->with('success', 'Folder berhasil dihapus.');
        }
        return to_route('smkp.index')->with('success', 'Folder berhasil dihapus.');
    }
    
    // --- FILE ACTIONS (DOWNLOAD & DELETE) ---

    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        // Validasi: Hanya pemilik file atau Auditor yang boleh download
        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }

    public function deleteFile($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        // Validasi: Hanya pemilik file atau Auditor yang boleh hapus
        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus file ini.');
        }

        // Hapus fisik file dari storage
        if (Storage::exists('public/' . $file->file_path)) {
            Storage::delete('public/' . $file->file_path);
        }

        // Hapus record dari database
        $file->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}