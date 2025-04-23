@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="h-[calc(100vh-110px)] sm:h-[calc(100vh-48px)] overflow-scroll">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 text-gray-800 dark:text-gray-100">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <div>
            {{__('msg.role')}}:{{__('msg.'.Auth::user()->getRoleNames()->first())}}
            </div>            
                @livewire('profile.update-profile-information-form')
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>
                <x-section-border />
            @endif

            {{-- @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif --}}

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
    <script>

        console.log(window.Livewire.components.componentsById);
    </script>
@endsection
@php
    $title = '';
@endphp
