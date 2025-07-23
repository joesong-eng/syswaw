@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('msg.Profile') }}
    </h2>
@endsection
@section('content')
    <div class="h-[calc(100vh-110px)] sm:h-[calc(100vh-48px)] overflow-scroll">
        <span class="font-semibold text-indigo-600 dark:text-indigo-400 text-xs text-right">
            {{ Auth::user()->getRoleNames()->isNotEmpty() ? __('msg.' . Str::slug(Auth::user()->getRoleNames()->first(), '_'), [], Auth::user()->getRoleNames()->first()) : __('msg.no_role_assigned') }}
        </span>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 text-gray-800 dark:text-gray-100">

            {{-- <div class="mb-6 p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('msg.current_role') }}: --}}
            {{-- </p>
            </div> --}}

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
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
        document.addEventListener('livewire:init', () => {
            console.log(window.Livewire.components.componentsById);
        });
    </script>
@endsection
@php
    $title = '';
@endphp
