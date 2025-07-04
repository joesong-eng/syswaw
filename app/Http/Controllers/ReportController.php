<?php

namespace App\Http\Controllers; // <--- 確保命名空間是正確的

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // 確保引用了基底 Controller
use App\Models\Arcade;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\MachineData;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $baseMachineQuery = $this->getAuthorizedMachineQuery($user);

        // 1. 準備場地篩選器
        // 從有權限的機台中，提取不重複的場地
        $authorizedArcadeIds = $baseMachineQuery->clone()->select('arcade_id')->distinct()->pluck('arcade_id');
        $arcades = Arcade::whereIn('id', $authorizedArcadeIds)->orderBy('name')->get();

        // 2. 準備機台相關篩選器
        $machinesForFilter = $baseMachineQuery->clone()
            ->with('arcade:id,name') // 預載入關聯的場地名稱
            ->orderBy('name')
            ->get(['id', 'name', 'arcade_id', 'machine_type', 'owner_id']); // <<< 把需要的欄位都加進來
        $machineTypes = $baseMachineQuery->clone()->select('machine_type')
            ->whereNotNull('machine_type')->distinct()->pluck('machine_type')
            ->mapWithKeys(fn($type) => [$type => __('msg.' . $type, [], $user->locale)])
            ->all();

        // 3. 準備廠商(機主)篩選器
        // 從有權限的機台中，提取不重複的廠商
        $authorizedOwnerIds = $baseMachineQuery->clone()->select('owner_id')->distinct()->pluck('owner_id');
        $owners = User::whereIn('id', $authorizedOwnerIds)->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.index', compact(
            'arcades',
            'machineTypes',
            'machinesForFilter',
            'owners'
        ));
    }

    /**
     * 根據篩選條件產生報表數據
     */
    /**
     * 根據篩選條件產生報表數據 (最終修正版)
     */
    public function generate(Request $request): RedirectResponse
    {
        // 1. 驗證請求數據
        $validated = $request->validate([
            'period' => 'required|string|in:today,yesterday,last_3_days,this_week,last_week,this_month,last_month',
            'arcade_id' => 'nullable|integer|exists:arcades,id',
            'machine_type' => 'nullable|string',
            'machine_id' => 'nullable|integer|exists:machines,id',
            'owner_id' => 'nullable|integer|exists:users,id',
        ]);

        // 2. 根據 'period' 計算日期範圍
        [$startDate, $endDate] = $this->calculateDateRange($validated['period']);

        // 3. 構建基礎查詢，先應用權限過濾
        $machineQuery = $this->getAuthorizedMachineQuery(Auth::user());

        // 再應用前端傳來的篩選條件
        $machineQuery
            ->when($validated['arcade_id'] ?? null, fn($q, $id) => $q->where('arcade_id', $id))
            ->when($validated['machine_type'] ?? null, fn($q, $type) => $q->where('machine_type', $type))
            ->when($validated['machine_id'] ?? null, fn($q, $id) => $q->where('id', $id))
            ->when($validated['owner_id'] ?? null, fn($q, $id) => $q->where('owner_id', $id));

        $machines = $machineQuery->with(['arcade:id,name', 'owner:id,name'])->get();
        $machineIds = $machines->pluck('id');

        if ($machineIds->isEmpty()) {
            return back()->with('error', '在指定的篩選條件下找不到任何機台。')->withInput();
        }

        // =================================================================
        // <<< 健壯的期初期末值獲取邏輯 >>>

        // 4. 獲取期末值：在 endDate 或之前的最後一筆記錄
        $latestRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<=', $endDate->endOfDay())
            ->groupBy('machine_id');

        $latestRecords = MachineData::joinSub($latestRecordsSubquery, 'latest', function ($join) {
            $join->on('machine_data.id', '=', 'latest.max_id');
        })->get()->keyBy('machine_id');

        // 5. 獲取期初值：在 startDate 之前的最後一筆記錄
        $initialRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<', $startDate->startOfDay())
            ->groupBy('machine_id');

        $initialRecords = MachineData::joinSub($initialRecordsSubquery, 'initial', function ($join) {
            $join->on('machine_data.id', '=', 'initial.max_id');
        })->get()->keyBy('machine_id');

        // =================================================================

        // 6. 計算增量並生成報表
        $reportData = [];
        foreach ($machines as $machine) {
            $latest = $latestRecords->get($machine->id);

            // 如果連期末值都找不到（即 endDate 之前沒有任何數據），則跳過此機台
            if (!$latest) {
                continue;
            }

            $initial = $initialRecords->get($machine->id);

            // 如果找不到期初值（即 startDate 之前沒有任何數據），則期初值為 0
            $initialValues = $initial ? $initial->getAttributes() : array_fill_keys(['credit_in', 'assign_credit', 'coin_out', 'ball_in', 'ball_out', 'bill_denomination'], 0);

            // 計算增量
            $delta_credit_in = $latest->credit_in - $initialValues['credit_in'];
            $delta_assign_credit = $latest->assign_credit - $initialValues['assign_credit'];
            $delta_coin_out = $latest->coin_out - $initialValues['coin_out'];

            // 計算營收
            $revenue_from_coins = $delta_credit_in * (float)$machine->coin_input_value;
            $revenue_from_assign = $delta_assign_credit * (float)$machine->credit_button_value;
            $total_revenue = $revenue_from_coins + $revenue_from_assign;

            // 計算成本
            $total_cost = $delta_coin_out * (float)$machine->payout_unit_value;

            // 計算淨利
            $net_profit = $total_revenue - $total_cost;

            $reportData[] = [
                'machine_name' => $machine->name,
                'arcade_name' => $machine->arcade->name ?? 'N/A',
                'owner_name' => $machine->owner->name ?? 'N/A',
                'credit_in_value' => $revenue_from_coins,
                'assign_credit_value' => $revenue_from_assign,
                'total_revenue' => $total_revenue,
                'total_cost' => $total_cost,
                'net_profit' => $net_profit,
            ];
        }

        // 7. 將結果閃存到 Session 並返回
        $reportTitle = $this->generateReportTitle($validated);
        $filterContext = [];
        if (!empty($validated['arcade_id'])) {
            $filterContext['arcade_name'] = Arcade::find($validated['arcade_id'])->name ?? null;
        }
        if (!empty($validated['owner_id'])) {
            $filterContext['owner_name'] = User::find($validated['owner_id'])->name ?? null;
        }

        return back()
            ->with('filters', $validated)
            ->with('reportData', $reportData)
            ->with('reportTitle', $reportTitle)
            ->with('filterContext', $filterContext)
            ->with('dateRange', ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString()])
            ->withInput();
    }

    /**
     * 新增：根據登入用戶的角色，返回一個已應用權限過濾的 Machine 查詢構建器
     */
    private function getAuthorizedMachineQuery(User $user)
    {
        $query = Machine::query();

        if ($user->hasRole(['arcade-owner', 'arcade-staff'])) {
            $arcadeIds = $user->hasRole('arcade-owner')
                ? Arcade::where('owner_id', $user->id)->pluck('id')
                : [$user->arcade_id]; // 假設員工的 arcade_id 欄位直接關聯場地
            return $query->whereIn('arcade_id', $arcadeIds);
        }

        // <<< 關鍵修正：使用 'machine-owner' 和 'machine-staff' >>>
        if ($user->hasRole(['machine-owner', 'machine-staff'])) {
            $ownerId = $user->hasRole('machine-owner') ? $user->id : $user->parent_id;
            return $query->where('owner_id', $ownerId);
        }

        // 如果是 Admin 或其他未定義的角色，則返回無限制的查詢
        return $query;
    }

    /**
     * 輔助函式：根據字串計算日期範圍
     */
    private function calculateDateRange(string $period): array
    {
        // ... (這個方法保持不變) ...
        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()],
            'last_3_days' => [Carbon::today()->subDays(2), Carbon::today()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::today(), Carbon::today()],
        };
    }

    /**
     * 新增：輔助函式，根據篩選條件生成人類可讀的標題
     */
    private function generateReportTitle(array $filters): string
    {
        $periodMap = [
            'today' => '今天',
            'yesterday' => '昨天',
            'last_3_days' => '過去3天',
            'this_week' => '本週',
            'last_week' => '上週',
            'this_month' => '本月',
            'last_month' => '上月',
        ];

        $parts = [];

        // 1. 時間描述
        $parts[] = $periodMap[$filters['period']] ?? '指定時段';

        // 2. 場地描述
        if (!empty($filters['arcade_id'])) {
            $arcadeName = Arcade::find($filters['arcade_id'])->name ?? '未知場地';
            $parts[] = "的「{$arcadeName}」";
        } else {
            $parts[] = '的所有場地';
        }

        // 3. 機台/類型描述
        if (!empty($filters['machine_id'])) {
            $machineName = Machine::find($filters['machine_id'])->name ?? '未知機台';
            $parts[] = "的「{$machineName}」機台";
        } elseif (!empty($filters['machine_type'])) {
            // 假設翻譯鍵是 'msg.pinball' 等
            $machineTypeName = __('msg.' . $filters['machine_type']);
            $parts[] = "的所有「{$machineTypeName}」";
        } else {
            $parts[] = '的所有機台';
        }

        // 4. 廠商描述 (只有在 admin 且有選擇時才加入)
        if (Auth::user()->hasRole('admin') && !empty($filters['owner_id'])) {
            $ownerName = User::find($filters['owner_id'])->name ?? '未知廠商';
            $parts[] = "（廠商: {$ownerName}）";
        }

        // 5. 結尾
        $parts[] = '的統計報表';

        return implode('', $parts);
    }
}
