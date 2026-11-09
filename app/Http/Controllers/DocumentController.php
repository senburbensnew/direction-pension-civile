<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UploadDocumentRequest;
use App\Support\Storage\InteractsWithFilesystemAdapter;
use App\Support\Storage\UploadPaths;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    use InteractsWithFilesystemAdapter;

    public function index()
    {
        return view('admin.documents.upload');
    }

    public function upload(UploadDocumentRequest $request)
    {
        $basename = Str::slug($request->input('pdf_name')).'.pdf';

        $this->publicDiskAdapter()->putFileAs(
            UploadPaths::cms('publications'),
            $request->file('pdf_file'),
            $basename
        );

        return back()->with('success', 'Le document a été téléversé avec succès.');
    }

    public function show(string $filename)
    {
        $storagePath = UploadPaths::cms('publications').'/'.basename($filename);
        $disk = $this->publicDiskAdapter();

        abort_unless($disk->exists($storagePath), 404);

        return $disk->response($storagePath);
    }

    public function download(string $filename)
    {
        $storagePath = UploadPaths::cms('publications').'/'.basename($filename);
        $disk = $this->publicDiskAdapter();

        abort_unless($disk->exists($storagePath), 404);

        return $disk->download($storagePath, basename($filename));
    }
}
