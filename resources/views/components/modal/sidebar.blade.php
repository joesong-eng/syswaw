{{-- 檔案路徑：您的側邊欄 Blade 檔案 --}}

{{--
  說明：
  這是一個完整的側邊欄檔案，整合了所有角色邏輯。
  - 使用 <x-sidebar.info-card> 來包裹所有資訊區塊，自動處理亮/暗模式樣式。
  - 資訊卡片內部使用 flex 和 justify-between 讓排版更整齊。
  - 所有 <x-modal.sidebar-link> 元件都使用重構後的版本，樣式統一且支援亮/暗模式。
  - 使用 <hr> 標籤在不同區塊間建立視覺分隔線。
--}}

@if (Auth::user()->hasRole('admin'))
    <x-sidebar.info-card>
        <div class="text-center">
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 break-words">
                {{ __('msg.welcome', ['name' => Auth::user()->name]) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('msg.profile_info') }}
            </div>
        </div>
    </x-sidebar.info-card>

    <x-modal.sidebar-link route="admin.arcadeKey.index" icon="key" title="{{ __('msg.arcade_key_management') }}"
        description="{{ __('msg.arcade_key_management_desc') }}" />
    <x-modal.sidebar-link route="admin.arcades.index" icon="stores" title="{{ __('msg.arcade_management') }}"
        description="{{ __('msg.arcade_management_desc') }}" />
    <x-modal.sidebar-link route="admin.users.index" icon="user" title="{{ __('msg.user_management') }}"
        description="{{ __('msg.user_management_desc') }}" />
    <x-modal.sidebar-link route="admin.roles.index" icon="role" title="{{ __('msg.role_management') }}"
        description="{{ __('msg.role_management_desc') }}" />
    <x-modal.sidebar-link route="admin.machines.index" icon="machines" title="{{ __('msg.machine_management') }}"
        description="{{ __('msg.machine_management_desc') }}" />
    <x-modal.sidebar-link route="admin.machine_auth_keys.index" icon="key"
        title="{{ __('msg.machine_auth_keys') }}" description="{{ __('msg.machine_auth_key_management_desc') }}" />
    <x-modal.sidebar-link route="admin.tcp-server.index" icon="Floppy" title="{{ __('msg.data_record') }}"
        description="{{ __('msg.data_record_desc') }}" />
    <x-modal.sidebar-link route="admin.tcp-server.streamData" icon="transactions" title="{{ __('msg.data_stream') }}"
        description="{{ __('msg.data_stream_desc') }}" />
    <x-modal.sidebar-link route="reports.index" icon="Floppy" title="{{ __('报表系统') }}" description="生成和查看机器数据报表" />
    <x-modal.sidebar-link route="admin.monthly-reports.index" title="月結總報表" icon="Floppy" description="月結總報表" />
@elseif (Auth::user()->hasRole('arcade-owner'))
    @php $ownerArcade = Auth::user()->arcades()->first(); @endphp
    @if ($ownerArcade)
        <x-sidebar.info-card>
            <h3 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                {{ __('msg.arcade_info') }}</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.name') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $ownerArcade->name }}</span>
                </div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.address') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $ownerArcade->address }}</span>
                </div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.phone') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 text-right">{{ $ownerArcade->phone }}</span></div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.business_hours') }}:</span>
                    <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $ownerArcade->business_hours ?? __('msg.not_set') }}</span>
                </div>
                <div class="flex justify-between items-center"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.authorization_code') }}:</span>
                    <code
                        class="text-xs bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 px-1 py-0.5 rounded">{{ $ownerArcade->authorization_code ?? __('msg.not_set') }}</code>
                </div>
            </div>
        </x-sidebar.info-card>
    @else
        <x-sidebar.info-card class="text-center text-red-600 dark:text-red-400 font-semibold">
            {{ __('msg.no_arcade_info') }}
        </x-sidebar.info-card>
    @endif

    <hr class="border-gray-200 dark:border-gray-700 my-2">
    <x-modal.sidebar-link route="profile.edit" icon="user" title="{{ __('msg.profile') }}"
        description="{{ __('msg.personal_info_desc') }}" />
    <x-modal.sidebar-link route="arcade.staff.index" icon="manager" title="{{ __('msg.staff_management') }}"
        description="{{ __('msg.staff_management_desc') }}" />
    <x-modal.sidebar-link route="arcade.auth_keys.index" icon="key" title="{{ __('msg.machine_auth_keys') }}"
        description="{{ __('msg.machine_auth_key_management_desc') }}" />
    <x-modal.sidebar-link route="arcade.machines.index" icon="machines" title="{{ __('msg.machine_management') }}"
        description="{{ __('msg.machine_management_desc') }}" />
    <x-modal.sidebar-link route="arcade.transactions.index" icon="transactions"
        title="{{ __('msg.transaction_management') }}" description="{{ __('msg.transaction_management_desc') }}" />
    <x-modal.sidebar-link route="arcade.monthly-reports.index" title="我的月結報表" icon="transactions" description="月結總報表" />
