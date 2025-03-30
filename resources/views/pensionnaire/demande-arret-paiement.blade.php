@extends('layouts.main')

@section('content')
    <x-payment-stop-transfer-requests :pensionCategories="$pensionCategories" />
@endsection
