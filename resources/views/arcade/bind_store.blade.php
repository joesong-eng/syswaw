@extends('layouts.guest')
@section('content')
@if($storeKey)
    <x-create-store :storeKey="$storeKey"/>
@endif
@endsection
@php
    $title = 'Bind';
@endphp