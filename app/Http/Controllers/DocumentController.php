<?php

namespace App\Http\Controllers;

use App\Support\Storage\UploadPaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index()
    {
        return view('admin.documents.upload');
    }

    public function upload(Request $request)
    {
        $messages = [
            'pdf_file.mimes' => 'Le fichier doit être un document PDF.',
            'pdf_file.max' => 'Le fichier ne doit pas dépasser 2MB.',
        ];

        $request->validate([
            'pdf_name' => 'required|string',
            'pdf_file' => 'required|file|mimes:pdf|max:2048',
        ], $messages);

        $basename = Str::slug($request->input('pdf_name')).'.pdf';

        Storage::disk(UploadPaths::publicDisk())->putFileAs(
            UploadPaths::cms('publications'),
            $request->file('pdf_file'),
            $basename
        );

        return back()->with('success', 'Le document a été téléversé avec succès.');
    }
}
