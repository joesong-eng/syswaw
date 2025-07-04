<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineTransaction;
use App\Models\Machine;
use App\Models\Store;
use App\Models\VsStore;
use App\Models\User;
use Auth;

class TransactionQueryController extends Controller
{
    /**
     * Display a listing of the transactions based on filters.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index3(Request $request){
        $user = Auth::user();

        // Base query for machine transactions
        $query = MachineTransaction::query();

        // Filter by owner if the user is a machine owner
        if ($user->hasRole('MachineOwner')) {
            $query->where('owner_id', $user->id);
        }

        // Filter by storeable if the user is a store owner
        if ($user->hasRole('StoreOwner')) {
            $query->whereHas('storeable', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        // Apply additional filters from the request
        if ($request->has('machine_id')) {
            $query->where('machine_id', $request->input('machine_id'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        $transactions = $query->paginate(20);

        return view('transactions.index', compact('transactions'));
    }
    public function index                                                                                                                           (Request $request){
        $user = Auth::user();
        // 1. 獲取當前用戶和子用戶的 ID
        $allOwnerIds = User::where('id', $user->id)
            ->orWhere('parent_id', $user->id)
            ->pluck('id');

        // 2. 撈取機器
        $machineQuery = Machine::whereIn('owner_id', $allOwnerIds)
            ->where('used', '>', 0);

        // 3. 如果有商店篩選條件
        if ($request->filled('storeable_id') && $request->filled('storeable_type')) {
            $machineQuery->where('storeable_id', $request->input('storeable_id'))
                ->where('storeable_type', $request->input('storeable_type'));
        }
        $machines = $machineQuery->get();
        // 4. 撈出符合條件的機器交易
        $transactionQuery = MachineTransaction::query();
        $transactionQuery->whereIn('machine_id', $machines->pluck('machine_id'));

        // 5. 篩選機器 ID
        if ($request->filled('machine_id')) {
            $transactionQuery->where('machine_id', $request->input('machine_id'));
        }

        // 6. 篩選日期範圍
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $transactionQuery->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        } else {
            // 預設條件：當日紀錄
            $transactionQuery->whereDate('created_at', now());
        }

        $transactions = $transactionQuery->paginate(60);

        // 7. 計算統計數據
        $transactionRecords = $transactionQuery->get();
        $firstTransaction = $transactionRecords->first();
        $lastTransaction = $transactionRecords->last();

        $summary = [
            'credit_in' => $lastTransaction ? $lastTransaction->credit_in - $firstTransaction->credit_in : 0,
            'ball_in' => $lastTransaction ? $lastTransaction->ball_in - $firstTransaction->ball_in : 0,
            'ball_out' => $lastTransaction ? $lastTransaction->ball_out - $firstTransaction->ball_out : 0,
        ];

        // 8. 獲取用戶的所有機器和店鋪列表，用於篩選框
        $stores = Store::whereIn('owner_id', $allOwnerIds)->get();

        // 9. 台北當前時間
        date_default_timezone_set('Asia/Taipei');
        $timenow = date('ymd H:i');

        // 10. 返回視圖，並傳遞數據
        return view('transactions.index', compact('transactions', 'summary', 'machines', 'stores', 'timenow'));
    }

    
    
}

