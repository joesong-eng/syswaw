<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        // dd($user->hasRole('admin'));
        $baseMachineQuery = $this->getAuthorizedMachineQuery($user);

        // 1. 準備場地篩選器
        // 從有權限的機台中，提取不重複的場地
        $authorizedArcadeIds = $baseMachineQuery->clone()->select('arcade_id')->distinct()->pluck('arcade_id');
        $arcades = Arcade::whereIn('id', $authorizedArcadeIds)->orderBy('name')->get();

        // 2. 準備機台相關篩選器
        // **修改這裡：排除 'money_slot' 機台類型**
        $machinesForFilter = $baseMachineQuery->clone()
            ->where('machine_type', '!=', 'money_slot') // <-- 新增：排除紙鈔機
            ->with('arcade:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'arcade_id', 'machine_type', 'owner_id']);

        // **修改這裡：排除 'money_slot' 機台類型**
        $machineTypes = $baseMachineQuery->clone()->select('machine_type')
            ->whereNotNull('machine_type')
            ->where('machine_type', '!=', 'money_slot') // <-- 新增：排除紙鈔機
            ->distinct()->pluck('machine_type');

        // 3. 準備廠商篩選器
        $authorizedOwnerIds = $baseMachineQuery->clone()->select('owner_id')->distinct()->pluck('owner_id');
        $owners = User::whereIn('id', $authorizedOwnerIds)->orderBy('name')->get();


        // **修改這裡：在 generate 方法的查詢中排除 'money_slot' 機台類型**
        $machineQuery = $baseMachineQuery->where('machine_type', '!=', 'money_slot'); // <-- 新增：排除紙鈔機

        if (!empty($filters['arcade_id'])) {
            $machineQuery->where('arcade_id', $filters['arcade_id']);
        }
        if (!empty($filters['machine_id'])) {
            $machineQuery->where('id', $filters['machine_id']);
        }
        if (!empty($filters['machine_type'])) {
            $machineQuery->where('machine_type', $filters['machine_type']);
        }
        if (!empty($filters['owner_id'])) {
            $machineQuery->where('owner_id', $filters['owner_id']);
        }

        $machines = $machineQuery->with(['arcade:id,name', 'owner:id,name'])->get();



        $admin = ($user->hasRole('admin')) ? 'admin' : '';
        return view($admin . '.' . 'reports.index', [
            'machines' => $machines,
            'arcades' => $arcades,
            'machineTypes' => $machineTypes,
            'machinesForFilter' => $machinesForFilter,
            'owners' => $owners,
            'reportData' => session('reportData') ?? [],
            'filters' => session('filters') ?? ['period' => 'last_week']
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'period' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'arcade_id' => 'nullable|exists:arcades,id',
            'machine_id' => 'nullable|exists:machines,id',
            'machine_type' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $filters = $request->only([
            'period',
            'start_date',
            'end_date',
            'arcade_id',
            'machine_id',
            'machine_type',
            'owner_id'
        ]);

        list($startDate, $endDate) = $this->calculateDateRange($filters);

        $user = Auth::user();
        $baseMachineQuery = $this->getAuthorizedMachineQuery($user);

        // **修改這裡：在 generate 方法的查詢中排除 'money_slot' 機台類型**
        $machineQuery = $baseMachineQuery->where('machine_type', '!=', 'money_slot'); // <-- 新增：排除紙鈔機

        if (!empty($filters['arcade_id'])) {
            $machineQuery->where('arcade_id', $filters['arcade_id']);
        }
        if (!empty($filters['machine_id'])) {
            $machineQuery->where('id', $filters['machine_id']);
        }
        if (!empty($filters['machine_type'])) {
            $machineQuery->where('machine_type', $filters['machine_type']);
        }
        if (!empty($filters['owner_id'])) {
            $machineQuery->where('owner_id', $filters['owner_id']);
        }

        $machines = $machineQuery->with(['arcade:id,name', 'owner:id,name'])->get();
        $machineIds = $machines->pluck('id');

        if ($machineIds->isEmpty()) {
            return redirect()->back()->with('reportData', [])
                ->with('filters', $filters)
                ->with('message', '找不到符合條件的機台或沒有數據。');
        }

        // 獲取期末值：在 endDate 或之前的最後一筆記錄
        $latestRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<=', $endDate->endOfDay())
            ->groupBy('machine_id');

        $latestRecords = MachineData::joinSub($latestRecordsSubquery, 'latest', function ($join) {
            $join->on('machine_data.id', '=', 'latest.max_id');
        })
            ->whereIn('machine_data.machine_id', $machineIds)
            ->get()
            ->keyBy('machine_id');

        // 獲取期初值：在 startDate 之前的最後一筆記錄
        $initialRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id')) // 統一使用 MAX(id)
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<', $startDate->startOfDay())
            ->groupBy('machine_id');

        $initialRecords = MachineData::joinSub($initialRecordsSubquery, 'initial', function ($join) {
            $join->on('machine_data.id', '=', 'initial.max_id'); // 統一使用 machine_data.id
        })
            ->whereIn('machine_data.machine_id', $machineIds)
            ->get()
            ->keyBy('machine_id');


        $reportData = [];
        foreach ($machines as $machine) {
            $latest = $latestRecords->get($machine->id);

            // 如果連期末值都找不到，則跳過此機台
            if (!$latest) {
                // 如果您希望顯示所有機台，即使沒有數據，可以將這段註釋掉或處理為 0 值
                continue;
            }

            $initial = $initialRecords->get($machine->id);

            // 如果找不到期初值（即 startDate 之前沒有任何數據），則期初值為 0
            $initialValues = $initial ? $initial->getAttributes() : array_fill_keys(['credit_in', 'assign_credit', 'coin_out', 'settled_credit', 'ball_in', 'ball_out', 'bill_denomination'], 0);

            // 計算增量
            $delta_credit_in = $latest->credit_in - $initialValues['credit_in'];
            if ($delta_credit_in < 0) { // 如果是負數，表示發生重置，取最新值作為該期間的增量
                $delta_credit_in = $latest->credit_in;
            }

            $delta_assign_credit = $latest->assign_credit - $initialValues['assign_credit'];
            if ($delta_assign_credit < 0) { // 同樣處理 assign_credit 的重置
                $delta_assign_credit = $latest->assign_credit;
            }

            $delta_coin_out = $latest->coin_out - $initialValues['coin_out'];
            if ($delta_coin_out < 0) { // 如果是負數，表示發生重置，取最新值作為該期間的增量
                $delta_coin_out = $latest->coin_out;
            }

            $delta_settled_credit = $latest->settled_credit - $initialValues['settled_credit'];
            if ($delta_settled_credit < 0) { // 如果是負數，表示發生重置，取最新值作為該期間的增量
                $delta_settled_credit = $latest->settled_credit;
            }

            // 確保所有值都是數字，避免 null 或非數字導致錯誤
            $coinInputValue = (float)($machine->coin_input_value ?? 0);
            $creditButtonValue = (float)($machine->credit_button_value ?? 0);
            $payoutUnitValue = (float)($machine->payout_unit_value ?? 0);
            $payoutButtonValue = (float)($machine->payout_button_value ?? 0);


            // 計算營收
            $revenue_from_coins = $delta_credit_in * $coinInputValue;
            $revenue_from_assign = $delta_assign_credit * $creditButtonValue;
            $total_revenue = $revenue_from_coins + $revenue_from_assign;

            // 計算成本
            $cost_from_coin_out = $delta_coin_out * $payoutUnitValue;
            $cost_from_settled_credit = $delta_settled_credit * $payoutButtonValue;
            $total_cost = $cost_from_coin_out + $cost_from_settled_credit;


            // 計算淨利
            $net_profit = $total_revenue - $total_cost;

            $reportData[] = [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'arcade_name' => $machine->arcade->name ?? 'N/A',
                'owner_name' => $machine->owner->name ?? 'N/A',
                'machine_type' => $machine->machine_type, // 為了前端顯示或驗證，保留機台類型
                'credit_in_initial' => $initialValues['credit_in'],
                'credit_in_latest' => $latest->credit_in,
                'credit_in_delta' => $delta_credit_in,
                'assign_credit_initial' => $initialValues['assign_credit'],
                'assign_credit_latest' => $latest->assign_credit,
                'assign_credit_delta' => $delta_assign_credit,
                'coin_out_initial' => $initialValues['coin_out'],
                'coin_out_latest' => $latest->coin_out,
                'coin_out_delta' => $delta_coin_out,
                'settled_credit_initial' => $initialValues['settled_credit'],
                'settled_credit_latest' => $latest->settled_credit,
                'settled_credit_delta' => $delta_settled_credit,
                'credit_in_value' => $revenue_from_coins,
                'assign_credit_value' => $revenue_from_assign,
                'total_revenue' => $total_revenue,
                'total_cost' => $total_cost,
                'net_profit' => $net_profit,
            ];
        }

        // 對 reportData 進行排序（例如按機台名稱或ID）
        usort($reportData, function ($a, $b) {
            return $a['machine_name'] <=> $b['machine_name'];
        });

        return redirect()->back()->with('reportData', $reportData)->with('filters', $filters);
    }

    private function calculateDateRange(array $filters): array
    {
        $startDate = null;
        $endDate = null;

        switch ($filters['period']) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday();
                break;
            case 'last_3_days':
                $endDate = Carbon::today();
                $startDate = Carbon::today()->subDays(2); // 包含今天，共3天
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $startDate = Carbon::parse($filters['start_date']);
                $endDate = Carbon::parse($filters['end_date']);
                break;
            default:
                // 預設為上週
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;
        }

        return [$startDate, $endDate];
    }

    private function getAuthorizedMachineQuery(User $user)
    {
        $query = Machine::query();

        if ($user->hasRole('admin')) {
            // 管理員可以查看所有機台
        } elseif ($user->hasRole('machine-owner')) {
            // 機台廠商只能查看自己的機台
            $query->where('owner_id', $user->id);
        } elseif ($user->hasRole('machine-staff')) {
            // 機台員工只能查看所屬場地的機台
            // 假設員工和場地有關聯
            $arcadeIds = $user->arcades()->pluck('id');
            $query->whereIn('arcade_id', $arcadeIds);
        } else {
            // 其他角色，預設看不到任何機台
            $query->whereRaw('0=1'); // 返回空結果
        }

        // 確保只選擇 active 且未刪除的機台
        $query->where('is_active', 1)->whereNull('deleted_at');

        return $query;
    }

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
            'custom' => '指定時段', // 新增 custom 期間
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

        // 4. 廠商描述 (只有在 admin 角色且有指定廠商時才加入)
        $user = Auth::user();
        if ($user->hasRole('admin') && !empty($filters['owner_id'])) {
            $ownerName = User::find($filters['owner_id'])->name ?? '未知廠商';
            $parts[] = " (由「{$ownerName}」擁有)";
        }

        return implode('', $parts) . '的數據報表';
    }
}
