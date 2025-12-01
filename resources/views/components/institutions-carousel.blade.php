<section class="bg-white fade-in overflow-hidden border-t py-6">
    <div class="relative w-full overflow-hidden">

        <!-- TRACK (DUPLIQUÉ POUR BOUCLE INFINIE) -->
        <div class="flex items-center gap-20 animate-scroll whitespace-nowrap">

            @foreach ($institutions as $ins)
                <div class="flex flex-col items-center min-w-[200px]">
                    <img src="{{ asset($ins['logo']) }}"
                         alt="{{ $ins['name'] }}"
                         class="w-24 h-24 mb-2 object-contain">
                    <h6 class="text-center text-gray-700 font-medium whitespace-normal break-words">
                        {{ $ins['name'] }}
                    </h6>
                </div>
            @endforeach

            <!-- DUPLICATE -->
            @foreach ($institutions as $ins)
                <div class="flex flex-col items-center min-w-[200px]">
                    <img src="{{ asset($ins['logo']) }}"
                         alt="{{ $ins['name'] }}"
                         class="w-24 h-24 mb-2 object-contain">
                    <h6 class="text-center text-gray-700 font-medium whitespace-normal break-words">
                        {{ $ins['name'] }}
                    </h6>
                </div>
            @endforeach

        </div>

    </div>
</section>

<style>
@keyframes scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.animate-scroll {
    animation: scroll 30s linear infinite;
}
</style>
