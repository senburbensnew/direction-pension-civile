<div class="border md:w-1/5 w-full mt-4 md:mt-0">
    <div class="text-center text-white" style="background-color: #173152;">
        {{ __('messages.new_feeds') }}
    </div>

    {{-- @forelse ($nouveautes as $nouveaute) --}}
    @forelse ([] as $nouveaute)
        <x-nouveaute :title="$nouveaute['title']" :date="$nouveaute['date']">
            {{ $nouveaute['content'] }}
        </x-nouveaute>
    @empty
        <div class="p-4 text-gray-500">{{ __('Aucune nouveauté pour le moment.') }}</div>
    @endforelse
</div>
