<?php

namespace App\Http\Controllers;

use App\Models\FaqItem;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function publicIndex()
    {
        $items = FaqItem::published()->ordered()->get()->groupBy('category');
        return view('faq.index', compact('items'));
    }

    public function adminIndex(Request $request)
    {
        $query = FaqItem::orderBy('order_column')->orderBy('id');
        if ($request->filled('q')) {
            $query->where('question', 'like', '%' . $request->q . '%');
        }
        $items = $query->paginate(20)->withQueryString();
        return view('admin.faq.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'category'     => 'required|string|max:100',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        FaqItem::create($data);
        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, FaqItem $faqItem)
    {
        $data = $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'category'     => 'required|string|max:100',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        $faqItem->update($data);
        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(FaqItem $faqItem)
    {
        $faqItem->delete();
        return back()->with('success', 'Question supprimée.');
    }

    public function togglePublish(FaqItem $faqItem)
    {
        $faqItem->update(['published' => !$faqItem->published]);
        return back()->with('success', $faqItem->published ? 'Publiée.' : 'Dépubliée.');
    }
}
