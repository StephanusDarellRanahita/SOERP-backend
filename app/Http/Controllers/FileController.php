<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Folder;

class FileController extends Controller
{
    public function uploadFiles(Request $request, $company)
    {
        $request->validate([
            'file.*' => 'required}file',
        ]);

        $files = [];
        if ($request->id_folder) {
            $folder = Folder::where('id', $request->id_folder)->first();
            foreach ($request->file('files') as $file) {
                $timestamp = time();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $type = $file->getClientMimeType();
                $size = $file->getSize();
                $fileName = $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
                $path = $folder->folder;
                $store = $file->storeAs($path, $fileName, 'public');

                $files[] = File::create([
                    'id_folder' => $request->id_folder,
                    'name' => $originalName,
                    'file' => $store,
                    'size' => $size,
                    'type' => $type,
                    'company' => $company
                ]);
            }
        } else {
            foreach ($request->file('files') as $file) {
                $timestamp = time();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $type = $file->getClientMimeType();
                $size = $file->getSize();
                $fileName = $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
                $path = $company . '/files';
                $store = $file->storeAs($path, $fileName, 'public');

                $files[] = File::create([
                    'name' => $originalName,
                    'file' => $store,
                    'size' => $size,
                    'type' => $type,
                    'company' => $company
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Upload file success',
            'files' => $request->id_folder
        ]);
    }

    public function uploadFolder(Request $request, $company)
    {
        $request->validate([
            'name' => 'required',
        ]);

        if ($request->id_folder) {
            $parent = Folder::where('id', $request->id_folder)->first();
            $folderPath = $parent->folder . '/' . $request->name;
            $folderData = [
                'id_folder' => $request->id_folder,
                'name' => $request->name,
                'folder' => $folderPath,
                'company' => $company
            ];
            Storage::disk('public')->makeDirectory($folderPath);
            $folder = Folder::create($folderData);
        } else {
            $path = $company . '/files';
            $folderPath = $path . '/' . $request->name;
            $folderData = [
                'name' => $request->name,
                'folder' => $folderPath,
                'company' => $company
            ];

            Storage::disk('public')->makeDirectory($folderPath);
            $folder = Folder::create($folderData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Folder ' . $request->name . ' uploaded successfully',
            'folder' => $folder
        ]);
    }

    public function getAllFiles($company)
    {
        $folders = Folder::where('company', $company)->get();
        $files = File::where('company', $company)->get();

        $foldersByParent = $folders->groupBy('id_folder');
        $filesByParent = $files->groupBy('id_folder');

        $buildTree = function ($parentId, $parentKey = '') use (&$buildTree, $foldersByParent, $filesByParent) {
            $counter = 0;
            $items = collect();
            foreach ($foldersByParent->get($parentId, collect()) as $folder) {
                $key = $parentKey === '' ? (string) $counter : $parentKey . '-' . $counter;
                $children = $buildTree($folder->id, $key);

                $items->push([
                    'key' => $key,
                    'data' => [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'path' => $folder->folder,
                        'type' => 'folder'
                    ],
                    'children' => $children,
                ]);
                $counter++;
            }

            foreach ($filesByParent->get($parentId, collect()) as $file) {
                $key = $parentKey === '' ? (string) $counter : $parentKey . '-' . $counter;
                $items->push([
                    'key' => $key,
                    'data' => [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->file,
                        'size' => $file->size,
                        'type' => $file->type
                    ],
                ]);
                $counter++;
            }
            return $items;
        };

        $tree = $buildTree(null);

        return response()->json([
            'success' => true,
            'message' => "Retrieve all files success",
            'files' => $tree
        ]);
    }
}
