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
        // Jika tidak ada ID, ambil folder root (yang parent_id nya null)
        if (!$folderId) {
            $folders = Folder::whereNull('parent_id')->get();
            $currentFolder = null;
            $files = [];
            $breadcrumbs = [];
        } else {
            $currentFolder = Folder::with('children', 'files')->findOrFail($folderId);
            $folders = $currentFolder->children; // Sub-folder
            $files = $currentFolder->files;     // File di folder ini
            
            // Membuat navigasi remah roti (Breadcrumb)
            $breadcrumbs = [];
            $temp = $currentFolder;
            while($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        return view('smkp.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    public function upload(Request $request, $folderId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'name' => 'required|string|max:255', // Nama custom dari user
        ]);

        $file = $request->file('file');
        // Simpan file dengan aman di storage/app/public/smkp_files
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId,
            'name' => $request->name,
            'file_path' => str_replace('public/', '', $path), // Simpan path relatif
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }
    
    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }
}