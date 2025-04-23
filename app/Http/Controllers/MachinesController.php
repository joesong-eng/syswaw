<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\ChipKey;
use App\Models\Arcade;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // 引入 DB facade
class MachinesController extends Controller{
    public function index(Request $request) {
        $user = Auth::user();
        $queryMachines = Machine::query();
        $queryArcades = Arcade::query();
        $queryUsers = User::query();
        
        if ($user->hasRole('admin')) {
            // 如果是 admin，顯示所有機器、Arcade 和用戶
            $machines = $queryMachines->with('chip')->get(); // 預加載 chip 關聯
            $arcades = $queryArcades->get();
            $users = $queryUsers->whereHas('roles', function($query) {
                $query->whereIn('name', ['arcade-owner', 'machine-owner', 'admin']);
            })->get();
            $returnurl = 'admin.machine.index';
        } elseif ($user->hasRole(['arcade-owner', 'machine-owner'])) {
            // 如果是 arcade 或 machine 擁有者，查看自己擁有的
            $machines = $queryMachines->where('owner_id', $user->id)
                                      ->orWhere('created_by', $user->id)
                                      ->with('chip') // 預加載 chip 關聯
                                      ->get();
            $arcades = $queryArcades->where('owner_id', $user->id)
                                    ->orWhere('created_by', $user->id)
                                    ->get();
            $users = collect([$user]);
            $returnurl = 'machine.index';
        } elseif ($user->hasRole(['arcade-staff', 'machine-staff'])) {
            // 如果是 arcade-staff 或 machine-staff，查看上級擁有的
            $machines = $queryMachines->where('owner_id', $user->parent->id)
                                      ->with('chip') // 預加載 chip 關聯
                                      ->get();
            $arcades = $queryArcades->where('owner_id', $user->parent->id)->get();
            $users = collect([$user]);
            $returnurl = 'machine.index';
        } else {
            // 其他角色無權查看
            abort(403, 'Unauthorized action.');
        }
        
        $arcades->each(function ($arcade) {
            $arcade->authenticatable_type = 'arcade';
        });
        
        return view($returnurl, compact('machines', 'arcades', 'users'));
    }
    public function addMachine(Request $request){
        //驗證請求資料
        $validated = $request->validate([
            'machine_id' => 'required|string|max:8|regex:/^[a-zA-Z0-9]+$/', 
            'token' => 'required|string|exists:machines,token', // 金鑰必須存在於 machines 表
            'mname' => 'nullable|string',
            'machine_type' => 'nullable|string',
            'revenue_split' => 'required|numeric|min:0.1|max:0.95', // 分成比例必須在 0.1 到 0.95 之間
            'owner_id' => 'required|exists:users,id', // 機主 ID 必須有效
        ]);
        // 查找機器
        $machine = Machine::where('token', $validated['token'])->first();
        $status = $machine->status == 'Normal' ? 'Normal':'First Time' ;
        if (!$machine) {
            return redirect()->back()->withErrors(['token' => '該金鑰對應的機器不存在']);
        }

        // 檢查金鑰是否已經被使用
        if ($machine->used) {
            return redirect()->back()->withErrors(['token' => '該金鑰已經被使用']);
        }
        $machine->update([
            'machine_id' => $validated['machine_id'],
            'machine_type' => $validated['machine_type'] ?? $machine->machine_type,
            'name' => $validated['mname']?? $machine->name,
            'owner_id' => $validated['owner_id'],
            'revenue_split' => $validated['revenue_split'],
            'status' => ['status_key' => $status],
            'is_active' => true, // 啟用機器
            'used' => true, // 標記為已使用
            'updated_at' => now(),
        ]);


        return redirect()->back()->with('success', '機器已成功新增');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name'          => 'nullable|string',
            'chipKey'       => 'required|string', // 金鑰必須存在於 machines 表
            'machine_type'  => 'nullable|string',
            'revenue_split' => 'required|numeric|min:0.1|max:0.95', // 分成比例必須在 0.1 到 0.95 之間
            'owner_id'      => 'required|exists:users,id', // 機主 ID 必須有效
            'arcade_id'     => 'required|exists:arcades,id',
        ]);
        try {
            DB::transaction(function () use ($validated) {
                $machineData = [
                    'name' => $validated['name'],
                    'arcade_id' => $validated['arcade_id'],
                    'owner_id' => $validated['owner_id'],
                    'created_by' => Auth::user()->id,
                    'machine_type' => $validated['machine_type'],
                    'is_active' => false,
                    'revenue_split' => $validated['revenue_split'],
                ];
                if (!empty($validated['chipKey'])) {
                    $chipKey = ChipKey::where('key', $validated['chipKey'])->firstOrFail();
                    
                    // 检查芯片密钥是否已被使用
                    if ($chipKey->status === 'used') {
                        throw new \Exception(__('msg.chip_key_already_used'));
                    }
    
                    $machineData['chip_id'] = $chipKey->id;
                    $chipKey->update(['status' => 'used']);
                }
                Machine::create($machineData);
            });
            return redirect()->back()->with('success', __('msg.machine_added_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('msg.error_adding_machine') . ': ' . $e->getMessage());
        }
    }

    public function edit(Machine $machine)
    {
        $arcades = Arcade::all();
        //@machine->chip_id去找到對應的chip_key
        $chipKey = ChipKey::where('id', $machine->chip_id)->first();
        //把chip_key加進$machine裡面
        $machine->key = $chipKey;
        return view('components.modal.machine-edit', compact('machine', 'arcades'));
    }

    // public function update(Request $request,$id)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'machine_type' => 'required|string|in:normally,pachinko,claw_machine,beat_em_up,racing_game,light_gun_game,dance_game,basketball_game,air_hockey,slot_machine,light_and_sound_game,labyrinth_game,flight_simulator,punching_machine,water_shooting_game,stacker_machine,mini_golf_game,interactive_dance_game,electronic_shooting_game,giant_claw_machine,arcade_music_game',
    //         'arcade_id' => 'required|exists:arcades,id',
    //         'owner_id' => 'required|exists:users,id',
    //     ]);
    //     $machine = Machine::findOrFail($id);
    //     try {
    //         DB::transaction(function () use ($validated, $id) {
    //             // $machine = Machine::findOrFail($id);
    //             $machine->update([
    //                 'name' => $validated['name'],
    //                 'machine_type' => $validated['machine_type'],
    //                 'arcade_id' => $validated['arcade_id'],
    //                 'owner_id' => $validated['owner_id'],
    //             ]);
    //         });
    //         return redirect()->back()->with('success', __('msg.machine_updated_successfully'));
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage());
    //     }
    // }

    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'chipkey' => 'nullable|string', // 只在未綁定時有效
            'machine_type' => 'nullable|string',
            'revenue_split' => 'required|numeric|min:0.1|max:0.95',
            'owner_id' => 'required|exists:users,id', // 驗證但不允許修改
            'arcade_id' => 'required|exists:arcades,id',
        ]);
    
        try {
            DB::transaction(function () use ($validated, $machine) {
                // 準備更新數據
                $machineData = [
                    'name' => $validated['name'],
                    'arcade_id' => $validated['arcade_id'],
                    'machine_type' => $validated['machine_type'],
                    'revenue_split' => $validated['revenue_split'],
                    // owner_id 不允許修改，保持原值
                    'owner_id' => $machine->owner_id,
                ];
    
                // 處理 chipkey（只在未綁定時允許設置）
                if (empty($machine->chip_id) && !empty($validated['chipkey'])) {
                    $chipKey = ChipKey::where('key', $validated['chipkey'])->first();
                    if (!$chipKey) {
                        // 如果 chipkey 不存在，創建新的
                        $chipKey = ChipKey::create([
                            'key' => $validated['chipkey'],
                            'expires_at' => now()->addYear(),
                            'owner_id' => Auth::id(),
                            'created_by' => Auth::id(),
                            'status' => 'used',
                        ]);
                    } elseif ($chipKey->status === 'used') {
                        throw new \Exception(__('msg.chip_key_already_used'));
                    } else {
                        $chipKey->update(['status' => 'used']);
                    }
                    $machineData['chip_id'] = $chipKey->id;
                }
    
                // 更新機器
                $machine->update($machineData);
            });
    
            return redirect()->back()->with('success', __('msg.machine_updated_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage());
        }
    }
    public function toggleActive(Request $request, $id){
        $machine = Machine::findOrFail($id);
        $machine->is_active = $request->input('is_active', 0);
        $machine->save();
        return redirect()->back()->with('success', 'Machine activated successfully.');

    }
    public function activate(Machine $machine){
        $machine->update(['is_active' => true]);
        // return redirect()->back()->with('success', 'Machine activated successfully.');
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();
        return redirect()->route('machine.index')->with('success', 'Machine deleted successfully.');
    }
}