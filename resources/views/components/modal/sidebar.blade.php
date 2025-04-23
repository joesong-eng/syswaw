<div id="sidebar" 
    class="sidebar ease-in-out transition-transform duration-300
        bg-gray-100 dark:bg-gray-800 shadow-lg overflow-auto w-full
        fixed top-12 left-0 z-10 
        sm:pt-14 sm:w-64 md:w-72 
        h-12 sm:h-full sm:static sm:block
        max-h-[calc(100vh-10px)]
        px-2 mx-1" >
    <!-- 移動端：固定高度橫向滑動；桌面端：垂直佈局 -->
    <div id="sidebarkid" 
         class="sidebarkid flex sm:flex-col h-12 sm:h-full overflow-x-auto sm:overflow-y-auto max-h-[calc(100vh-48px)] sm:max-h-[calc(100vh-56px)] whitespace-nowrap sm:whitespace-normal">
        
        <!-- Admin 專屬功能 -->
        @if(Auth::user()->hasRole('admin'))
            <div class="hidden sm:block text-center p-2 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-600">
                <div class="text-lg font-thin text-gray-900 dark:text-gray-100 break-words whitespace-normal max-w-48 m-auto">
                    {{ __('msg.welcome', ['name' => Auth::user()->name]) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 break-words whitespace-normal max-w-48 m-auto">
                    {{ __('msg.profile_info') }}
                </div>
            </div>
            <hr class="hidden sm:block">
            <!-- 導航項 -->
            <x-modal.sidebar-link route="admin.arcadeKey" icon="key" 
                title="{{ __('msg.arcade_key_management') }}" 
                description="{{ __('msg.arcade_key_management_desc') }}" />
            <x-modal.sidebar-link route="admin.arcades" icon="stores" 
                title="{{ __('msg.arcade_management') }}" 
                description="{{ __('msg.arcade_management_desc') }}" />
            <x-modal.sidebar-link route="admin.users" icon="user" 
                title="{{ __('msg.user_management') }}" 
                description="{{ __('msg.user_management_desc') }}" />
            <x-modal.sidebar-link route="roles.index" icon="role" 
                title="{{ __('msg.role_management') }}" 
                description="{{ __('msg.role_management_desc') }}" />
            <x-modal.sidebar-link route="chips.index" icon="machine-key" 
                title="{{ __('msg.chip_token') }}" 
                description="{{ __('msg.machine_key_management_desc') }}" />
            <x-modal.sidebar-link route="admin.machines" icon="machines" 
                title="{{ __('msg.machine_management') }}" 
                description="{{ __('msg.machine_management_desc') }}" />
            <x-modal.sidebar-link route="admin.tcp-server" icon="transactions" 
                title="{{ __('msg.data_stream') }}" 
                description="{{ __('msg.data_stream_desc') }}" />
        @endif

        <!-- arcade-owner -->
        @if(Auth::user()->hasRole('arcade-owner'))
            @if(Auth::user()->arcade)
                <div class="hidden sm:block shadow-lg p-2">
                    <a href="{{ route('arcades.index')}}" class="text-sm text-center hover:text-blue-600">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('msg.arcade_info') }}</h3>
                        <div class="space-y-2 overflow-auto shadow-lg">
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.name') }}</span>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->name }}</p>
                            </div>
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.address') }}</span>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->address }}</p>
                            </div>
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.phone') }}</span>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->phone }}</p>
                            </div>
                            <div class="flex items-center justify-start pb-2">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.owner') }}</span>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->owner->name }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            <hr class="hidden sm:block">
            <x-modal.sidebar-link route="profile.edit" icon="user" 
                title="{{ __('msg.ProfileInfo') }}" 
                description="{{ __('msg.personal_info_desc') }}" />
            <x-modal.sidebar-link route="staff" icon="manager" 
                title="{{ __('msg.staff_management') }}" 
                description="{{ __('msg.staff_management_desc') }}" />
            <x-modal.sidebar-link route="chips.index" icon="machine-key" 
                title="{{ __('msg.chip_token') }}" 
                description="{{ __('msg.machine_key_management_desc') }}" />
            <x-modal.sidebar-link route="machine.index" icon="machines" 
                title="{{ __('msg.machine_management') }}" 
                description="{{ __('msg.machine_management_desc') }}" />
            <x-modal.sidebar-link route="machine.index" icon="manager" 
                title="{{ __('msg.add_machine_owner') }}" 
                description="{{ __('msg.add_machine_owner_desc') }}" />
            <x-modal.sidebar-link route="transactions.index" icon="transactions" 
                title="{{ __('msg.transaction_management') }}" 
                description="{{ __('msg.transaction_management_desc') }}" />
        @endif

        <!-- arcade-staff -->
        @if(Auth::user()->hasRole('arcade-staff'))
            @if((Auth::user()->parent)->arcade)
            <div class="hidden sm:block shadow-lg p-2">
                <a href="{{ route('arcades.index')}}" class="text-sm text-center hover:text-blue-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('msg.arcade_info') }}</h3>
                    <div class="space-y-2 overflow-auto shadow-lg">
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.arcade') }}</span>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->name }}</p>
                        </div>
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.address') }}</span>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->address }}</p>
                        </div>
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.phone') }}</span>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->phone }}</p>
                        </div>
                        <div class="flex items-center justify-start pb-2">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2">{{ __('msg.owner') }}</span>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->parent->arcade->owner->name }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endif
            <hr class="hidden sm:block">
            <x-modal.sidebar-link route="profile.edit" icon="user"
                title="{{ __('msg.personal_info') }}"
                description="{{ __('msg.personal_info_desc') }}" />
        
            @if(in_array('chips.index', Auth::user()->sidebar_permissions ?? []))
                <x-modal.sidebar-link route="chips.index" icon="machine-key"
                    title="{{ __('msg.chip_token') }}"
                    description="{{ __('msg.machine_key_management_desc') }}" />
            @endif
        
            @if(in_array('machine.index', Auth::user()->sidebar_permissions ?? []))
                <x-modal.sidebar-link route="machine.index" icon="machines"
                    title="{{ __('msg.machine_management') }}"
                    description="{{ __('msg.machine_management_desc') }}" />
            @endif
        
            @if(in_array('add_machine_owner', Auth::user()->sidebar_permissions ?? []))
                <x-modal.sidebar-link route="machine.index" icon="manager"
                    title="{{ __('msg.add_machine_owner') }}"
                    description="{{ __('msg.add_machine_owner_desc') }}" />
            @endif
        
            @if(in_array('transactions.index', Auth::user()->sidebar_permissions ?? []))
                <x-modal.sidebar-link route="transactions.index" icon="transactions"
                    title="{{ __('msg.transaction_management') }}"
                    description="{{ __('msg.transaction_management_desc') }}" />
            @endif
        @endif

        <!-- machine-owner -->
        @if(Auth::user()->hasRole('machine-owner'))
            <div class="hidden sm:block bg-white dark:bg-gray-800 p-4 shadow-lg flex-shrink-0 w-48 sm:w-auto">
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('msg.machine_info') }}</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-gray-600 dark:text-gray-300">{{ __('msg.machine_owner') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
            <x-modal.sidebar-link route="profile.edit" icon="user" 
                title="{{ __('msg.ProfileInfo') }}" 
                description="{{ __('msg.personal_info_desc') }}" />
            <x-modal.sidebar-link route="machine.manager" icon="manager" 
                title="{{ __('msg.staff_management') }}" 
                description="{{ __('msg.staff_management_desc') }}" />
            <x-modal.sidebar-link route="machine.machine_key" icon="machine-key" 
                title="{{ __('msg.chip_token') }}" 
                description="{{ __('msg.machine_key_management_desc') }}" />
            <x-modal.sidebar-link route="machine.machines" icon="machines" 
                title="{{ __('msg.machine_management') }}" 
                description="{{ __('msg.machine_management_desc') }}" />
            <x-modal.sidebar-link route="machine.visualStore" icon="stores" 
                title="{{ __('msg.vsstore') }}" 
                description="{{ __('msg.vsarcade_desc') }}" />
            <x-modal.sidebar-link route="transactions.index" icon="transactions" 
                title="{{ __('msg.transaction_management') }}" 
                description="{{ __('msg.transaction_management_desc') }}" />
        @endif

        <!-- machine-manager -->
        @if(Auth::user()->hasRole('machine-manager'))
            <div class="hidden sm:block bg-white dark:bg-gray-800 p-4 shadow-lg">
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('msg.machine_info') }}</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-gray-600 dark:text-gray-300">{{ __('msg.machine_owner') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ Auth::user()->parent ? Auth::user()->parent->name : '無店主資料' }}
                        </p>
                    </div>
                </div>
            </div>
            <x-modal.sidebar-link route="machine.machines" icon="machines" 
                title="{{ __('msg.machine') }}" 
                description="{{ __('msg.machine.dashboard') }}" />
            <x-modal.sidebar-link route="profile.edit" icon="user" 
                title="{{ __('msg.ProfileInfo') }}" 
                description="{{ __('msg.ProfileDiscription') }}" />
            <x-modal.sidebar-link route="machine.visualStore" icon="store" 
                title="{{ __('msg.vsstore') }}" 
                description="{{ __('msg.vsarcade_desc') }}" />
            <x-modal.sidebar-link route="transactions.index" icon="transactions" 
                title="{{ __('msg.transaction_management') }}" 
                description="{{ __('msg.transaction_management_desc') }}" />
        @endif
    </div>
</div>

