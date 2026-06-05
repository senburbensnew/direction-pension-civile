<?php

namespace App\Http\Controllers;

use App\Models\MediathequeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediathequeController extends Controller
{
    public function publicIndex()
    {
        $images = MediathequeItem::published()->where('type', 'image')->ordered()->get();
        $videos = MediathequeItem::published()->where('type', 'video')->ordered()->get();
        $audios = MediathequeItem::published()->where('type', 'audio')->ordered()->get();
        return view('communication.mediatheque', compact('images', 'videos', 'audios'));
    }

    public function adminIndex(Request $request)
    {
        $query = MediathequeItem::orderBy('order_column')->orderBy('created_at', 'desc');
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $items = $query->paginate(20)->withQueryString();
        $types = MediathequeItem::$types;
        return view('admin.mediatheque.index', compact('items', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'type'         => 'required|string|in:image,video,audio,document',
            'file'         => 'nullable|file|max:51200',
            'url'          => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);

        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('mediatheque', 'public');
        }

        MediathequeItem::create($data);
        return back()->with('success', 'Élément ajouté à la médiathèque.');
    }

    public function update(Request $request, MediathequeItem $mediathequeItem)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'type'         => 'required|string|in:image,video,audio,document',
            'file'         => 'nullable|file|max:51200',
            'url'          => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);

        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            if ($mediathequeItem->file_path) {
                Storage::disk('public')->delete($mediathequeItem->file_path);
            }
            $data['file_path'] = $request->file('file')->store('mediatheque', 'public');
        }

        $mediathequeItem->update($data);
        return back()->with('success', 'Élément mis à jour.');
    }

    public function destroy(MediathequeItem $mediathequeItem)
    {
        if ($mediathequeItem->file_path) {
            Storage::disk('public')->delete($mediathequeItem->file_path);
        }
        $mediathequeItem->delete();
        return back()->with('success', 'Élément supprimé.');
    }

    public function togglePublish(MediathequeItem $mediathequeItem)
    {
        $mediathequeItem->update(['published' => !$mediathequeItem->published]);
        return back()->with('success', $mediathequeItem->published ? 'Publié.' : 'Dépublié.');
    }
}
