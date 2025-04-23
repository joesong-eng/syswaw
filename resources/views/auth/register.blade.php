<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div>
                <x-label for="name" value="{{ __('msg.name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class=" mt-4">
                <x-label for="phone" value="{{ __('msg.phone') }}" />
                <div class="flex items-center">
                    <!-- Country Code Dropdown -->
                    <select id="country_code" name="country_code" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <!-- 常見國家 -->
                        <option value="+886">+886 (TW)</option>
                        <option value="+86">+86 (China)</option>
                        <option value="+1">+1 (United States)</option>
                        <option value="+81">+81 (Japan)</option>
                        
                        <!-- 東南亞國家 -->
                        <option value="+62">+62 (Indonesia)</option>
                        <option value="+60">+60 (Malaysia)</option>
                        <option value="+65">+65 (Singapore)</option>
                        <option value="+66">+66 (Thailand)</option>
                        <option value="+63">+63 (Philippines)</option>
                        <option value="+84">+84 (Vietnam)</option>
                        <option value="+82">+82 (South Korea)</option>
                        <option value="+95">+95 (Myanmar)</option>
                        <option value="+673">+673 (Brunei)</option>
                        <option value="+855">+855 (Cambodia)</option>
                        <option value="+856">+856 (Laos)</option>
                        
                        <option value="+91">+91 (India)</option>
                        <option value="+61">+61 (Australia)</option>
                        <option value="+64">+64 (New Zealand)</option>
                        <!-- 歐洲主要國家 -->
                        <option value="+44">+44 (United Kingdom)</option>
                        <option value="+33">+33 (France)</option>
                        <option value="+49">+49 (Germany)</option>
                        <option value="+34">+34 (Spain)</option>
                        <option value="+39">+39 (Italy)</option>
                        <option value="+31">+31 (Netherlands)</option>
                        <option value="+46">+46 (Sweden)</option>
                        <option value="+45">+45 (Denmark)</option>
                        <option value="+41">+41 (Switzerland)</option>
                        <option value="+47">+47 (Norway)</option>


                    </select>
                    <x-input id="phone" class="block mt-1 w-3/4 ml-2" type="text" name="phone" :value="old('phone')" required autocomplete="phone" />
                </div>
            </div>
            
            <div class="mt-4">
                <x-label for="email" value="{{ __('msg.email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('msg.password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('msg.ConfirmPassword') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            {{-- <div class="mt-4">
                <x-label for="be_role" value="{{ __('msg.role') }}" />
                <select name="be_role" id="">
                    <option value="user">User</option>
                </select>
            </div> --}}
            <div class="mt-4">
                <x-label for="invitation_code" :value="__('邀請碼 (可選)')" />
                <x-input id="invitation_code" class="block mt-1 w-full" type="text" name="invitation_code" />
                    <x-input-error for="invitation_code" class="mt-2" />

            </div>
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />
                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('msg.alreadyRegistered') }}
                </a>

                <x-button class="ms-4 text-black bg-blue-500 " style="background-color: cornflowerblue">
                    {{ __('msg.register') }}
                </x-button>
            </div>
            
        </form>
    </x-authentication-card>
</x-guest-layout>
