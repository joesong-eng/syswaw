        <!-- 新增機器金鑰模態框 -->
        <div x-cloak x-show="addOwnerModal" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
                    @click.away="addOwnerModal = false">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">新增機器金鑰</h2>
                    <form class="justify-between" action="{{ route('machines.addMachinekey') }}" method="POST">
                        @csrf
                        <!-- 選擇商鋪< -->
                        <div class="w-full">
                            @if (Auth::user()->hasRole('store-owner'))
                                <input type="hidden" name="store_id" value="{{ Auth::user()->store->id ?? '' }}">
                            @elseif(Auth::user()->hasRole('admin'))
                                <label for="store_id" class="block text-sm font-medium text-gray-700">選擇商鋪</label>
                                <select name="store_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}"
                                            {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->id }}. {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="store_id"
                                    value="{{ Auth::user()->parent->store->id ?? '' }}">
                            @endif
                        </div>
                        <!--選擇 / 新增 機主帳戶 -->
                        <div class="flex justify-between w-full pt-3">
                            <div>
                                <label for="owner_id"
                                    class="block text-sm font-medium text-gray-700">
                                    選擇 / <a
                                        class="px-0 py-2 text-blue-500 rounded-md hover:text-blue-100 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                                        onclick="toggleModal('addOwnerModal')">新增
                                    </a> 機主帳戶
                                </label>
                                <select name="owner_id" id="owner_id"
                                    class="w-fullrounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm ">
                                    <!-- 空選項 -->
                                    <option value="" class="text-sm"
                                        {{ old('owner_id', session('selected_owner_id', '')) == '' ? 'selected' : '' }}>
                                        暫時留空
                                    </option>

                                    @foreach ($currentUser as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->roles->first()->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- 生成數量 -->
                            <div class=" items-center justify-center">
                                <label for="count"
                                    class="block text-sm font-medium text-gray-700">生成數量</label>
                                <input type="number" name="count" id="count" value="1" min="1"
                                    max="50"
                                    class="w-full pb-2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    required>
                            </div>
                        </div>
                        <div class="flex justify-end w-full pt-3">
                            <!-- 提交按鈕 -->
                            <div class=" items-end justify-end">
                                <div class="px-3">
                                    <button type="submit"
                                        class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-md font-thin">
                                        新增
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
