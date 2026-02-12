<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SmkpController extends Controller
{
    public function index($folderId = null)
    {
        $user = Auth::user();

        // Query Folder (Semua role bisa melihat folder)
        if (!$folderId) {
            $folders = Folder::whereNull('parent_id')->get();
            $currentFolder = null;
            $breadcrumbs = [];
            
            // Query File di Root
            $fileQuery = FileUpload::whereNull('folder_id');
        } else {
            $currentFolder = Folder::with('children')->findOrFail($folderId);
            $folders = $currentFolder->children;
            
            // Breadcrumbs
            $breadcrumbs = [];
            $temp = $currentFolder;
            while($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }

            // Query File di Folder ini
            $fileQuery = $currentFolder->files()->getQuery();     
        }

        // LOGIKA FILTER FILE BERDASARKAN ROLE
        // Jika BUKAN Auditor, hanya tampilkan file milik sendiri (user_id = Auth::id())
        if ($user->role !== 'Auditor') {
            $fileQuery->where('user_id', $user->id);
        }
        
        // Ambil data file setelah difilter
        $files = $fileQuery->get();

        return view('smkp.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    public function upload(Request $request, $folderId = null)
    {
        $request->validate([
            'file' => 'required|file|max:51200', 
            'name' => 'required|string|max:255', 
        ]);

        $file = $request->file('file');
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId,
            'user_id'   => Auth::id(), // Simpan ID User pengupload
            'name'      => $request->name,
            'file_path' => str_replace('public/', '', $path),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }

    // --- FOLDER MANAGEMENT (HANYA AUDITOR) ---

    public function createFolder(Request $request, $parentId = null)
    {
        // Cek Role
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
        // Cek Role
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
        // Cek Role
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
    
    // --- FILE ACTIONS (DOWNLOAD & DELETE) ---

    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        // Cek Hak Akses: Boleh jika Auditor ATAU Pemilik File
        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }

    public function deleteFile($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();

        // Cek Hak Akses: Boleh jika Auditor ATAU Pemilik File
        if ($user->role !== 'Auditor' && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus file ini.');
        }

        // Hapus fisik file
        if (Storage::exists('public/' . $file->file_path)) {
            Storage::delete('public/' . $file->file_path);
        }

        // Hapus data
        $file->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}