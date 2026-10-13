<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresOptimizedImages;
use App\Models\MediathequeItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminMediathequeController extends Controller
{
    use StoresOptimizedImages;

    public function index()
    {
        $items = MediathequeItem::ordered()->get()->groupBy('type');
        $total = MediathequeItem::count();

        return view('admin.mediatheque.index', compact('items', 'total'));
    }

    public function create()
    {
        return view('admin.mediatheque.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:image,video,audio,document',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published' => 'boolean',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $this->storeMediathequeFile($request->file('file'), $request->type);
        }

        MediathequeItem::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $filePath,
            'url' => $request->url,
            'order_column' => $request->order_column ?? 0,
            'published' => $request->boolean('published', true),
        ]);

        return redirect()->route('admin.mediatheque.index')
            ->with('success', 'Élément ajouté à la médiathèque.');
    }

    public function edit(MediathequeItem $mediathequeItem)
    {
        return view('admin.mediatheque.edit', ['item' => $mediathequeItem]);
    }

    public function update(Request $request, MediathequeItem $mediathequeItem)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:image,video,audio,document',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $this->deleteMediathequeFile($mediathequeItem);
            $mediathequeItem->file_path = $this->storeMediathequeFile($request->file('file'), $request->type);
        }

        $mediathequeItem->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'url' => $request->url,
            'order_column' => $request->order_column ?? $mediathequeItem->order_column,
            'published' => $request->boolean('published', true),
            'file_path' => $mediathequeItem->file_path,
        ]);

        return redirect()->route('admin.mediatheque.index')
            ->with('success', 'Élément mis à jour.');
    }

    public function destroy(MediathequeItem $mediathequeItem)
    {
        $this->deleteMediathequeFile($mediathequeItem);
        $mediathequeItem->delete();

        return redirect()->route('admin.mediatheque.index')
            ->with('success', 'Élément supprimé.');
    }

    private function storeMediathequeFile(UploadedFile $file, string $type): string
    {
        $directory = 'mediatheque/'.$type;

        if ($type === 'image') {
            return $this->storeOptimizedImage($file, $directory, 'mediatheque');
        }

        return $file->store($directory, 'public');
    }

    private function deleteMediathequeFile(MediathequeItem $item): void
    {
        if (! $item->file_path) {
            return;
        }

        if ($item->type === 'image') {
            $this->deleteOptimizedImage($item->file_path);

            return;
        }

        Storage::disk('public')->delete($item->file_path);
    }
}
