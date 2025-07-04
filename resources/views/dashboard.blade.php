@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <x-welcome />
    </div>
</div>
@endsection
@php
    $title =  __('msg.dashboard.01') ;
@endphp
