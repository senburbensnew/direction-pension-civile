<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreFaqItemRequest;
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
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('question', 'like', '%' . $q . '%')
                    ->orWhere('answer', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->paginate(20)->withQueryString();
        $categories = FaqItem::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.faq.index', compact('items', 'categories'));
    }

    public function store(StoreFaqItemRequest $request)
    {
        $data = $request->validated();
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        FaqItem::create($data);
        return back()->with('success', 'Question ajoutée.');
    }

    public function update(StoreFaqItemRequest $request, FaqItem $faqItem)
    {
        $data = $request->validated();
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        $faqItem->update($data);
        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Request $request, FaqItem $faqItem)
    {
        return back()->with('success', $this->adminDelete($faqItem, $request));
    }

    public function togglePublish(FaqItem $faqItem)
    {
        $faqItem->update(['published' => !$faqItem->published]);
        return back()->with('success', $faqItem->published ? 'Publiée.' : 'Dépubliée.');
    }
}
