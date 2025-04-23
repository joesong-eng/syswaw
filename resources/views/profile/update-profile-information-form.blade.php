<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        <div class="text-gray-800 dark:text-gray-100">
            {{ __('Profile Information') }}
        </div>
    </x-slot>

    <x-slot name="description">
        <div class="text-gray-600 dark:text-gray-400">
            {{ __('Update your account\'s profile information and email address.') }}
        </div>
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden"
                            wire:model.live="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4 text-gray-800 dark:text-gray-100">
            <x-label for="name" value="{{ __('msg.name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" value="{{ Auth::user()->name }}" required autocomplete="name" />
                <div class="mt-4">
                    <x-label for="phone" value="{{ __('msg.phone') }}" />
                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="Auth::user()->phone" required autofocus  autocomplete="phone"/>
                </div>
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4 text-gray-800 dark:text-gray-100">
            <x-label for="email" value="{{ __('msg.email') }}" />
            <div class="text-gray-600 dark:text-gray-300">{{ Auth::user()->email }}</div>
            {{-- <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" value="{{ Auth::user()->email }}"/>
            <x-input-error for="email" class="mt-2" /> --}}

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('msg.save') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('msg.save') }}
        </x-button>
    </x-slot>
</x-form-section>
