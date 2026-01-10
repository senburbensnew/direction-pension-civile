@extends('layouts.main')

@section('content')
    <x-payment-stop-request :pensionCategories="$pensionCategories" />
@endsection
