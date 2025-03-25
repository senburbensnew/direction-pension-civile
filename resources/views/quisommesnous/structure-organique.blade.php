@extends('layouts.main')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Structure Organique</h1>

            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                <p class="text-lg text-gray-700">
                    La Direction de la Pension Civile (DPC) est une entité sous la tutelle de la Direction Générale du MEF.
                    Elle est composée de plusieurs services et cellules qui assurent une gestion efficace des pensions
                    civiles.
                </p>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-5">Organigramme</h2>
            <img src="{{ asset('images/dpc_organigram.jpg') }}" alt="Organigramme de la DPC" class="w-full h-auto">

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Coordination</h2>
            <p class="text-gray-600">
                Chaque service fonctionne sous la supervision de la Directrice Générale et collabore avec les autres
                services pour assurer une gestion efficace des pensions civiles.
            </p>
        </div>
    </div>
@endsection
