{{-- @extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 pt-2">
        <div class="w-full max-w-2xl bg-white shadow-lg rounded-lg px-6 py-8 mx-4">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Create Role</h1>

            @if ($errors->any())
                <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300 transition">
                </div>

                <div class="mb-4">
                    <label for="level" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" id="level" name="level" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300 transition">
                </div>

                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" id="slug" name="slug" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300 transition">
                </div>

                <div class="text-right">
                    <button type="submit"
                        class="inline-block px-6 py-2 font-semibold rounded-md shadow text-white bg-blue-500 hover:bg-blue-700 transition">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@php
    $title = 'Create Role';
@endphp
 --}}
