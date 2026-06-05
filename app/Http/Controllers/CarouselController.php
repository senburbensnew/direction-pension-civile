<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarouselController extends Controller
{
    public function index()
    {
        $carousels = Carousel::ordered()->get();
        return view('admin.carousel.index', compact('carousels'));
    }
    

    public function create()
    {
        return view('admin.carousel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'image'            => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            'link'             => 'nullable|url|max:255',
            'cta_label'        => 'nullable|string|max:60',
            'overlay_position' => 'nullable|string',
            'text_size'        => 'nullable|string|in:sm,md,lg,xl',
            'text_color'       => 'nullable|string|max:20',
            'text_styles'      => 'nullable|array',
            'text_styles.*'    => 'in:bold,italic,underline,uppercase',
            'font_family'      => 'nullable|string|in:sans,serif,playfair,oswald,condensed',
            'status'           => 'boolean',
            'order'            => 'nullable|integer|min:0',
        ]);

        $imagePath = $request->file('image')->store('carousel', 'public');

        Carousel::create([
            'title'            => $request->title,
            'description'      => $request->description,
            'image'            => $imagePath,
            'link'             => $request->link,
            'cta_label'        => $request->cta_label,
            'overlay_position' => $request->overlay_position ?? 'bottom-left',
            'text_size'        => $request->text_size ?? 'md',
            'text_color'       => $request->text_color ?? '#ffffff',
            'text_styles'      => $request->input('text_styles', []),
            'font_family'      => $request->font_family ?? 'sans',
            'status'           => $request->boolean('status', true),
            'order'            => $request->order ?? (Carousel::max('order') + 1),
        ]);
    
        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide added successfully.');
    }    

    public function edit(Carousel $carousel)
    {
        return view('admin.carousel.edit', compact('carousel'));
    }

    public function update(Request $request, Carousel $carousel)
    {
        $request->validate([
            'title'            => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'link'             => 'nullable|url|max:255',
            'cta_label'        => 'nullable|string|max:60',
            'overlay_position' => 'nullable|string',
            'text_size'        => 'nullable|string|in:sm,md,lg,xl',
            'text_color'       => 'nullable|string|max:20',
            'text_styles'      => 'nullable|array',
            'text_styles.*'    => 'in:bold,italic,underline,uppercase',
            'font_family'      => 'nullable|string|in:sans,serif,playfair,oswald,condensed',
            'status'           => 'boolean',
            'order'            => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($carousel->image);
            $carousel->image = $request->file('image')->store('carousel', 'public');
        }

        $carousel->update([
            'title'            => $request->title,
            'description'      => $request->description,
            'link'             => $request->link,
            'cta_label'        => $request->cta_label,
            'overlay_position' => $request->overlay_position ?? $carousel->overlay_position,
            'text_size'        => $request->text_size ?? $carousel->text_size,
            'text_color'       => $request->text_color ?? $carousel->text_color,
            'text_styles'      => $request->input('text_styles', []),
            'font_family'      => $request->font_family ?? $carousel->font_family,
            'status'           => $request->boolean('status', true),
            'order'            => $request->order ?? $carousel->order,
        ]);
    
        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide updated successfully.');
    }
    

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:carousels,id']);

        foreach ($request->order as $position => $id) {
            Carousel::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Carousel $carousel)
    {
        Storage::disk('public')->delete($carousel->image);
        $carousel->delete();

        return redirect()->route('admin.carousels.index')->with('success', 'Diapositive supprimée.');
    }
}