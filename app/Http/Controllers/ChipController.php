<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChipKey;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ChipController extends Controller
{
    // 新增 Chip Key
    public function store(Request $request)
    {
        $token = bin2hex(random_bytes(16)); // 產生隨機 API token
        ChipKey::create([
            'key' => $token,
            'expires_at' => now()->addHours(3),
            'owner_id' => Auth::id(),
            'created_by' => Auth::id(),
            'status' => 'unused',
            'printed' => false,
        ]);

        return redirect()->back()->with('success', 'Chip Key created successfully.');
    }
    public function index(Request $request)
    {
        $this->clear_expire_key();
        $user = Auth::user();
        $rolename = Auth::user()->getRoleNames()->first();
        $filter = $request->input('filter', 'all');
        $query = ChipKey::query(); // 構建查詢
        if ($rolename == 'admin') {
            // 管理員可以查看所有 ChipKey
        } elseif (in_array($rolename, ['arcade-owner', 'machine-owner'])) {
            $query->where('owner_id', $user->id);
        } elseif (in_array($rolename, ['arcade-staff', 'machine-staff'])) {
            $query->where('owner_id', $user->parent->id);
        }
        if (in_array($filter, ['used', 'unused'])) { // 根據篩選條件過濾
            $query->where('status', $filter);
        }
        $chipKeys = $query->get();
        return view('chip.index', compact('chipKeys', 'filter'));
    }


    private function clear_expire_key()
    { // 刪除未使用且過期的金鑰
        $deleted = ChipKey::where('status', 'unused')
            ->where('expires_at', '<', now())
            ->delete();
        // 記錄一下操作結果
        \Log::info("已刪除 {$deleted} 個過期且未使用的金鑰");
    }

    // 顯示新增 Chip Key 表單
    public function create()
    {
        return view('chip.create');
    }



    // 刪除 Chip Key
    public function destroy($id)
    {
        $chipKey = ChipKey::findOrFail($id);
        $chipKey->delete();

        return redirect()->route('arcade.chips.index')->with('success', 'Chip Key 刪除成功'); // 修正路由名稱
    }

    public function printKeys(Request $request)
    {
        // 取得前端勾選的金鑰 id 陣列
        $selectedIds = $request->input('selected_ids', []);
        // 根據 id 取得對應的機台資料
        $chipKeys = ChipKey::whereIn('id', $selectedIds)->get();
        // 將資料傳到列印頁面，由 Blade 利用 QrCode 產生 QR code（md5 後的金鑰）
        return view('chip.print_keys', compact('chipKeys'));
    }
}
