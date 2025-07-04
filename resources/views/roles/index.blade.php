@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.role_management') }}
    </h2>
@endsection
@section('content')
    {{-- @if (session('success'))
        <div id="success-message"
            class="absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in">
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div id="error-message" class="mb-4 text-red-600">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}
    <div class="flex justify-center bg-gray-100" x-data="{ createRoleModal: false, editModal: false, selectedRole: {} }">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2 px-6 pt-2">
                <x-button @click="createRoleModal = true"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.role') }}
                </x-button>
            </div>

            <div class="container mx-auto pb-3">
                <div class="bg-white rounded-lg shadow-lg px-2 ">
                    <!-- Header Row -->
                    <div
                        class="flex items-center text-center border-b border-gray-200 text-sm font-medium text-gray-700  shadow-md">
                        <div class="w-[10%]">ID</div>
                        <div class="w-[40%] border-l border-gray-200 ">{{ __('msg.name') }}</div>
                        <div class="w-[20%] border-l border-gray-200">Level</div>
                        <div class="w-[30%] border-l border-gray-200 ">{{ __('msg.actions') }}</div>
                    </div>

                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                        @foreach ($roles as $role)
                            <div
                                class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 py-1">
                                <div class="w-[10%] break-words text-center ">{{ $role->id }}</div>
                                <div class="w-[40%] break-words border-l px-2">{{ $role->name }}</div>
                                <div class="w-[20%] break-words border-l px-2 text-center">{{ $role->level }}</div>
                                <div class="w-[30%] break-words hidden">{{ $role->guard_name }}</div>
                                <div class=" w-[30%] items-center text-center m-auto border-1 border-l space-x-1">
                                    <button
                                        class="inline-block px-1 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                                        @click="editModal = true; selectedRole = { id: {{ $role->id }}, name: '{{ $role->name }}',level: '{{ $role->level }}', slug: '{{ $role->slug }}' }">
                                        <x-svg-icons name="edit" classes="h-6 w-6" />
                                    </button>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-1 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                                            onclick="return confirm('{{ __('msg.confirm_delete') }}');">
                                            <x-svg-icons name="delete" classes="h-6 w-6" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

        </div>
        <!-- Modals -->
        <x-modal.role_create-modal x-show="createRoleModal" x-cloak />
        <x-modal.role_edit-modal x-show="editModal" x-cloak />
    </div>
@endsection

@php
    $title = __('msg.role_management');
@endphp
