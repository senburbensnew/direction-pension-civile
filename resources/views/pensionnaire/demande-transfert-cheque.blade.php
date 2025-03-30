@extends('layouts.main')

@section('content')
    <x-check-transfer-requests :pensionCategories="$pensionCategories" />
@endsection
