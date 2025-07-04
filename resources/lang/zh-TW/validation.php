<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom' => [
        'chip_hardware_id' => [
            'unique' => '此晶片硬體 ID 已被使用。',
        ],
        'machine_type' => [
            'in' => '選擇的遊戲機種類無效。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */
    'attributes' => [
        'chipKey'          => __('msg.chip_token'), // 通訊卡金鑰
        'name'             => __('msg.name'), // 名稱
        'machine_type'     => __('msg.machine_type'), // 機器類型
        'arcade_id'        => __('msg.arcade'), // 遊藝場
        'chip_hardware_id' => __('msg.chip_hardware_id'), // 晶片硬體 ID
        'owner_id'         => __('msg.owner'), // 擁有者
        // 在這裡添加其他您在驗證中使用的屬性名稱的翻譯

    ],
];
