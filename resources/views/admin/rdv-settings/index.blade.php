@extends('layouts.admin')

@section('title', 'Paramètres RDV')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Paramètres RDV</span>
@endsection

@section('content')
<div class="site-gutter space-y-5">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Paramètres des rendez-vous</h1>
        <p class="text-sm text-gray-500 mt-0.5">Règles communes aux calendriers de réservation (portail) et aux disponibilités agents.</p>
    </div>

    <form
        method="POST"
        action="{{ route('admin.rdv-settings.update') }}"
        data-rdv-booking-times
        data-slot-minutes="{{ $slotMinutes }}"
        data-initial-start="{{ $bookingStart }}"
        data-initial-end="{{ $bookingEnd }}"
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
    >
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-week text-indigo-600 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Planification</p>
                <p class="text-xs text-gray-400">Jours et heures dans les calendriers FullCalendar</p>
            </div>
        </div>

        <div class="p-5 space-y-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="allow_weekends" value="0">
                <input
                    type="checkbox"
                    name="allow_weekends"
                    value="1"
                    class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    @checked(old('allow_weekends', $allowWeekends))
                >
                <span>
                    <span class="block text-sm font-medium text-gray-800">Autoriser la réservation le week-end</span>
                    <span class="block text-xs text-gray-500 mt-0.5">
                        Samedi et dimanche deviennent sélectionnables dans les calendriers et acceptés par la validation serveur.
                        Les jours fériés haïtiens restent exclus.
                    </span>
                </span>
            </label>

            <div class="border-t border-gray-100 pt-6 space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-800">Plage horaire des créneaux</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Heures proposées dans les calendriers de réservation et validées à la soumission.
                        Intervalle entre créneaux : {{ $slotMinutes }} minutes.
                        Utilisez <strong>24:00</strong> comme fin pour couvrir jusqu’à la fin de la journée.
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
                    <div>
                        <label for="booking_start" class="block text-xs font-medium text-gray-600 mb-1">Début (24 h)</label>
                        <input
                            type="text"
                            id="booking_start"
                            name="booking_start"
                            value="{{ old('booking_start', $bookingStart) }}"
                            data-rdv-booking-start
                            required
                            autocomplete="off"
                            class="w-full rounded-lg border-gray-300 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('booking_start')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="booking_end" class="block text-xs font-medium text-gray-600 mb-1">Fin (24 h)</label>
                        <input
                            type="text"
                            id="booking_end"
                            name="booking_end"
                            value="{{ old('booking_end', $bookingEnd) }}"
                            data-rdv-booking-end
                            required
                            autocomplete="off"
                            class="w-full rounded-lg border-gray-300 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <button
                            type="button"
                            data-rdv-set-end-midnight
                            class="mt-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800"
                        >
                            Fin de journée → 24:00
                        </button>
                        @error('booking_end')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 border-t border-gray-100 pt-4">
                État actuel :
                <strong>{{ $bookingDaysLabel }}</strong>,
                horaires : <strong>{{ $bookingHoursLabel }}</strong>
            </p>
        </div>

        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </div>
    </form>

    <a href="{{ route('admin.settings') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-800">
        <i class="fas fa-arrow-left text-xs"></i> Retour aux paramètres
    </a>

</div>
@endsection
