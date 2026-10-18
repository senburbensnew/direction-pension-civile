{{-- Flash success/error : toast global via layouts.main --}}
@error('email')
    <p class="text-red-300 text-base mt-2">{{ $message }}</p>
@enderror
