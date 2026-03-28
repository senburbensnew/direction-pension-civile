@extends('layouts.main')

@section('content')
    <x-payment-stop-request :pensionCategories="$pensionCategories" :demande="$demande" :isDemandeReadyForSubmission="$isDemandeReadyForSubmission" />
@endsection
