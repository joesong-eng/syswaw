<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    // 定義系統設定的鍵名常量，方便管理
    const KEY_DEFAULT_CREDIT_VALUE = 'default_credit_value';
    const KEY_PLATFORM_SHARE_PCT = 'platform_share_pct';
    const KEY_ARCADE_DEFAULT_SHARE_PCT = 'arcade_default_share_pct';
    const KEY_MACHINE_DEFAULT_SHARE_PCT = 'machine_default_share_pct';
    const KEY_DEFAULT_BALLS_PER_CREDIT = 'default_balls_per_credit'; // 新增：預設每 Credit 出珠數

    /**
     * 顯示帳務設定頁面。
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = SystemSetting::pluck('setting_value', 'setting_key');

        $defaultCreditValue = $settings[self::KEY_DEFAULT_CREDIT_VALUE] ?? 10.00; // 預設值
        $platformSharePct = $settings[self::KEY_PLATFORM_SHARE_PCT] ?? 5.00;    // 預設值
        $arcadeDefaultSharePct = $settings[self::KEY_ARCADE_DEFAULT_SHARE_PCT] ?? 55.00; // 預設值
        $machineDefaultSharePct = $settings[self::KEY_MACHINE_DEFAULT_SHARE_PCT] ?? 40.00; // 預設值
        $defaultBallsPerCredit = $settings[self::KEY_DEFAULT_BALLS_PER_CREDIT] ?? 10; // 新增：預設值，例如10顆

        return view('admin.settings.accounting', compact(
            'defaultCreditValue',
            'platformSharePct',
            'arcadeDefaultSharePct',
            'machineDefaultSharePct',
            'defaultBallsPerCredit' // 新增
        ));
    }

    /**
     * 更新帳務設定。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $rules = [
            'default_credit_value' => 'required|numeric|min:0',
            'platform_share_pct' => 'required|numeric|min:0|max:100',
            'arcade_default_share_pct' => 'required|numeric|min:0|max:100',
            'machine_default_share_pct' => 'required|numeric|min:0|max:100',
            'default_balls_per_credit' => 'required|integer|min:1', // 新增：驗證規則，必須是正整數
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $platformShare = (float) $request->input('platform_share_pct', 0);
            $arcadeShare = (float) $request->input('arcade_default_share_pct', 0);
            $machineShare = (float) $request->input('machine_default_share_pct', 0);

            if (round($platformShare + $arcadeShare + $machineShare, 2) !== 100.00) {
                $validator->errors()->add('share_sum', '平台、遊藝場和遊戲機老闆的分潤百分比總和必須等於 100%。');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->updateOrCreateSetting(self::KEY_DEFAULT_CREDIT_VALUE, $request->input('default_credit_value'), '預設每 Credit 價值 (台幣)');
        $this->updateOrCreateSetting(self::KEY_PLATFORM_SHARE_PCT, $request->input('platform_share_pct'), '平台商分潤百分比 (%)');
        $this->updateOrCreateSetting(self::KEY_ARCADE_DEFAULT_SHARE_PCT, $request->input('arcade_default_share_pct'), '預設遊藝場老闆分潤百分比 (%)');
        $this->updateOrCreateSetting(self::KEY_MACHINE_DEFAULT_SHARE_PCT, $request->input('machine_default_share_pct'), '預設遊戲機老闆分潤百分比 (%)');
        $this->updateOrCreateSetting(self::KEY_DEFAULT_BALLS_PER_CREDIT, $request->input('default_balls_per_credit'), '預設每 Credit 出珠數 (顆)'); // 新增

        return redirect()->route('admin.settings.accounting.index')->with('success', '帳務設定已成功更新！');
    }

    /**
     * Helper function to update or create a setting.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return void
     */
    private function updateOrCreateSetting(string $key, $value, ?string $description = null)
    {
        SystemSetting::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'description' => $description ?? SystemSetting::where('setting_key', $key)->first()->description ?? ''
            ]
        );
    }
}
