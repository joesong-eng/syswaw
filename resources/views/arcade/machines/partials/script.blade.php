{{-- /Users/ilawusong/Documents/sysWawIot/sys180/resources/views/arcade/machines/partials/script.blade.php --}}
@php
    $ownerId = auth()->check()
        ? (auth()->user()->isArcadeStaff() && !is_null(auth()->user()->parent)
            ? auth()->user()->parent->id
            : auth()->id())
        : null;
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
                machine_type: '',
                payout_type: 'none',
                ball_input_value: '1',
                point_value: '100',
                ticket_value: '10',
                prize_value: '30',
                coin_input_value_enabled: false,
                coin_input_value: '0',
                credit_button_value: '0',
                payout_button_value: '0',
                revenue_split: 45,
                balls_per_credit: '0',
                balls_per_credit_enabled: false,
                credit_in_enable: false,
                points_per_credit_action: '0',
                create_credit_out_enable: false,
                owner_id: @json($ownerId),
                bill_acceptor_enabled: false,
                bill_currency: 'TWD',
                accepted_denominations: [],
                all_denominations_selected: true
            },

            // 編輯用的表單
            editForm: {
                id: null,
                name: '',
                machine_type: '',
                arcade_id: '',
                arcade_currency: 'TWD',
                auth_key: '',
                chip_hardware_id: '',
                machine_auth_key: null,
                coin_input_value: '0',
                credit_button_value: '0',
                payout_button_value: '0',
                payout_type: 'none',
                payout_unit_value: null,
                revenue_split: null,
                owner_id: null,
                owner: null,
                bill_acceptor_enabled: false,
                bill_currency: 'TWD',
                accepted_denominations: [],
                all_denominations_selected: false // 新增：與 createForm 一致，預設 false
            },

            // 可選面額，初始化為空數組
            available_denominations_for_selected_currency: [],

            // 從後端傳遞的數據
            arcadesData: @json(
                $arcades->mapWithKeys(function ($arcade) {
                        return [$arcade->id => ['name' => $arcade->name, 'currency' => $arcade->currency ?? 'TWD']];
                    })->all()),
            machineTypeNames: @json(collect(config('machines.types', []))->mapWithKeys(function ($displayNameKey, $key) {
                        return [$key => __($displayNameKey)];
                    })->all()),
            payoutTypeNames: {
                'none': "{{ __('msg.select') }}",
                'ball': "{{ __('msg.payout_type_pachinko') }}",
                'points': "{{ __('msg.payout_type_points') }}",
                'tickets': "{{ __('msg.payout_type_tickets') }}",
                'coins': "{{ __('msg.payout_type_coins') }}",
                'prize': "{{ __('msg.payout_type_prize_claw') }}"
            },
            // 定義一次 bill_mappings 配置
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
                console.log('openCreateModal called');
                this.resetCreateForm();
                console.log('after resetCreateForm, createForm is:', JSON.parse(JSON.stringify(this
                    .createForm)));
                this.showCreateMachineModal = true;
                console.log('showCreateMachineModal is now:', this.showCreateMachineModal);
            },

            closeCreateModal() {
                this.showCreateMachineModal = false;
            },

            setEditForm(machine) {
                console.log('setEditForm called with machine:', JSON.parse(JSON.stringify(
                    machine)));
                console.log('machine.accepted_denominations:', JSON.stringify(machine
                    .accepted_denominations));

                this.editForm.id = machine.id || null;
                this.editForm.name = machine.name || '';
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
                this.editForm.accepted_denominations = Array.isArray(machine
                        .accepted_denominations) ?
                    machine.accepted_denominations.map(String) : [];

                // 更新面額
                console.log('mappingsConfig:', JSON.parse(JSON.stringify(this.mappingsConfig)));
                console.log('bill_currency:', this.editForm.bill_currency);
                console.log('mappingsConfig[bill_currency]:', this.mappingsConfig[this.editForm
                    .bill_currency]);

                const denominations = this.mappingsConfig[this.editForm.bill_currency];
                // 處理物件或陣列，提取值
                this.available_denominations_for_selected_currency = denominations ?
                    (Array.isArray(denominations) ? denominations : Object.values(denominations))
                    .map(String)
                    .filter((value, index, self) => self.indexOf(value) === index)
                    .sort((a, b) => Number(a) - Number(b)) : [];

                this.editForm.all_denominations_selected = this
                    .available_denominations_for_selected_currency.length > 0 &&
                    this.editForm.accepted_denominations.length === this
                    .available_denominations_for_selected_currency.length &&
                    this.available_denominations_for_selected_currency.every(denom =>
                        this.editForm.accepted_denominations.includes(denom));

                console.log('available_denominations_for_selected_currency:', this
                    .available_denominations_for_selected_currency);
                console.log('editForm.accepted_denominations:', this.editForm
                    .accepted_denominations);
                console.log('editForm.all_denominations_selected:', this.editForm
                    .all_denominations_selected);

                this.showEditMachineModal = true;

                this.$nextTick(() => {
                    console.log('--- Debugging Edit Modal ---');
                    console.log('Final editForm state:', JSON.parse(JSON.stringify(this
                        .editForm)));
                    console.log('Available denominations:', this
                        .available_denominations_for_selected_currency);
                    console.log('--- End Debugging ---');
                });
            },

            closeEditModal() {
                this.showEditMachineModal = false;
            },

            resetCreateForm() {
                this.createForm = {
                    name: '',
                    arcade_id: '',
                    arcade_currency: 'TWD',
                    chip_hardware_id: '',
                    auth_key: '',
                    machine_type: '',
                    payout_type: 'none',
                    ball_input_value: '1',
                    point_value: '100',
                    ticket_value: '10',
                    prize_value: '30',
                    coin_input_value: '0',
                    coin_input_value_enabled: false,
                    credit_button_value: '0',
                    payout_button_value: '0',
                    revenue_split: 45,
                    balls_per_credit: '0',
                    balls_per_credit_enabled: false,
                    credit_in_enable: false,
                    points_per_credit_action: '0',
                    create_credit_out_enable: false,
                    owner_id: @json($ownerId),
                    bill_acceptor_enabled: false,
                    bill_currency: 'TWD',
                    accepted_denominations: [],
                    all_denominations_selected: true
                };
                this.available_denominations_for_selected_currency = [];
                const newBillCurrency = this.createForm.bill_currency;
                if (newBillCurrency && this.mappingsConfig && this.mappingsConfig[
                        newBillCurrency]) {
                    const denominations = this.mappingsConfig[newBillCurrency];
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
                switch (this.createForm.payout_type) {
                    case 'ball':
                        dataToSubmit.payout_unit_value = this.createForm.ball_input_value;
                        break;
                    case 'tickets':
                        dataToSubmit.payout_unit_value = this.createForm.ticket_value;
                        break;
                    case 'points':
                        dataToSubmit.payout_unit_value = this.createForm.point_value;
                        break;
                    case 'prize':
                        dataToSubmit.payout_unit_value = this.createForm.prize_value;
                        break;
                    case 'coins':
                        dataToSubmit.payout_unit_value = this.createForm.coin_input_value_enabled ?
                            this.createForm.coin_input_value : '0';
                        break;
                    default:
                        dataToSubmit.payout_unit_value = '0';
                }
                if (dataToSubmit.machine_type === 'money_slot') {
                    dataToSubmit.bill_acceptor_enabled = true;
                }
                console.log('Submitting createForm data:', dataToSubmit);
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

            generateCreateKey() {
                this.isLoadingCreateKey = true;
                axios.post("{{ route('arcade.auth_keys.generate_single') }}")
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
                console.log('machineManagement Alpine component initialized.');
                console.log('Initial arcadesData:', JSON.parse(JSON.stringify(this.arcadesData)));
                console.log('Initial machineTypeNames:', JSON.parse(JSON.stringify(this
                    .machineTypeNames)));
                console.log('mappingsConfig:', JSON.parse(JSON.stringify(this.mappingsConfig)));

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

                this.$watch('createForm.payout_type', (newPayoutType) => {});

                this.$watch('createForm.machine_type', (newMachineType) => {
                    if (newMachineType === 'money_slot') {
                        this.createForm.payout_type = 'none';
                        this.createForm.coin_input_value_enabled = false;
                        this.createForm.coin_input_value = '0';
                        this.createForm.credit_in_enable = false;
                        this.createForm.credit_button_value = '0';
                        this.createForm.create_credit_out_enable = false;
                        this.createForm.payout_button_value = '0';
                    } else {
                        this.createForm.bill_acceptor_enabled = false;
                        this.createForm.coin_input_value_enabled = true;
                        if (this.createForm.coin_input_value_enabled && this.createForm
                            .coin_input_value === '0') {
                            this.createForm.coin_input_value = '10';
                        }
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
                    this.$dispatch('alpine-watch-bill-currency', this.createForm
                        .bill_currency);
                });

                // 在 init() 中的 createForm.bill_currency 監聽器
                this.$watch('createForm.bill_currency', (newBillCurrency) => {
                    console.log('Bill currency changed to:', newBillCurrency);
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
                        console.log('Available denominations:', this
                            .available_denominations_for_selected_currency);
                    } else {
                        this.available_denominations_for_selected_currency = [];
                        this.createForm.accepted_denominations = [];
                        this.createForm.all_denominations_selected = false;
                        console.log('No denominations for currency or currency not found:',
                            newBillCurrency);
                    }
                });

                this.$watch('createForm.all_denominations_selected', (isAllSelected) => {
                    if (isAllSelected) {
                        this.createForm.accepted_denominations = [...this
                            .available_denominations_for_selected_currency
                        ];
                    } else {
                        if (this.createForm.accepted_denominations.length === this
                            .available_denominations_for_selected_currency.length) {
                            this.createForm.accepted_denominations = [];
                        }
                    }
                });

                this.$watch('createForm.accepted_denominations', (newlyAccepted) => {
                    const allCurrentlyAvailable = this
                        .available_denominations_for_selected_currency;
                    if (allCurrentlyAvailable.length > 0 && newlyAccepted.length ===
                        allCurrentlyAvailable.length &&
                        allCurrentlyAvailable.every(denom => newlyAccepted.includes(denom))
                    ) {
                        if (!this.createForm.all_denominations_selected) this.createForm
                            .all_denominations_selected = true;
                    } else {
                        if (this.createForm.all_denominations_selected) this.createForm
                            .all_denominations_selected = false;
                    }
                });

                this.$watch('editForm.bill_currency', (newBillCurrency) => {
                    console.log('EditForm: Bill currency changed to:', newBillCurrency);
                    if (newBillCurrency && this.mappingsConfig && this.mappingsConfig[
                            newBillCurrency]) {
                        this.available_denominations_for_selected_currency = Object.values(
                                this.mappingsConfig[newBillCurrency])
                            .map(String)
                            .filter((value, index, self) => self.indexOf(value) === index)
                            .sort((a, b) => Number(a) - Number(b));
                        if (this.available_denominations_for_selected_currency.length > 0) {
                            this.editForm.accepted_denominations = [...this
                                .available_denominations_for_selected_currency
                            ];
                            this.editForm.all_denominations_selected = true;
                        } else {
                            this.editForm.accepted_denominations = [];
                            this.editForm.all_denominations_selected = false;
                        }
                    } else {
                        this.available_denominations_for_selected_currency = [];
                        this.editForm.accepted_denominations = [];
                        this.editForm.all_denominations_selected = false;
                    }
                });

                this.$watch('editForm.all_denominations_selected', (isAllSelected) => {
                    if (isAllSelected) {
                        this.editForm.accepted_denominations = [...this
                            .available_denominations_for_selected_currency
                        ];
                    } else {
                        if (this.editForm.accepted_denominations.length === this
                            .available_denominations_for_selected_currency.length) {
                            this.editForm.accepted_denominations = [];
                        }
                    }
                });

                this.$watch('editForm.accepted_denominations', (newlyAccepted) => {
                    const allCurrentlyAvailable = this
                        .available_denominations_for_selected_currency;
                    if (allCurrentlyAvailable.length > 0 && newlyAccepted.length ===
                        allCurrentlyAvailable.length &&
                        allCurrentlyAvailable.every(denom => newlyAccepted.includes(denom))
                    ) {
                        if (!this.editForm.all_denominations_selected) this.editForm
                            .all_denominations_selected = true;
                    } else {
                        if (this.editForm.all_denominations_selected) this.editForm
                            .all_denominations_selected = false;
                    }
                });

                // this.$watch('available_denominations_for_selected_currency', (newValue) => {
                //     console.log('available_denominations_for_selected_currency changed:',
                //         JSON.parse(JSON.stringify(newValue)));
                // });

                if (this.createForm.bill_currency) {
                    this.$dispatch('alpine-watch-bill-currency', this.createForm.bill_currency);
                }
            }
        }));
    });
</script>
