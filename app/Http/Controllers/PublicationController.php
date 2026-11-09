<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StorePublicationRequest;
use App\Http\Requests\Admin\StorePublicationTypeRequest;
use App\Http\Requests\Admin\UpdatePublicationTypeRequest;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Support\Storage\InteractsWithFilesystemAdapter;
use App\Support\Storage\UploadPaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    use InteractsWithFilesystemAdapter;

    public function publicIndex(Request $request)
    {
        $query = Publication::published()->ordered();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $publications = $query->get()->groupBy('type');
        $types = PublicationType::options();
        $typeVisuals = PublicationType::visuals();

        return view('communication.textes_publication', compact('publications', 'types', 'typeVisuals'));
    }

    public function download(Publication $publication)
    {
        $path = $publication->storageRelativePath();

        $disk = $this->publicDiskAdapter();

        if (! $path || ! $disk->exists($path)) {
            abort(404, 'Fichier introuvable.');
        }

        return $disk->download($path);
    }

    public function adminIndex(Request $request)
    {
        $query = Publication::orderBy('order_column')->orderBy('created_at', 'desc');
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $publications = $query->paginate(20)->withQueryString();
        $types = PublicationType::options();
        $publicationTypes = PublicationType::ordered()->get();

        return view('admin.publications.index', compact('publications', 'types', 'publicationTypes'));
    }

    public function store(StorePublicationRequest $request)
    {
        $data = $request->validated();

        $data['published'] = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store(UploadPaths::cms('publications'), UploadPaths::publicDisk());
        }

        Publication::create($data);

        return back()->with('success', 'Publication ajoutée.');
    }

    public function update(StorePublicationRequest $request, Publication $publication)
    {
        $data = $request->validated();

        $data['published'] = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            $previous = $publication->storageRelativePath();
            if ($previous) {
                Storage::disk(UploadPaths::publicDisk())->delete($previous);
            }
            $data['file_path'] = $request->file('file')->store(UploadPaths::cms('publications'), UploadPaths::publicDisk());
        }

        $publication->update($data);

        return back()->with('success', 'Publication mise à jour.');
    }

    public function destroy(Publication $publication)
    {
        $path = $publication->storageRelativePath();
        if ($path) {
            Storage::disk(UploadPaths::publicDisk())->delete($path);
        }
        $publication->delete();

        return back()->with('success', 'Publication supprimée.');
    }

    public function togglePublish(Publication $publication)
    {
        $publication->update(['published' => ! $publication->published]);

        return back()->with('success', $publication->published ? 'Publiée.' : 'Dépubliée.');
    }

    public function storeType(StorePublicationTypeRequest $request)
    {
        $data = $request->validated();

        $data['code'] = $data['code'] ?: PublicationType::makeCode($data['label']);
        $data['icon'] = $data['icon'] ?: 'fa-file';
        $data['badge_class'] = $data['badge_class'] ?: 'bg-gray-100 text-gray-700';
        $data['order_column'] = $data['order_column'] ?? ((int) PublicationType::max('order_column') + 1);

        PublicationType::create($data);

        return back()->with('success', 'Type de document ajouté.');
    }

    public function updateType(UpdatePublicationTypeRequest $request, PublicationType $publicationType)
    {
        $data = $request->validated();

        $data['icon'] = $data['icon'] ?: $publicationType->icon;
        $data['badge_class'] = $data['badge_class'] ?: $publicationType->badge_class;
        $data['order_column'] = $data['order_column'] ?? $publicationType->order_column;

        $publicationType->update($data);

        return back()->with('success', 'Type de document mis à jour.');
    }

    public function destroyType(PublicationType $publicationType)
    {
        $count = Publication::where('type', $publicationType->code)->count();
        if ($count > 0) {
            return back()->with('error', "Impossible de supprimer « {$publicationType->label} » : {$count} publication(s) l'utilisent encore.");
        }

        $publicationType->delete();

        return back()->with('success', 'Type de document supprimé.');
    }
}
