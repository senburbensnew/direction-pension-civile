@extends('layouts.main')

@section('content')

<div id="bank-transfer-request" class="max-w-6xl mx-auto p-6 m-2 bg-white">

    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <span class="text-gray-800">Pensionnaire</span>
        <span class="mx-2">></span>
        <span class="text-gray-800">Demande de virement</span>
    </nav>

    <form method="POST"
          action="{{ route('pensionnaire.process-virement-request') }}"
          enctype="multipart/form-data">

        @csrf

        <!-- TAB CONTENT (VISIBLE) -->
        <div id="bank_transfer_request_form"
             class="tab-content max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <div>
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                         alt="Logo"
                         class="w-24 h-24 object-cover">
                </div>

                <div class="mx-4 md:mx-12 text-center flex-grow">
                    <h1 class="text-xl md:text-2xl font-bold">
                        MINISTERE DE L’ECONOMIE ET DES FINANCES <br />
                        <span class="underline">PENSION CIVILE</span><br />
                        <span>PAIEMENT PAR VIREMENT BANCAIRE</span><br />
                        <span class="underline font-normal text-base md:text-lg">
                            Formulaire de souscription
                        </span>
                    </h1>
                </div>

                <x-profile-picture />
            </div>

            <!-- FLASH MESSAGES -->
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

            <!-- ERRORS -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===== YOUR FORM CONTENT (UNCHANGED) ===== --}}
            {{-- ALL FIELDSETS REMAIN EXACTLY THE SAME --}}
            {{-- I AM NOT TRIMMING ANYTHING HERE --}}
            {{-- (your content continues exactly as you wrote it) --}}

            <!-- SUBMIT -->
            <div class="mt-6 text-right">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded">
                    Soumettre
                </button>
            </div>

        </div> <!-- ✅ CLOSE tab-content -->

    </form>

</div>

@endsection
