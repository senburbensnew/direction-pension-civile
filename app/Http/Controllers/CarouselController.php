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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);
    
        $imagePath = $request->file('image')->store('carousel', 'public');
    
        Carousel::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status ?? true,
            'order' => $request->order ?? Carousel::max('order') + 1
        ]);
    
        return redirect()->route('carousels.index')->with('success', 'Carousel slide added successfully.');
    }    

    public function edit(Carousel $carousel)
    {
        return view('admin.carousel.edit', compact('carousel'));
    }

    public function update(Request $request, Carousel $carousel)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);
    
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($carousel->image);
            $imagePath = $request->file('image')->store('carousel', 'public');
            $carousel->image = $imagePath;
        }
    
        $carousel->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? true,
            'order' => $request->order ?? $carousel->order
        ]);
    
        return redirect()->route('carousels.index')->with('success', 'Carousel slide updated successfully.');
    }
    

    public function destroy(Carousel $carousel)
    {
        Storage::disk('public')->delete($carousel->image);
        $carousel->delete();

        return redirect()->route('carousels.index')->with('success', 'Carousel slide deleted.');
    }
}