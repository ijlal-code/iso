<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SmkpController extends Controller
{
    public function index($folderId = null)
    {
        if (!$folderId) {
            $folders = Folder::whereNull('parent_id')->get();
            $currentFolder = null;
            $files = FileUpload::whereNull('folder_id')->get(); // Ambil file di root
            $breadcrumbs = [];
        } else {
            $currentFolder = Folder::with('children', 'files')->findOrFail($folderId);
            $folders = $currentFolder->children; 
            $files = $currentFolder->files;     
            
            $breadcrumbs = [];
            $temp = $currentFolder;
            while($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        return view('smkp.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    // Update parameter agar folderId opsional
    public function upload(Request $request, $folderId = null)
    {
        $request->validate([
            'file' => 'required|file|max:51200', 
            'name' => 'required|string|max:255', 
        ]);

        $file = $request->file('file');
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId, // Bisa null jika di root
            'name' => $request->name,
            'file_path' => str_replace('public/', '', $path),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }

    public function createFolder(Request $request, $parentId = null)
    {
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

    // FUNGSI BARU: Edit Folder
    public function updateFolder(Request $request, $id)
    {
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

    // FUNGSI BARU: Hapus Folder
    public function deleteFolder($id)
    {
        $folder = Folder::findOrFail($id);
        $parentId = $folder->parent_id;
        
        // Hapus folder (file di dalamnya otomatis terhapus jika onCascade delete di migration aktif)
        // Jika tidak, Anda perlu loop delete file secara manual.
        // Asumsi migration Anda sudah onDelete('cascade').
        $folder->delete();

        // Redirect ke parent folder atau ke home
        if($parentId) {
            return to_route('smkp.index', $parentId)->with('success', 'Folder berhasil dihapus.');
        }
        return to_route('smkp.index')->with('success', 'Folder berhasil dihapus.');
    }
    
    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }

    public function deleteFile($id)
{
    $file = FileUpload::findOrFail($id);

    // Hapus fisik file dari storage jika ada
    if (Storage::exists('public/' . $file->file_path)) {
        Storage::delete('public/' . $file->file_path);
    }

    // Hapus data dari database
    $file->delete();

    return back()->with('success', 'Dokumen berhasil dihapus.');
}
}