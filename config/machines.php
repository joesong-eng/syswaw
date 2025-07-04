<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Machine Behavioral Templates
    |--------------------------------------------------------------------------
    |
    | Defines machine behaviors. Each template has a display name,
    | allowed payout types, and a list of specific machine types that
    | fall under this behavior.
    |
    */
    'templates' => [
        'simple_io' => [
            'display' => 'msg.template_simple_io', // 翻譯鍵："一般娛樂機 (投幣/出獎)"
            'payout_types' => ['none', 'tickets', 'points', 'coins'],
            'machine_types' => [
                'normally' => 'msg.normally',
                'racing_game' => 'msg.racing_game',
                'dance_game' => 'msg.dance_game',
                'basketball_game' => 'msg.basketball_game',
                'air_hockey' => 'msg.air_hockey',
                'beat_em_up' => 'msg.beat_em_up',
                'light_gun_game' => 'msg.light_gun_game',
                'light_and_sound_game' => 'msg.light_and_sound_game',
                'punching_machine' => 'msg.punching_machine',
                // ...可以繼續增加
            ],
        ],
        'gambling_like' => [
            'display' => 'msg.template_gambling_like', // 翻譯鍵："電子遊戲機 (多重輸入/返還率)"
            'payout_types' => ['tickets', 'points', 'coins'],
            'machine_types' => [
                'slot_machine' => 'msg.slot_machine',
                'gambling' => 'msg.gambling',
            ],
        ],
        'claw_like' => [
            'display' => 'msg.template_claw_like', // 翻譯鍵："獎品機 (娃娃機/禮品機)"
            'payout_types' => ['prize'], // 娃娃機的產出類型固定是 'prize'
            'machine_types' => [
                'claw_machine' => 'msg.claw_machine',
                'giant_claw_machine' => 'msg.giant_claw_machine',
                'stacker_machine' => 'msg.stacker_machine',
            ],
        ],
        'pinball_like' => [
            'display' => 'msg.template_pinball_like', // 翻譯鍵："彈珠台 (鋼珠交換)"
            'payout_types' => ['ball'], // 彈珠台的產出類型固定是 'ball'
            'machine_types' => [
                'pinball' => 'msg.pinball',
                'pachinko' => 'msg.pachinko',
            ],
        ],
        'input_only' => [
            'display' => 'msg.template_input_only', // 翻譯鍵："兌換/儲值機 (只進不出)"
            'payout_types' => ['none'], // 只進不出的機器沒有產出類型
            'machine_types' => [
                'money_slot' => 'msg.money_slot',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Type Definitions
    |--------------------------------------------------------------------------
    */
    'types' => [
        'normally' => 'msg.normally', // 普通機台
        'money_slot' => 'msg.money_slot', //紙鈔機
        'pinball' => 'msg.pinball', // 柏青哥
        'pachinko' => 'msg.pachinko', // 柏青哥
        'claw_machine' => 'msg.claw_machine', // 娃娃機 (與 'claw' 指向同一個翻譯，但資料庫值不同)
        'beat_em_up' => 'msg.beat_em_up', // 格鬥遊戲
        'racing_game' => 'msg.racing_game', // 賽車遊戲
        'light_gun_game' => 'msg.light_gun_game', // 光線槍遊戲
        'dance_game' => 'msg.dance_game', // 跳舞機
        'basketball_game' => 'msg.basketball_game', // 籃球機
        'air_hockey' => 'msg.air_hockey', // 空氣曲棍球
        'slot_machine' => 'msg.slot_machine', // 老虎機
        'light_and_sound_game' => 'msg.light_and_sound_game', // 聲光遊戲
        'labyrinth_game' => 'msg.labyrinth_game', // 迷宮遊戲
        'flight_simulator' => 'msg.flight_simulator', // 飛行模擬器
        'punching_machine' => 'msg.punching_machine', // 拳擊機
        'water_shooting_game' => 'msg.water_shooting_game', // 水槍射擊遊戲
        'stacker_machine' => 'msg.stacker_machine', // 堆疊機 (如 Stacker)
        'mini_golf_game' => 'msg.mini_golf_game', // 迷你高爾夫遊戲
        'interactive_dance_game' => 'msg.interactive_dance_game', // 互動跳舞遊戲
        'electronic_shooting_game' => 'msg.electronic_shooting_game', // 電子射擊遊戲
        'giant_claw_machine' => 'msg.giant_claw_machine', // 大型娃娃機
        'arcade_music_game' => 'msg.arcade_music_game', // 街機音樂遊戲
    ],

];
