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
            $files = [];
            $breadcrumbs = [];
        } else {
            $currentFolder = Folder::with('children', 'files')->findOrFail($folderId);
            $folders = $currentFolder->children; 
            $files = $currentFolder->files;     
            
            // Breadcrumb logika
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
            'file' => 'required|file|max:51200', // Max 50MB
            'name' => 'required|string|max:255', 
        ]);

        $file = $request->file('file');
        $path = $file->store('public/smkp_files');

        FileUpload::create([
            'folder_id' => $folderId,
            'name' => $request->name,
            'file_path' => str_replace('public/', '', $path),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return back()->with('success', 'File berhasil disimpan.');
    }

    // FUNGSI BARU: Membuat Folder
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
    
    public function download($id)
    {
        $file = FileUpload::findOrFail($id);
        return Storage::download('public/' . $file->file_path, $file->name . '.' . pathinfo($file->file_path, PATHINFO_EXTENSION));
    }
}