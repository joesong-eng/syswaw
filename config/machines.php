<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Machine Behavior-Driven Templates (引導式設定)
    |--------------------------------------------------------------------------
    |
    | 這個結構定義了「新增機台」時的引導式選項。
    | 每個 key (e.g., 'entertainment_only') 代表一個主要的營運模式。
    | - 'display': 在第一步選擇中顯示給用戶的文字 (對應翻譯鍵)。
    | - 'follow_up': 根據第一步選擇，需要用戶進一步提供的資訊。
    |   - 'payout_type_selection': 需要用戶選擇返還物類型。
    |   - 'optional_modules': 讓用戶勾選額外支援的功能。
    | - 'fixed_payout_type': 此模式下固定的返還物類型。
    | - 'db_category_map': 此模式對應到資料庫中的核心分類 (建議未來新增 machine_category 欄位)。
    |
    */
    'templates' => [
        'entertainment_only' => [
            'display' => 'msg.template_entertainment_only', // "一般娛樂機 (投幣玩遊戲，無返還)"
            'follow_up' => [],
            'fixed_payout_type' => 'none',
            'db_category_map' => 'pure_game',
        ],
        'redemption' => [
            'display' => 'msg.template_redemption', // "獎勵型遊戲機 (投幣玩，依表現給獎勵)"
            'follow_up' => [
                'payout_type_selection' => ['tickets', 'coins', 'prize', 'points'],
            ],
            'db_category_map' => 'redemption',
        ],
        'pinball' => [
            'display' => 'msg.template_pinball', // "彈珠台 (換鋼珠，玩遊戲贏鋼珠)"
            'follow_up' => [],
            'fixed_payout_type' => 'ball',
            'db_category_map' => 'pinball_pachinko',
        ],
        'gambling' => [
            'display' => 'msg.template_gambling', // "電子遊戲機 (主要透過開分/洗分記帳)"
            'follow_up' => [
                'optional_modules' => [
                    ['accepts_coin' => '接受硬幣'],
                    ['accepts_bill' => '接受紙鈔'],
                    ['payouts_coin' => '硬幣出點'],
                    ['payouts_ticket' => '彩票出點'],
                ],
            ],
            'fixed_payout_type' => 'none', // 基礎是 none，可被模組覆蓋
            'db_category_map' => 'gambling',
        ],
        'utility' => [
            'display' => 'msg.template_utility', // "兌換/儲值機 (只收錢，用於兌換)"  // "兌換/儲值機 (只收錢，用於兌換)"
            'follow_up' => [],
            'fixed_payout_type' => 'none',
            'db_category_map' => 'other', // 'other'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Machine Type Definitions (for display purposes)
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
