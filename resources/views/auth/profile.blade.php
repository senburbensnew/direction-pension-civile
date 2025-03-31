@extends('layouts.main')

@section('title', 'Modifier profil')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Profil</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Modifier profil</span>
        </nav>

        <div class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <h2 class="text-lg md:text-xl font-bold mb-4">Modifier votre profil</h2>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="#" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo actuel</label>
                    <div
                        class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden mb-4 relative group">
                        @if (auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile Picture"
                                class="h-full w-full object-cover">
                            <div
                                class="absolute inset-0 bg-black bg-opacity-40 hidden group-hover:flex items-center justify-center">
                                <span class="text-white text-sm">Changer</span>
                            </div>
                        @else
                            <svg class="h-12 w-12 text-gray-400 group-hover:text-gray-500 transition-colors"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="relative">
                            <input type="file" name="avatar" id="avatar"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Aucun fichier sélectionné'">

                            <div
                                class="flex items-center justify-center px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg 
                                    hover:border-blue-500 hover:bg-blue-50 transition-colors duration-200
                                    {{ $errors->has('avatar') ? 'border-red-500' : '' }}">
                                <svg class="w-5 h-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600">
                                    <span id="file-name">Choisir un fichier</span>
                                </span>
                            </div>
                        </div>

                        @error('avatar')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Formats supportés: JPEG, PNG - Max 2MB</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach (['firstname' => 'Prénom', 'lastname' => 'Nom', 'email' => 'Email', 'nif' => 'NIF', 'ninu' => 'NINU', 'phone' => 'Telephone'] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $label }} *
                                @if ($field === 'email' || $field === 'firstname' || $field === 'lastname')
                                    <span class="text-gray-500 text-xs">(non modifiable)</span>
                                @endif
                            </label>
                            <input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}"
                                id="{{ $field }}" value="{{ old($field, auth()->user()->$field) }}"
                                class="w-full rounded-md @error($field) border-red-500 @else border-gray-300 @enderror
                                          {{ $field === 'email' || $field === 'firstname' || $field === 'lastname' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                {{ $field === 'email' || $field === 'firstname' || $field === 'lastname' ? 'readonly' : '' }}>
                            @error($field)
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de
                            passe</label>
                        <input type="password" name="password" id="password"
                            class="w-full rounded-md @error('password') border-red-500 @else border-gray-300 @enderror"
                            placeholder="••••••••">
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmer le mot de passe
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full rounded-md border-gray-300" placeholder="••••••••">
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update file name display
        document.getElementById('avatar').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Aucun fichier sélectionné';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
@endsection
