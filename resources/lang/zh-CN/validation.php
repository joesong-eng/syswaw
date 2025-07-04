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
            'unique' => '此芯片硬件 ID 已被使用。', // 簡體中文翻譯
        ],
        'machine_type' => [
            'in' => '选择的游戏机类型无效。', // 簡體中文翻譯
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
        'chipKey'          => __('msg.chip_token'), // 智联机令牌 (來自您的 zh-CN/msg.php)
        'name'             => __('msg.name'), // 名称
        'machine_type'     => __('msg.machine_type'), // 机器类型
        'arcade_id'        => __('msg.arcade'), // 娱乐城
        'chip_hardware_id' => __('msg.chip_hardware_id'), // 硬件 ID
        'owner_id'         => __('msg.owner'), // 所有人
        // 在这里添加其他您在验证中使用的属性名称的翻译
    ],
];
