@php
    $ownerId = auth()->check()
        ? (auth()->user()->isArcadeStaff() && !is_null(auth()->user()->parent)
            ? auth()->user()->parent->id
            : auth()->id())
        : null;

    $arcadesData = $arcades
        ->mapWithKeys(function ($arcade) {
            return [$arcade->id => ['name' => $arcade->name, 'currency' => $arcade->currency ?? 'TWD']];
        })
        ->all();

    $machineTypeNames = collect(config('machines.types', []))
        ->mapWithKeys(function ($displayNameKey, $key) {
            return [$key => __($displayNameKey)];
        })
        ->all();
@endphp
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('machineManagement', () => ({
            showCreateMachineModal: false,
            showEditMachineModal: false,
            isLoadingCreateKey: false,

            // 建立用的表單
            createForm: {
                name: '',
                arcade_id: '',
                arcade_currency: 'TWD',
                chip_hardware_id: '',
                auth_key: '',
                machine_category: '', // New
                machine_type: '', // Now for appearance
                payout_type: 'none',
                payout_type_selection: [], // New
                optional_modules: [], // New
                selected_modules: [], // New
                credit_value: '0',
                balls_per_credit: '0',
                points_per_credit_action: '0',
                payout_unit_value: '0',
                revenue_split: 45,
                owner_id: @json($ownerId),
                bill_acceptor_enabled: false,
                bill_currency: 'TWD',
                accepted_denominations: [],
                all_denominations_selected: true,
                share_pct: '',
            },

            // 編輯用的表單
            editForm: {
                id: null,
                name: '',
                machine_category: '', // New
                machine_type: '', // Now for appearance
                payout_type_selection: [], // New
                optional_modules: [], // New
                selected_modules: [], // New
                arcade_id: '',
                arcade_currency: 'TWD',
                auth_key: '',
                chip_hardware_id: '',
                machine_auth_key: null,
                credit_value: '0',
                balls_per_credit: '0',
                points_per_credit_action: '0',
                payout_type: 'none',
                payout_unit_value: null,
                revenue_split: null,
                owner_id: null,
                owner: null,
                bill_acceptor_enabled: false,
                bill_currency: 'TWD',
                accepted_denominations: [],
                all_denominations_selected: false,
                share_pct: '',
            },

            // 可選面額，初始化為空數組
            available_denominations_for_selected_currency: [],

            // 從後端傳遞的數據
            arcadesData: @json($arcadesData),
            machineTemplates: @json(config('machines.templates', [])), // New
            machineTypeNames: @json($machineTypeNames),
            payoutTypeNames: {
                'none': "{{ __('msg.select') }}",
                'ball': "{{ __('msg.payout_type_pachinko') }}",
                'points': "{{ __('msg.payout_type_points') }}",
                'tickets': "{{ __('msg.payout_type_tickets') }}",
                'coins': "{{ __('msg.payout_type_coins') }}",
                'prize': "{{ __('msg.payout_type_prize_claw') }}"
            },
            mappingsConfig: @json(config('bill_mappings', [])),

            // 輔助函數
            getMachineTypeName(typeKey) {
                return this.machineTypeNames[typeKey] || typeKey || 'N/A';
            },

            getPayoutTypeName(typeKey) {
                return this.payoutTypeNames[typeKey] || typeKey || 'N/A';
            },

            formatPayoutUnitValue(value) {
                if (value === null || value === undefined || value === '') return null;
                let num = parseFloat(value);
                if (isNaN(num)) return String(value);
                return Number.isInteger(num) ? String(num) : num.toFixed(2);
            },

            formatNumberWithCommas(number) {
                if (number === null || number === undefined || isNaN(parseFloat(String(number)
                        .replace(/,/g, '')))) {
                    return number;
                }
                return parseFloat(String(number).replace(/,/g, '')).toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            },

            // 模態框控制
            openCreateModal() {
                this.resetCreateForm();
                this.showCreateMachineModal = true;
            },

            closeCreateModal() {
                this.showCreateMachineModal = false;
            },

            openEditModal(machine) {
                this.setEditForm(machine);
                this.showEditMachineModal = true;
            },

            closeEditModal() {
                this.showEditMachineModal = false;
            },

            updateCreateFormBasedOnCategory() {
                const category = this.createForm.machine_category;
                const template = this.machineTemplates[category];

                if (!template) {
                    this.createForm.payout_type_selection = [];
                    this.createForm.optional_modules = [];
                    return;
                }

                this.createForm.payout_type_selection = template.follow_up?.payout_type_selection
                    ?.map(item => ({
                        value: Object.keys(item)[0],
                        text: Object.values(item)[0]
                    })) || [];

                this.createForm.optional_modules = template.follow_up?.optional_modules?.map(item =>
                    ({
                        value: Object.keys(item)[0],
                        text: Object.values(item)[0]
                    })) || [];

                // Reset selections
                this.createForm.payout_type = '';
                this.createForm.selected_modules = [];
            },

            updateEditFormBasedOnCategory() {
                const category = this.editForm.machine_category;
                const template = this.machineTemplates[category];

                if (!template) {
                    this.editForm.payout_type_selection = [];
                    this.editForm.optional_modules = [];
                    return;
                }

                this.editForm.payout_type_selection = template.follow_up?.payout_type_selection
                    ?.map(item => ({
                        value: Object.keys(item)[0],
                        text: Object.values(item)[0]
                    })) || [];

                this.editForm.optional_modules = template.follow_up?.optional_modules?.map(item =>
                    ({
                        value: Object.keys(item)[0],
                        text: Object.values(item)[0]
                    })) || [];
            },

            setEditForm(machine) {
                this.editForm.id = machine.id || null;
                this.editForm.name = machine.name || '';
                this.editForm.machine_category = machine.machine_category || '';
                this.editForm.machine_type = machine.machine_type || '';
                this.editForm.arcade_id = String(machine.arcade_id || '');
                this.editForm.arcade_currency = machine.arcade?.currency || 'TWD';
                this.editForm.auth_key = machine.machine_auth_key?.auth_key || '';
                this.editForm.chip_hardware_id = machine.machine_auth_key?.chip_hardware_id || '';
                this.editForm.owner_id = String(machine.owner_id || '');
                this.editForm.owner = machine.owner || null;
                this.editForm.coin_input_value = this.formatPayoutUnitValue(machine
                    .coin_input_value ?? '0');
                this.editForm.credit_button_value = this.formatPayoutUnitValue(machine
                    .credit_button_value ?? '0');
                this.editForm.payout_button_value = this.formatPayoutUnitValue(machine
                    .payout_button_value ?? '0');
                this.editForm.payout_type = machine.payout_type || 'none';
                this.editForm.payout_unit_value = this.formatPayoutUnitValue(machine
                    .payout_unit_value ?? '0');
                this.editForm.revenue_split = String(machine.revenue_split ?? 45);
                this.editForm.bill_acceptor_enabled = machine.bill_acceptor_enabled ?? false;
                this.editForm.bill_currency = String(machine.bill_currency ?? this.editForm
                    .arcade_currency ?? 'TWD');
                // Correctly format share_pct to match option values (e.g., "0.5", "1.0")
                this.editForm.share_pct = machine.share_pct !== null ? parseFloat(machine.share_pct)
                    .toFixed(1) : '';

                // 僅在 machine_category 為 utility 時處理面額
                if (this.editForm.machine_category === 'utility') {
                    this.editForm.accepted_denominations = Array.isArray(machine
                            .accepted_denominations) ?
                        machine.accepted_denominations.map(String) : [];
                    const denominations = this.mappingsConfig[this.editForm.bill_currency] || [];
                    this.available_denominations_for_selected_currency = Array.isArray(
                            denominations) ?
                        denominations :
                        Object.values(denominations)
                        .map(String)
                        .filter((value, index, self) => self.indexOf(value) === index)
                        .sort((a, b) => Number(a) - Number(b));
                    this.editForm.all_denominations_selected = this
                        .available_denominations_for_selected_currency.length > 0 &&
                        this.editForm.accepted_denominations.length === this
                        .available_denominations_for_selected_currency.length &&
                        this.available_denominations_for_selected_currency.every(denom =>
                            this.editForm.accepted_denominations.includes(denom));
                } else {
                    this.editForm.accepted_denominations = [];
                    this.editForm.all_denominations_selected = false;
                    this.available_denominations_for_selected_currency = [];
                    this.editForm.bill_currency = 'TWD';
                }

                this.$nextTick(() => {
                    console.log('--- Debugging Edit Modal ---');
                    console.log('Final editForm state:', JSON.parse(JSON.stringify(this
                        .editForm)));
                    console.log('Available denominations:', this
                        .available_denominations_for_selected_currency);
                    console.log('--- End Debugging ---');
                });
            },

            resetCreateForm() {
                this.createForm = {
                    name: '',
                    arcade_id: '',
                    arcade_currency: 'TWD',
                    chip_hardware_id: '',
                    auth_key: '',
                    machine_category: '',
                    machine_type: '',
                    payout_type: 'none',
                    payout_type_selection: [],
                    optional_modules: [],
                    selected_modules: [],
                    credit_value: '0',
                    balls_per_credit: '0',
                    points_per_credit_action: '0',
                    payout_unit_value: '0',
                    revenue_split: 45,
                    owner_id: @json($ownerId),
                    bill_acceptor_enabled: false,
                    bill_currency: 'TWD',
                    accepted_denominations: [],
                    all_denominations_selected: true,
                    share_pct: '',
                };
                this.available_denominations_for_selected_currency = [];
                if (this.createForm.machine_category === 'utility' && this.createForm
                    .bill_currency && this.mappingsConfig[this.createForm.bill_currency]) {
                    const denominations = this.mappingsConfig[this.createForm.bill_currency];
                    this.available_denominations_for_selected_currency = (Array.isArray(
                            denominations) ? denominations : Object.values(denominations))
                        .map(String)
                        .filter((value, index, self) => self.indexOf(value) === index)
                        .sort((a, b) => Number(a) - Number(b));
                    this.createForm.accepted_denominations = [...this
                        .available_denominations_for_selected_currency
                    ];
                    this.createForm.all_denominations_selected = true;
                } else {
                    this.createForm.accepted_denominations = [];
                    this.createForm.all_denominations_selected = false;
                }
            },

            submitCreate() {
                let dataToSubmit = {
                    ...this.createForm
                };
                if (dataToSubmit.machine_category === 'utility') {
                    dataToSubmit.bill_acceptor_enabled = true;
                } else {
                    dataToSubmit.accepted_denominations = [];
                    dataToSubmit.all_denominations_selected = false;
                    dataToSubmit.bill_currency = null;
                }
                console.log('Final data to submit:', JSON.stringify(dataToSubmit)); // <<<<<< 添加這行

                axios.post('{{ route('arcade.machines.store') }}', dataToSubmit)
                    .then(response => {
                        this.closeCreateModal();
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('msg.success') }}',
                            text: response.data.message ||
                                '{{ __('msg.machine_created_successfully') }}',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error creating machine:', error.response);
                        let title = '{{ __('msg.error') }}';
                        let htmlMessage = '{{ __('msg.failed_to_create_machine') }}';
                        if (error.response && error.response.data) {
                            if (error.response.data.message) {
                                htmlMessage += `<br>${error.response.data.message}`;
                            }
                            if (error.response.data.errors) {
                                htmlMessage +=
                                    '<ul class="mt-2 text-sm text-red-600 list-disc list-inside">';
                                Object.values(error.response.data.errors).forEach(errorsArray =>
                                    errorsArray.forEach(err => htmlMessage +=
                                        `<li>${err}</li>`));
                                htmlMessage += '</ul>';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: htmlMessage
                        });
                    });
            },

            submitEdit() {
                let dataToSubmit = {
                    ...this.editForm
                };
                dataToSubmit.payout_unit_value = this.formatPayoutUnitValue(this.editForm
                    .payout_unit_value);
                if (dataToSubmit.machine_category !== 'utility') {
                    dataToSubmit.accepted_denominations = [];
                    dataToSubmit.all_denominations_selected = false;
                    dataToSubmit.bill_currency = null;
                }
                console.log('提交 editForm 數據:', JSON.stringify(dataToSubmit));

                if (!this.editForm.id || isNaN(this.editForm.id)) {
                    console.error('無效的機器 ID:', this.editForm.id);
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('msg.error') }}',
                        html: '無效的機器 ID，請重新嘗試。'
                    });
                    return;
                }

                const routeTemplate = "{{ route('arcade.machines.update', ':id') }}";
                const url = routeTemplate.replace(':id', this.editForm.id);
                axios.patch(url, dataToSubmit)
                    .then(response => {
                        this.closeEditModal();
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('msg.success') }}',
                            text: response.data.message ||
                                '{{ __('msg.machine_updated_successfully') }}',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('更新機器失敗:', error.response);
                        let title = '{{ __('msg.error') }}';
                        let htmlMessage = '{{ __('msg.failed_to_update_machine') }}';
                        if (error.response && error.response.data) {
                            if (error.response.data.message) {
                                htmlMessage += `<br>${error.response.data.message}`;
                            }
                            if (error.response.data.errors) {
                                console.log('驗證錯誤:', error.response.data.errors);
                                htmlMessage +=
                                    '<ul class="mt-2 text-sm text-red-600 list-disc list-inside">';
                                Object.values(error.response.data.errors).forEach(errorsArray =>
                                    errorsArray.forEach(err => htmlMessage +=
                                        `<li>${err}</li>`));
                                htmlMessage += '</ul>';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: htmlMessage
                        });
                    });
            },

            generateCreateKey() {
                this.isLoadingCreateKey = true;
                axios.post("{{ route('arcade.machine_auth_keys.generateSingle') }}")
                    .then(response => {
                        if (response.data.success) {
                            this.createForm.auth_key = response.data.auth_key;
                        } else {
                            alert(response.data.message ||
                                '{{ __('msg.failed_to_generate_key') }}');
                        }
                    })
                    .catch(error => {
                        console.error("Error generating key:", error);
                        let errorMessage = '{{ __('msg.generate_key_request_failed') }}';
                        if (error.response && error.response.data && error.response.data
                            .message) {
                            errorMessage += ': ' + error.response.data.message;
                        } else if (error.message) {
                            errorMessage += ': ' + error.message;
                        }
                        alert(errorMessage);
                    })
                    .finally(() => {
                        this.isLoadingCreateKey = false;
                    });
            },

            init() {
                if (!this.arcadesData || Object.keys(this.arcadesData).length === 0) {
                    console.warn(
                        'No arcades data available in Alpine, setting default currency for forms'
                    );
                    this.createForm.arcade_currency = 'TWD';
                    this.editForm.arcade_currency = 'TWD';
                }

                if (!this.createForm.coin_input_value_enabled) {
                    this.createForm.coin_input_value = '0';
                }

                this.$watch('createForm.coin_input_value_enabled', (value) => {
                    this.createForm.coin_input_value = value ? '10' : '0';
                });

                this.$watch('createForm.balls_per_credit_enabled', (value) => {
                    this.createForm.balls_per_credit = value ? '100' : '0';
                });

                this.$watch('createForm.credit_in_enable', (isChecked) => {
                    this.createForm.credit_button_value = isChecked ? '100' : '0';
                });

                this.$watch('createForm.create_credit_out_enable', (isChecked) => {
                    this.createForm.payout_button_value = isChecked ? '100' : '0';
                });

                this.$watch('createForm.payout_type', (newPayoutType) => {
                    const category = this.createForm.machine_category;
                    if (category === 'redemption') {
                        if (newPayoutType === 'tickets') {
                            this.createForm.payout_unit_value = '1'; // 彩票預設值
                        } else if (newPayoutType === 'coins') {
                            this.createForm.payout_unit_value = '10'; // 代幣預設值
                        } else if (newPayoutType === 'prize') {
                            this.createForm.payout_unit_value = '30'; // 獎品預設值
                        } else if (newPayoutType === 'points') {
                            this.createForm.payout_unit_value = '10'; // 點數預設值
                        }
                    }
                });

                this.$watch('createForm.machine_category', (newMachineCategory) => {
                    // ... 您現有的 utility 和 gambling 判斷邏輯 ...
                    if (newMachineCategory === 'utility') {
                        this.createForm.payout_type = 'none';
                        this.createForm.coin_input_value_enabled = false;
                        this.createForm.coin_input_value = '0';
                        this.createForm.credit_in_enable = false;
                        this.createForm.credit_button_value = '0';
                        this.createForm.create_credit_out_enable = false;
                        this.createForm.payout_button_value = '0';
                        this.createForm.bill_acceptor_enabled = true;
                        this.updateCreateDenominations();
                    } else {
                        this.createForm.bill_acceptor_enabled = false;
                        this.createForm.accepted_denominations = [];
                        this.createForm.all_denominations_selected = false;
                        this.createForm.bill_currency = 'TWD';
                        this.available_denominations_for_selected_currency = [];
                        this.createForm.coin_input_value_enabled = true;
                        if (this.createForm.coin_input_value_enabled && this.createForm
                            .coin_input_value === '0') {
                            this.createForm.coin_input_value = '10';
                        }
                    }

                    if (newMachineCategory === 'pinball' || newMachineCategory ===
                        'pinball_pachinko') { // 建議使用 pinball_pachinko 保持一致
                        this.createForm.payout_unit_value = '1'; // 彈珠台預設值
                    }
                });

                this.$watch('editForm.machine_category', (newMachineCategory) => {
                    if (newMachineCategory === 'utility') {
                        this.editForm.bill_acceptor_enabled = true;
                        this.updateEditDenominations();
                    } else {
                        this.editForm.bill_acceptor_enabled = false;
                        this.editForm.accepted_denominations = [];
                        this.editForm.all_denominations_selected = false;
                        this.editForm.bill_currency = 'TWD';
                        this.available_denominations_for_selected_currency = [];
                    }
                });

                this.$watch('createForm.arcade_id', (newArcadeId) => {
                    if (newArcadeId && this.arcadesData && this.arcadesData[newArcadeId]) {
                        this.createForm.arcade_currency = String(this.arcadesData[
                            newArcadeId].currency || 'TWD');
                        this.createForm.bill_currency = String(this.createForm
                            .arcade_currency);
                    } else {
                        this.createForm.arcade_currency = 'TWD';
                        this.createForm.bill_currency = 'TWD';
                    }
                    this.updateCreateDenominations();
                });

                this.$watch('editForm.arcade_id', (newArcadeId) => {
                    if (newArcadeId && this.arcadesData && this.arcadesData[newArcadeId]) {
                        this.editForm.arcade_currency = String(this.arcadesData[newArcadeId]
                            .currency || 'TWD');
                        this.editForm.bill_currency = String(this.editForm.arcade_currency);
                    } else {
                        this.editForm.arcade_currency = 'TWD';
                        this.editForm.bill_currency = 'TWD';
                    }
                    this.updateEditDenominations();
                });

                this.$watch('createForm.bill_currency', (newBillCurrency) => {
                    this.updateCreateDenominations();
                });

                this.$watch('editForm.bill_currency', (newBillCurrency) => {
                    this.updateEditDenominations();
                });

                this.$watch('createForm.all_denominations_selected', (isAllSelected) => {
                    if (this.createForm.machine_category !== 'utility') return;
                    if (isAllSelected) {
                        this.createForm.accepted_denominations = [...this
                            .available_denominations_for_selected_currency
                        ];
                    } else if (this.createForm.accepted_denominations.length === this
                        .available_denominations_for_selected_currency.length) {
                        this.createForm.accepted_denominations = [];
                    }
                });

                this.$watch('editForm.all_denominations_selected', (isAllSelected) => {
                    if (this.editForm.machine_category !== 'utility') return;
                    if (isAllSelected) {
                        this.editForm.accepted_denominations = [...this
                            .available_denominations_for_selected_currency
                        ];
                    } else if (this.editForm.accepted_denominations.length === this
                        .available_denominations_for_selected_currency.length) {
                        this.editForm.accepted_denominations = [];
                    }
                });

                this.$watch('createForm.accepted_denominations', (newlyAccepted, oldValue) => {
                    if (this.createForm.machine_category !== 'utility' || JSON.stringify(
                            newlyAccepted) === JSON.stringify(oldValue)) return;
                    const allCurrentlyAvailable = this
                        .available_denominations_for_selected_currency || [];
                    if (allCurrentlyAvailable.length > 0 && newlyAccepted && newlyAccepted
                        .length === allCurrentlyAvailable.length &&
                        allCurrentlyAvailable.every(denom => newlyAccepted.includes(denom))
                    ) {
                        if (!this.createForm.all_denominations_selected) {
                            this.createForm.all_denominations_selected = true;
                        }
                    } else {
                        if (this.createForm.all_denominations_selected) {
                            this.createForm.all_denominations_selected = false;
                        }
                    }
                });

                this.$watch('editForm.accepted_denominations', (newlyAccepted, oldValue) => {
                    if (this.editForm.machine_category !== 'utility' || JSON.stringify(
                            newlyAccepted) === JSON.stringify(oldValue)) return;
                    const allCurrentlyAvailable = this
                        .available_denominations_for_selected_currency || [];
                    if (allCurrentlyAvailable.length > 0 && newlyAccepted && newlyAccepted
                        .length === allCurrentlyAvailable.length &&
                        allCurrentlyAvailable.every(denom => newlyAccepted.includes(denom))
                    ) {
                        if (!this.editForm.all_denominations_selected) {
                            this.editForm.all_denominations_selected = true;
                        }
                    } else {
                        if (this.editForm.all_denominations_selected) {
                            this.editForm.all_denominations_selected = false;
                        }
                    }
                });

                // 輔助方法更新面額
                this.updateCreateDenominations = function() {
                    if (this.createForm.machine_category !== 'utility') {
                        this.available_denominations_for_selected_currency = [];
                        this.createForm.accepted_denominations = [];
                        this.createForm.all_denominations_selected = false;
                        return;
                    }
                    const newBillCurrency = this.createForm.bill_currency;
                    if (newBillCurrency && this.mappingsConfig && this.mappingsConfig[
                            newBillCurrency]) {
                        const denominations = this.mappingsConfig[newBillCurrency];
                        this.available_denominations_for_selected_currency = (Array.isArray(
                                denominations) ? denominations : Object.values(
                                denominations))
                            .map(String)
                            .filter((value, index, self) => self.indexOf(value) === index)
                            .sort((a, b) => Number(a) - Number(b));
                        if (this.available_denominations_for_selected_currency.length > 0) {
                            this.createForm.accepted_denominations = [...this
                                .available_denominations_for_selected_currency
                            ];
                            this.createForm.all_denominations_selected = true;
                        } else {
                            this.createForm.accepted_denominations = [];
                            this.createForm.all_denominations_selected = false;
                        }
                    } else {
                        this.available_denominations_for_selected_currency = [];
                        this.createForm.accepted_denominations = [];
                        this.createForm.all_denominations_selected = false;
                    }
                };

                this.updateEditDenominations = function() {
                    if (this.editForm.machine_category !== 'utility') {
                        this.available_denominations_for_selected_currency = [];
                        this.editForm.accepted_denominations = [];
                        this.editForm.all_denominations_selected = false;
                        return;
                    }
                    const newBillCurrency = this.editForm.bill_currency;
                    if (newBillCurrency && this.mappingsConfig && this.mappingsConfig[
                            newBillCurrency]) {
                        const denominations = this.mappingsConfig[newBillCurrency];
                        this.available_denominations_for_selected_currency = (Array.isArray(
                                denominations) ? denominations : Object.values(
                                denominations))
                            .map(String)
                            .filter((value, index, self) => self.indexOf(value) === index)
                            .sort((a, b) => Number(a) - Number(b));
                        if (!this.editForm.accepted_denominations.length && this
                            .available_denominations_for_selected_currency.length > 0) {
                            this.editForm.accepted_denominations = [...this
                                .available_denominations_for_selected_currency
                            ];
                            this.editForm.all_denominations_selected = true;
                        }
                    } else {
                        this.available_denominations_for_selected_currency = [];
                        this.editForm.accepted_denominations = [];
                        this.editForm.all_denominations_selected = false;
                    }
                };
            }
        }));
    });
</script>
