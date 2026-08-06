@extends('layouts.main')

@section('title', 'Structure Organique')

@section('content')
<style>
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Qui sommes-nous</span>
            <h1 class="text-4xl font-bold gradient-text mt-2 mb-3">Structure organique</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                La Direction de la Pension Civile (DPC) est une entité sous la tutelle de la Direction Générale du MEF.
                Elle est composée de plusieurs services et cellules qui assurent une gestion efficace des pensions civiles.
            </p>
        </div>

        <section class="bg-white rounded-2xl border border-gray-200 card-shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-sitemap text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Organigramme</h2>
                        <p class="text-xs text-gray-500">Organisation des services de la DPC</p>
                    </div>
                </div>
                <a href="{{ asset('images/dpc_organigram.jpg') }}"
                   target="_blank"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1.5">
                    Agrandir <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="p-4 sm:p-6 bg-gray-50">
                <img src="{{ asset('images/dpc_organigram.jpg') }}"
                     alt="Organigramme de la Direction de la Pension Civile"
                     class="w-full h-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fas fa-network-wired text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Coordination</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Chaque service fonctionne sous la supervision de la Directrice Générale et collabore
                        avec les autres services pour assurer une gestion efficace des pensions civiles.
                    </p>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
