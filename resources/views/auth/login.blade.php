<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="text-red-500">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                <x-label for="email" value="{{ __('msg.email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            </div>
            {{-- <div class="mt-4">
                <x-label for="phone" value="{{ __('msg.phone') }}" />
                <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autofocus  autocomplete="phone"/>
            </div> --}}
            <div class="mt-4">
                <x-label for="password" value="{{ __('msg.password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('msg.rememberMe') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('msg.ForgotYourPassword') }}
                    </a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-4 underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('msg.register') }}</a>
                @endif

                <button class="ms-4">
                    <div type="submit" class="text-gray-600 hover:text-gray-800 px-3 py-2" style="hover">
                        {{ __('msg.login') }}
                    </div>
                </button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
