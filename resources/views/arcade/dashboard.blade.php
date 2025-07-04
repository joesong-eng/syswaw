@extends('layouts.app')
@section('content')
    @if ($errors->any())
        <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <x-welcome />
        </div>
    </div>
@endsection
@php
    $title = __('msg.arcade');
@endphp
