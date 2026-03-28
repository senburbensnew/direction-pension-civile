@extends('layouts.main')

@section('content')
    <x-check-transfer-requests :pensionCategories="$pensionCategories" :demande="$demande" :isDemandeReadyForSubmission="$isDemandeReadyForSubmission" />
@endsection
