<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ActualiteController extends Controller
{
    public function index()
    {
        $actualites = Actualite::with('images')
            ->latest()
            ->paginate(9);

        return view('actualites.index', compact('actualites'));
    }


    public function adminIndex(Request $request){
        $query = Actualite::query();

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%')
                ->orWhere('category', 'like', '%' . $request->q . '%');
        }

        $actualites = $query
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('admin.actualites.index', compact('actualites'));
    }

    // Show the form for creating a new resource
    public function create(Request $request)
    {
        return view('admin.actualites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_text' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'posted_in' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create actualité
            $actualite = Actualite::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'content_text' => $validated['content_text'] ?? null,
                'category' => $validated['category'] ?? null,
                'posted_in' => $validated['posted_in'] ?? null,
                'published_at' => $validated['published_at'] ?? null,
            ]);

            // Keep track of stored files (important!)
            $storedPaths = [];

            // 2️⃣ Store images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('actualites', 'public');
                    $storedPaths[] = $path;

                    $actualite->images()->create([
                        'image_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('actualites.admin.index')
                ->with('success', 'Actualité créée avec succès.');

        } catch (\Throwable $e) {

            DB::rollBack();

            // 3️⃣ Cleanup uploaded files if DB fails
            if (!empty($storedPaths)) {
                foreach ($storedPaths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Une erreur est survenue lors de la création de l’actualité.'
                ]);
        }
    }

    // Display the specified resource
    public function show($id)
    {
        $actu = Actualite::findOrFail($id);
        return view('actualites.show', compact('actu'));
    }

    // Show the form for editing the specified resource
    public function edit($id)
    {
        $actualiteEdit = Actualite::findOrFail($id);
        return view('admin.actualites.edit', compact('actualiteEdit'));
    }

    // Update the specified resource in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $actualite = Actualite::findOrFail($id);
        $actualite->title = $request->title;
        $actualite->content = $request->content;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($actualite->image && \Storage::disk('public')->exists($actualite->image)) {
                \Storage::disk('public')->delete($actualite->image);
            }
            $path = $request->file('image')->store('actualites', 'public');
            $actualite->image = $path;
        }

        $actualite->save();

        return redirect()->route('actualites.index')
            ->with('success', 'Actualité mise à jour avec succès.');
    }

    // Remove the specified resource from storage
    public function destroy($id)
    {
        $actualite = Actualite::findOrFail($id);

        // Delete image if exists
        if ($actualite->image && \Storage::disk('public')->exists($actualite->image)) {
            \Storage::disk('public')->delete($actualite->image);
        }

        $actualite->delete();

        return redirect()->route('actualites.admin.index')
            ->with('success', 'Actualité supprimée avec succès.');
    }
}
