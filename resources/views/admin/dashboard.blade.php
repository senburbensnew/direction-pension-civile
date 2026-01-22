@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Users -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-blue-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Utilisateurs</p>
                <p class="text-2xl font-bold">{{ $stats['users'] }}</p>
            </div>
        </div>

        <!-- Demandes -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-green-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m-6-8h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Demandes</p>
                <p class="text-2xl font-bold">{{ $stats['demandes'] }}</p>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-purple-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7h18M3 12h18M3 17h18"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Services</p>
                <p class="text-2xl font-bold">{{ $stats['services'] }}</p>
            </div>
        </div>

    </div>

    <!-- Row 2 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

        <!-- Roles -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-indigo-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4l3 6 6 .9-4.5 4.3 1 6-5.5-3-5.5 3 1-6L3 10.9 9 10z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Rôles</p>
                <p class="text-2xl font-bold">{{ $stats['roles'] }}</p>
            </div>
        </div>

        <!-- Permissions -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-yellow-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 11c0-3 4-3 4 0 0 2-2 2-2 4m-4 0h4m-2 4h.01"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Permissions</p>
                <p class="text-2xl font-bold">{{ $stats['permissions'] }}</p>
            </div>
        </div>

        <!-- Actualités -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-orange-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2zM7 8h10M7 12h10M7 16h6"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Actualités</p>
                <p class="text-2xl font-bold">{{ $stats['actualites'] }}</p>
            </div>
        </div>

    </div>

    <!-- Row 3 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <!-- Reports -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center">
            <div class="p-3 bg-red-500 rounded-full">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2h6v2m-6-4h6m-6-4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Rapports</p>
                <p class="text-2xl font-bold">{{ $stats['reports'] }}</p>
            </div>
        </div>

    </div>

</div>
@endsection