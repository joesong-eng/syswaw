<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Carbon; // 引入 Carbon

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:16'],
            'name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:45', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation_code' => ['nullable', 'string', 'exists:users,invitation_code'], // 新增邀請碼驗證
        ]);
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $regionMap = [
                '886' => 'TW',
                '86'  => 'CN',
                '1'   => 'US',
                '81'  => 'JP',
                '62'  => 'ID',
                '60'  => 'MY',
                '65'  => 'SG',
                '66'  => 'TH',
                '63'  => 'PH',
                '84'  => 'VN',
                '82'  => 'KR',
                '95'  => 'MM',
                '673' => 'BN',
                '855' => 'KH',
                '856' => 'LA',
                '91'  => 'IN',
                '61'  => 'AU',
                '64'  => 'NZ',
                '44'  => 'GB',
                '33'  => 'FR',
                '49'  => 'DE',
                '34'  => 'ES',
                '39'  => 'IT',
                '31'  => 'NL',
                '46'  => 'SE',
                '45'  => 'DK',
                '41'  => 'CH',
                '47'  => 'NO',
            ];

            $countryCode = ltrim($validated['country_code'], '+'); // 移除 "+"
            $regionCode = $regionMap[$countryCode] ?? null;

            if (!$regionCode) {
                throw new Exception("無效的國家代碼：$countryCode");
            }

            $phone = ltrim($request->phone, '+'); // 去掉電話號碼的 "+"
            $phoneNumber = $phoneUtil->parse($phone, $regionCode);
            $formattedNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        } catch (NumberParseException $e) {
            return response()->json(['error' => 'Invalid phone number format'], 400);
        }

        $parent_id = null;
        $assignRoleName = null; // 用於儲存要分配的角色名稱

        if ($request->filled('invitation_code')) {
            $inviter = User::where('invitation_code', $request->invitation_code)->first();
            if ($inviter) {
                $parent_id = $inviter->id;
                // 根據邀請者的角色決定新用戶的角色
                if ($inviter->hasRole('arcade-owner')) {
                    $assignRoleName = 'arcade-staff';
                } elseif ($inviter->hasRole('machine-owner')) {
                    $assignRoleName = 'machine-staff'; // 假設 machine-owner 的員工角色是 machine-staff
                }
                // 如果邀請碼有效但邀請者角色不符合上述條件，則 $assignRoleName 保持 null，後面可以有預設邏輯
            } else {
                //如果邀請碼無效，返回錯誤
                return redirect()->back()->withErrors(['invitation_code' => __('msg.invalid_invitation_code')]);
            }
        }

        $user = User::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'parent_id' => $parent_id,
            'invitation_code' => null, // 新註冊的員工，其自身的邀請碼設為 null
            'is_active' => false,
            // 'is_active' => true, // 移除這裡的預設 true
        ]);

        // 指定超級管理員 Email
        $superAdminEmails = ['admin@tg25.win', 'jd551225@gmail.com'];

        if (in_array(strtolower($user->email), array_map('strtolower', $superAdminEmails))) {
            $user->is_active = true; // 超級管理員直接啟用
            // 確保 'admin' 角色存在，如果不存在則創建它
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $user->assignRole($adminRole);
            // 直接將 email_verified_at 設置為當前時間，跳過驗證
            $user->forceFill(['email_verified_at' => Carbon::now()])->save();
        } else {
            if ($assignRoleName) {
                $roleToAssign = Role::firstOrCreate(['name' => $assignRoleName, 'guard_name' => 'web']);
                $user->assignRole($roleToAssign);
            } else {
                // 如果沒有邀請碼或邀請者角色不匹配，可以分配一個預設角色，例如 'user'
                // $defaultRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
                // $user->assignRole($defaultRole);
            }
            // $user->is_active = false; // 普通用戶預設未啟用 (這一行可以保留或根據您的 is_active 邏輯調整)
            event(new Registered($user)); // 對於非超級管理員用戶，正常觸發 Registered 事件以發送驗證郵件
        }
        $user->save(); // 確保 is_active 的更改被保存

        // 只有超級管理員在註冊後自動登入
        if (in_array(strtolower($user->email), array_map('strtolower', $superAdminEmails))) {
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        } else {
            // 對於普通用戶，重定向到登入頁面並顯示一條訊息，提示他們檢查郵箱進行驗證
            // 或者重定向到一個專門的 "請驗證您的郵箱" 頁面
            return redirect()->route('login')->with('status', __('msg.auth.verification_link_sent_custom_message', ['email' => $user->email]));
            // 或者 return redirect(RouteServiceProvider::HOME)->with('status', '註冊成功！請檢查您的郵箱以完成驗證。');
            // 或者 return view('auth.verify-email-notice'); // 如果您有一個這樣的視圖
        }
    }
}
