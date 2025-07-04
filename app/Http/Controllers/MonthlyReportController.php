<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyReport;
use App\Models\MonthlyReportDetail;
use App\Models\Arcade;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MonthlyReportController extends Controller
{
    /**
     * 顯示月結報表列表
     */
    public function index()
    {
        // 簡單地獲取所有總報表，按時間倒序排列
        $reports = MonthlyReport::orderBy('period_start', 'desc')->paginate(20);

        return view('monthly_reports.index', compact('reports'));
    }

    public function show(MonthlyReport $report)
    {
        $user = Auth::user();
        $viewData = ['report' => $report, 'view_type' => 'admin', 'details' => collect(), 'breakdown' => collect()];

        if ($user->hasRole('admin')) {
            $viewData['details'] = $report->details()->with('reportable')->get()->groupBy('reportable_type');
            $viewData['view_type'] = 'admin';
        } elseif ($user->hasRole(['arcade-owner', 'arcade-staff'])) {
            $arcadeIds = $user->hasRole('arcade-owner') ? $user->arcades()->pluck('id')->all() : [$user->arcade_id];
            $detailsQuery = $report->details()->where('reportable_type', Arcade::class)->whereIn('reportable_id', $arcadeIds);

            // 我們需要獲取這份報表相關的所有機台，然後按機主分組
            $machineIds = $detailsQuery->clone()->get()->flatMap(fn($d) => json_decode($d->source_data, true)['machine_ids'] ?? [])->unique();
            $machineReports = $this->getMachineBreakdown($report->period_start, $report->period_end, $machineIds->all());

            $viewData['details'] = $detailsQuery->get();
            $viewData['breakdown'] = collect($machineReports)->groupBy('owner_name');
            $viewData['view_type'] = 'arcade_owner';
        } elseif ($user->hasRole(['machine-owner', 'machine-staff'])) {
            $ownerId = $user->hasRole('machine-owner') ? $user->id : $user->parent_id;
            $detailsQuery = $report->details()->where('reportable_type', User::class)->where('reportable_id', $ownerId);

            $machineIds = $detailsQuery->clone()->get()->flatMap(fn($d) => json_decode($d->source_data, true)['machine_ids'] ?? [])->unique();
            $machineReports = $this->getMachineBreakdown($report->period_start, $report->period_end, $machineIds->all());

            $viewData['details'] = $detailsQuery->get();
            $viewData['breakdown'] = collect($machineReports)->groupBy('arcade_name');
            $viewData['view_type'] = 'machine_owner';
        }

        return view('monthly_reports.show', $viewData);
    }

    /**
     * 輔助函式：現在它會真正地計算明細
     */
    private function getMachineBreakdown($startDate, $endDate, $machineIds): array
    {
        // 為了避免重複程式碼，這個邏輯應該從 GenerateMonthlyReports 中抽離到一個 Service Class
        // 這裡我們暫時複製黏貼核心計算邏輯
        $machines = Machine::whereIn('id', $machineIds)->with(['arcade:id,name', 'owner:id,name'])->get();
        if ($machines->isEmpty()) return [];

        $latestRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))->whereIn('machine_id', $machineIds)->where('timestamp', '<=', $endDate)->groupBy('machine_id');
        $latestRecords = MachineData::joinSub($latestRecordsSubquery, 'latest', fn($j) => $j->on('machine_data.id', '=', 'latest.max_id'))->get()->keyBy('machine_id');

        $initialRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))->whereIn('machine_id', $machineIds)->where('timestamp', '<', $startDate)->groupBy('machine_id');
        $initialRecords = MachineData::joinSub($initialRecordsSubquery, 'initial', fn($j) => $j->on('machine_data.id', '=', 'initial.max_id'))->get()->keyBy('machine_id');

        $breakdownData = [];
        foreach ($machines as $machine) {
            $latest = $latestRecords->get($machine->id);
            if (!$latest || $latest->timestamp < $startDate) continue;

            $initial = $initialRecords->get($machine->id);
            $initialValues = $initial ? $initial->getAttributes() : array_fill_keys(['credit_in', 'assign_credit', 'coin_out'], 0);

            $delta_credit_in = $latest->credit_in - $initialValues['credit_in'];
            $delta_assign_credit = $latest->assign_credit - $initialValues['assign_credit'];
            $delta_coin_out = $latest->coin_out - $initialValues['coin_out'];

            $total_revenue = ($delta_credit_in * (float)$machine->coin_input_value) + ($delta_assign_credit * (float)$machine->credit_button_value);
            $total_cost = $delta_coin_out * (float)$machine->payout_unit_value;
            $net_profit = $total_revenue - $total_cost;

            $machine_owner_share = $net_profit * ((float)$machine->revenue_split / 100);

            $breakdownData[] = [
                'machine_name' => $machine->name,
                'arcade_name' => $machine->arcade->name ?? 'N/A',
                'owner_name' => $machine->owner->name ?? 'N/A',
                'net_profit' => $net_profit,
                'machine_owner_share' => $machine_owner_share,
            ];
        }
        return $breakdownData;
    }
}
