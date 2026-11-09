<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReorderCarouselsRequest;
use App\Http\Requests\Admin\StoreCarouselRequest;
use App\Http\Requests\Admin\UpdateCarouselRequest;
use App\Models\Carousel;
use App\Services\ImageStorageService;
use App\Support\Storage\UploadPaths;

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

    public function store(StoreCarouselRequest $request)
    {
        $imagePath = app(ImageStorageService::class)->storeUploadedImage(
            $request->file('image'),
            UploadPaths::cms('carousel'),
            config('media.upload_max_widths.carousel'),
        );

        Carousel::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'link' => $request->filled('link') ? $request->link : null,
            'cta_label' => $request->cta_label,
            'overlay_position' => $request->overlay_position ?? 'bottom-left',
            'text_size' => $request->text_size ?? 'md',
            'text_color' => $request->text_color ?? '#ffffff',
            'text_styles' => $request->input('text_styles', []),
            'font_family' => $request->font_family ?? 'sans',
            'status' => $request->boolean('status', true),
            'order' => $request->order ?? (Carousel::max('order') + 1),
        ]);

        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide added successfully.');
    }

    public function edit(Carousel $carousel)
    {
        return view('admin.carousel.edit', compact('carousel'));
    }

    public function update(UpdateCarouselRequest $request, Carousel $carousel)
    {
        if ($request->hasFile('image')) {
            app(ImageStorageService::class)->deleteWithVariants($carousel->image);
            $carousel->image = app(ImageStorageService::class)->storeUploadedImage(
                $request->file('image'),
                UploadPaths::cms('carousel'),
                config('media.upload_max_widths.carousel'),
            );
        }

        $carousel->update([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->filled('link') ? $request->link : null,
            'cta_label' => $request->cta_label,
            'overlay_position' => $request->overlay_position ?? $carousel->overlay_position,
            'text_size' => $request->text_size ?? $carousel->text_size,
            'text_color' => $request->text_color ?? $carousel->text_color,
            'text_styles' => $request->input('text_styles', []),
            'font_family' => $request->font_family ?? $carousel->font_family,
            'status' => $request->boolean('status', true),
            'order' => $request->order ?? $carousel->order,
        ]);

        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide updated successfully.');
    }

    public function reorder(ReorderCarouselsRequest $request)
    {
        foreach ($request->validated('order') as $position => $id) {
            Carousel::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Carousel $carousel)
    {
        app(ImageStorageService::class)->deleteWithVariants($carousel->image);
        $carousel->delete();

        return redirect()->route('admin.carousels.index')->with('success', 'Diapositive supprimée.');
    }
}
