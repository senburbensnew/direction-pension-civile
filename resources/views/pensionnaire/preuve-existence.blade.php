@extends('layouts.main')

@section('content')
    <x-existence-proof :pensionCategories="$pensionCategories" :genders="$genders" :civilStatuses="$civilStatuses" />
@endsection
