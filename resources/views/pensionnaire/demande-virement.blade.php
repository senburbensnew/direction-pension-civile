@extends('layouts.main')

@section('content')
    <x-bank-transfer-requests :pensionCategories="$pensionCategories" :pensionTypes="$pensionTypes" :civilStatuses="$civilStatuses" :genders="$genders" />
@endsection
