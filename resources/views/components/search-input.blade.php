<div class="w-full flex flex-col items-center justify-center p-2 hidden search-icon">
    <div class="relative w-3/4">
        <input type="text" placeholder="{{ __('messages.search') }}"
            class="w-full p-3 pr-12 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="button" class="absolute top-4 right-3">
            <svg class="w-5 h-5 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </button>
    </div>
    <div id="search_results" class="w-3/4"></div>
</div>
