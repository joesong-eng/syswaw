@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Machine Owner Dashboard') }}
        </h2>
    </x-slot>
    {{-- @if (!auth()->user()->hasVerifiedEmail())
        <div class="alert alert-warning">
            <strong>提醒：</strong> {{ __('msg.emailVerified') }}
            <a href="{{ route('verification.notice') }}">點擊這裡重新發送驗證信</a>。
        </div>
    @endif --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-welcome />
            </div>
        </div>
    </div>
@endsection
@php
    $title = 'dashboard.machine';
@endphp