@elseif (Auth::user()->hasRole('arcade-staff'))
    @php $staffOwnerArcade = Auth::user()->parent?->arcades()->first(); @endphp
    @if ($staffOwnerArcade)
        <x-sidebar.info-card>
            <h3 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                {{ __('msg.arcade_info') }}</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.arcade') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $staffOwnerArcade->name }}</span>
                </div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.address') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $staffOwnerArcade->address }}</span>
                </div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.phone') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 text-right">{{ $staffOwnerArcade->phone }}</span></div>
                <div class="flex justify-between"><span
                        class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.owner') }}:</span> <span
                        class="text-gray-800 dark:text-gray-200 truncate text-right">{{ $staffOwnerArcade->owner->name }}</span>
                </div>
            </div>
        </x-sidebar.info-card>
    @endif

    <hr class="border-gray-200 dark:border-gray-700 my-2">
    <x-modal.sidebar-link route="profile.edit" icon="user" title="{{ __('msg.personal_info') }}"
        description="{{ __('msg.personal_info_desc') }}" />
    @if (in_array('auth_keys.index', Auth::user()->sidebar_permissions ?? []))
        <x-modal.sidebar-link route="arcade.auth_keys.index" icon="key"
            title="{{ __('msg.machine_auth_keys') }}"
            description="{{ __('msg.machine_auth_key_management_desc') }}" />
    @endif
    @if (in_array('machine.index', Auth::user()->sidebar_permissions ?? []))
        <x-modal.sidebar-link route="arcade.machines.index" icon="machines"
            title="{{ __('msg.machine_management') }}" description="{{ __('msg.machine_management_desc') }}" />
    @endif
    @if (in_array('transactions.index', Auth::user()->sidebar_permissions ?? []))
        <x-modal.sidebar-link route="arcade.transactions.index" icon="transactions"
            title="{{ __('msg.transaction_management') }}"
            description="{{ __('msg.transaction_management_desc') }}" />
    @endif
@elseif (Auth::user()->hasRole('machine-owner'))
    <x-sidebar.info-card>
        <h3 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('msg.machine_info') }}</h3>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.machine_owner') }}:</span>
                <span class="text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</span>
            </div>
        </div>
    </x-sidebar.info-card>

    <hr class="border-gray-200 dark:border-gray-700 my-2">
    <x-modal.sidebar-link route="profile.edit" icon="user" title="{{ __('msg.profile') }}"
        description="{{ __('msg.personal_info_desc') }}" />
    <x-modal.sidebar-link route="machine.staff.index" icon="manager" title="{{ __('msg.staff_management') }}"
        description="{{ __('msg.staff_management_desc') }}" />
    <x-modal.sidebar-link route="machine.auth_keys.index" icon="machine-key" title="{{ __('msg.chip_token') }}"
        description="{{ __('msg.machine_key_management_desc') }}" />
    <x-modal.sidebar-link route="machine.machines.index" icon="machines" title="{{ __('msg.machine_management') }}"
        description="{{ __('msg.machine_management_desc') }}" />
    <x-modal.sidebar-link route="machine.transactions.index" icon="transactions"
        title="{{ __('msg.transaction_management') }}" description="{{ __('msg.transaction_management_desc') }}" />
    <x-modal.sidebar-link route="machine.monthly-reports.index" title="我的月結報表" icon="transactions"
        description="月結總報表" />
@elseif (Auth::user()->hasRole('machine-staff'))
    <x-sidebar.info-card>
        <h3 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('msg.machine_info') }}</h3>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="font-bold text-gray-600 dark:text-gray-400 pr-2">{{ __('msg.machine_owner') }}:</span>
                <span class="text-gray-800 dark:text-gray-200">{{ Auth::user()->parent->name ?? __('無店主資料') }}</span>
            </div>
        </div>
    </x-sidebar.info-card>

    <hr class="border-gray-200 dark:border-gray-700 my-2">
    <x-modal.sidebar-link route="profile.edit" icon="user" title="{{ __('msg.profile') }}"
        description="{{ __('msg.ProfileDiscription') }}" />
    @if (in_array('machine.machines.index', Auth::user()->sidebar_permissions ?? []))
        <x-modal.sidebar-link route="machine.machines.index" icon="machines"
            title="{{ __('msg.machine_management') }}" description="{{ __('msg.machine_management_desc') }}" />
    @endif
    @if (in_array('machine.transactions.index', Auth::user()->sidebar_permissions ?? []))
        <x-modal.sidebar-link route="machine.transactions.index" icon="transactions"
            title="{{ __('msg.transaction_management') }}"
            description="{{ __('msg.transaction_management_desc') }}" />
    @endif
@endif
