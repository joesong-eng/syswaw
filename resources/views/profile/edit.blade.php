@extends('layouts.app')
@section('content')
    @if (session('status'))
        <div class="text-green-600 mb-4">
            {{ session('status') }}
        </div>
    @endif
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1 flex justify-between">
                <div class="flex justify-between items-center  sm:block sm:m-auto sm:ml-0 mb-2 px-6 pt-6 sm:pt-0">
                    <div class="flex items-center p-2 text-lg font-medium text-gray-700 dark:text-gray-300">
                        {{ __('msg.ProfileInfo') }}
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 sm:block">
                        {{ __('msg.ProfileDiscription') }}
                    </p>
                </div>
                <div class="px-4 sm:px-0">
                </div>


            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                        <div class="">
                            <!-- Email -->
                            <div class="pb-4">
                                <label class="block font-medium text-sm text-gray-700" for="email">
                                    {{__('msg.email')}}
                                </label>
                                <div class="text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                                <input 
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                    id="email" type="hidden" name="email" value="{{ old('email', auth()->user()->email) }}"
                                    autocomplete="username">
                                @error('email')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Name -->
                            <div class="pb-4">
                                <label class="block font-medium text-sm text-gray-700" for="name">
                                    {{__('msg.name')}}
                                </label>
                                <input 
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                    id="name" 
                                    type="text" 
                                    name="name"
                                    value="{{ old('name', auth()->user()->name) }}"
                                    required
                                    autocomplete="name">
                                @error('name')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
    

    
                            <!-- Password -->
                            <div class="pb-4">
                                <label class="block font-medium text-sm text-gray-700" for="password">
                                    {{__('msg.update')}}{{__('msg.password')}} ({{ __('msg.Optional') }})
                                </label>
                                <input 
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                    id="password" 
                                    type="password" 
                                    name="password"
                                    autocomplete="new-password">
                                @error('password')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
    
                            <!-- Password Confirmation -->
                            <div class="pb-4">
                                <label class="block font-medium text-sm text-gray-700" for="password_confirmation">
                                    {{__('msg.confirm')}} {{__('msg.password')}}
                                </label>
                                <input 
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                    id="password_confirmation" 
                                    type="password" 
                                    name="password_confirmation"
                                    autocomplete="new-password">
                            </div>
                        </div>
                    </div>
    
                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                        <button type="submit" class="
                        inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring focus:ring-indigo-300 disabled:opacity-25 transition
                        ">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@php
    $title = __('msg.ProfileInfo');
@endphp