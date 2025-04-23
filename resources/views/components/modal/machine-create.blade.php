<div x-cloak class="fixed inset-0 z-50" x-show="addMachineModal">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-1">
        <div class="p-6 relative bg-white dark:bg-gray-800 w-full max-w-md rounded-lg shadow-lg" 
        @click.away="addMachineModal = false">
            <!-- 顯示成功或錯誤訊息 -->
            @if (session('success'))
                <div class="mb-4 p-2 bg-green-100 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-2 bg-red-100 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-2 bg-red-100 text-red-700 rounded-md">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-3 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold mb-4">{{ __('msg.add') }}{{ __('msg.machine') }}</h2>
                <form action="{{ route('machine.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="chipKey" class="block text-sm font-medium text-gray-700">{{ __('msg.chip_token') }}</label>
                        <div x-data="{ chipKey: '' }">
                            <input type="text" name="chipKey" id="chipKey" x-model="chipKey"
                              @paste=" setTimeout(() => {
                                  const pastedText = $event.target.value;
                                  const match = pastedText.match(/https?:\/\/[^\/]+\/chip\/([a-f0-9]+)/i);
                                  if (match && match[1]) { chipKey = match[1];}
                                }, 10)"
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                              required
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                              {{ __('msg.paste_chip_url_or_id') }}
                            </p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                        <input type="text" name="name" id="name" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <x-modal.machine-type />
                    </div>
                    <div class="mb-4">
                        <x-label for="arcade_id" class="text-gray-700 dark:text-gray-300">{{ __('msg.select_arcade') }}:</x-label>
                        <select id="arcade_id" name="arcade_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                            <option value="">{{ __('msg.select_arcade') }}</option>
                            @foreach ($arcades as $arcade)
                                <option value="{{ $arcade->id }}">{{ $arcade->id }}{{ $arcade->name }} ({{ $arcade->type === 'physical' ? __('msg.physical_arcade') : __('msg.virtual_arcade') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- 分成比例選單 -->
                    <div class="mb-4">
                        <label for="revenue_split" class="block text-sm text-gray-600 dark:text-gray-400">分成比例</label>
                        <select id="revenue_split" name="revenue_split" class="w-full mt-1 p-2 border rounded-md">
                                @for ($i = 0.1; $i <= 0.95; $i += 0.05)
                                    <option value="{{ number_format($i, 2) }}" 
                                        @if (number_format($i, 2) == '0.4') selected @endif>
                                        {{ number_format($i, 2) }}
                                    </option>
                                @endfor
                        </select>
                    </div>
                    @if(Auth::user()->hasRole('admin'))
                    <div class="mb-4">
                        <label for="owner_id" class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                        <select name="owner_id" id="owner_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                            <option id="default-option" value="" class="text-gray-500 text-sm font-thin">{{ __('msg.select') }} {{ __('msg.machine_owner') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="owner_id" id="owner_id" value={{Auth::user()->id}} >
                    @endif
                    <div class="flex justify-end">
                        <button type="button" @click="addMachineModal = false" class="mr-2 px-4 py-2 bg-gray-300 rounded-md">{{ __('msg.cancel') }}</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">{{ __('msg.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

