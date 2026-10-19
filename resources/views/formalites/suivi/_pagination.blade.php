@props(['paginator'])

@if($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->total() > 0)
    <div class="px-5 py-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/60">
        <p class="text-xs text-gray-600 tabular-nums">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>
        @if($paginator->hasPages())
            <div class="text-sm">{{ $paginator->withQueryString()->links() }}</div>
        @endif
    </div>
@endif
