@extends('layouts.app')
@section('content')


{{-- @section('content') --}}
    <div class="container">
        <h1>操作日志</h1>

        @foreach($logs as $log)
            <div class="mb-2">
                <strong>{{ $log->created_at }}</strong>: {{ $log->action }}
            </div>
        @endforeach
    </div>
{{-- @endsection --}}
@endsection
@php
    $title = '';
@endphp
