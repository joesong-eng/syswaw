@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        月結報表詳情: {{ $report->report_number }}
    </h2>
@endsection

@section('content')
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Admin 視角 -->
            @if ($view_type === 'admin')
                @include('monthly_reports.partials.admin-view')
            @endif

            <!-- Arcade Owner 視角 -->
            @if ($view_type === 'arcade_owner')
                @include('monthly_reports.partials.arcade-owner-view')
            @endif

            <!-- Machine Owner 視角 -->
            @if ($view_type === 'machine_owner')
                @include('monthly_reports.partials.machine-owner-view')
            @endif

        </div>
    </div>
@endsection
