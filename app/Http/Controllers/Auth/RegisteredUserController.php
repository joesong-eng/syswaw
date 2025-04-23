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

class RegisteredUserController extends Controller{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View{
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse{
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

            // dd($formattedNumber);
    
        } catch (NumberParseException $e) {
            return response()->json(['error' => 'Invalid phone number format'], 400);
        }

        $parent_id = null;
        if ($request->filled('invitation_code')) {
            $inviter = User::where('invitation_code', $request->invitation_code)->first();
            if ($inviter && $inviter->hasRole('arcade-owner')) {
                $parent_id = $inviter->id;
            } else {
                //如果邀請碼無效或邀請者不是 arcade-owner，返回錯誤
                return redirect()->back()->withErrors(['invitation_code' => __('msg.invalid_invitation_code')]);
            }
        }
    
        $user = User::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'parent_id' => $parent_id, // 設定 parent_id
        ]);
        event(new Registered($user));
        Auth::login($user);
        return redirect(RouteServiceProvider::HOME);
    }
}
