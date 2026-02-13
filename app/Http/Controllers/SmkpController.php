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

        // --- 1. LOGIKA FOLDER & BREADCRUMBS ---
        if (!$folderId) {
            // Root
            $folders = Folder::whereNull('parent_id')->get();
            $currentFolder = null;
            $breadcrumbs = [];
            
            // Query dasar file di root
            $fileQuery = FileUpload::whereNull('folder_id');
        } else {
            // Inside Folder
            $currentFolder = Folder::with('children')->findOrFail($folderId);
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

        // --- 2. LOGIKA FILTER & PENCARIAN CANGGIH ---
        
        // A. Filter Dropdown Unit (Strict/Pasti)
        if ($request->has('unit') && $request->unit != '') {
            $fileQuery->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->unit);
            });
        }

        // B. Pencarian Text Global (Nama File, Nama Pengirim, Nama Unit)
        if ($request->has('q') && $request->q != '') {
            $search = $request->q;
            
            $fileQuery->where(function($query) use ($search) {
                // 1. Cari berdasarkan Nama File
                $query->where('name', 'like', '%' . $search . '%')
                      // 2. ATAU Cari berdasarkan data User (Pengirim/Unit)
                      ->orWhereHas('user', function($u) use ($search) {
                          $u->where('name', 'like', '%' . $search . '%') // Nama Pengirim
                            ->orWhere('role', 'like', '%' . $search . '%'); // Nama Unit
                      });
            });
        }

        // --- 3. LOGIKA AKSES ROLE ---
        if ($user->role === 'Auditor') {
            // AUDITOR: Melihat semua file (yang sudah difilter di atas)
            $files = $fileQuery->with('user')->latest()->get();

            // Ambil list unit untuk dropdown (hanya user selain Auditor)
            // (Opsional: Jika ingin dinamis dari DB)
            // $units = User::select('role')->distinct()->where('role', '!=', 'Auditor')->orderBy('role')->pluck('role');

        } else {
            // USER BIASA: Hanya file milik sendiri
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
        
        $folder->delete();

        if($parentId) {
            return to_route('smkp.index', $parentId)->with('success', 'Folder berhasil dihapus.');
        }
        return to_route('smkp.index')->with('success', 'Folder berhasil dihapus.');
    }
    
    // --- FILE ACTIONS ---

    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }

    public function deleteFile($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

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