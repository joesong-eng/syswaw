@extends('layouts.app')
@section('content')
    <x-modal.arcade-create :arcadeKey ='$arcadeKey'/>
@endsection
@php
    $title = 'Bind Arcade';
@endphp