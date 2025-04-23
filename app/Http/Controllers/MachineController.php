<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\ChipKey;
use App\Models\Arcade;
use App\Models\VsArcade;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            // 如果是 admin，顯示所有機器、Arcade 和 VsArcade
            $machines = Machine::all();
            // $machines = Machine::withTrashed()->get();
            $arcades = Arcade::all();
            $users = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['arcade-owner', 'machine-owner','admin']);
            })->get();
        } else {
            // 如果不是 admin，根據用戶的身份篩選顯示的機器、Arcade 和 VsArcade
            $machines = Machine::where('owner_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->get();
            $arcades = Arcade::where('owner_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->get();
            // $vsArcades = VsArcade::where('owner_id', $user->id)
            //     ->orWhere('created_by', $user->id)
            //     ->get();
            $users = collect([$user]); // 只返回當前用戶
        }
        foreach ($arcades as $arcade) {
            $arcade->authenticatable_type='arcade';
        }
        // foreach ($vsArcades as $vsarcade) {
        //     $vsarcade->authenticatable_type='vsArcade';
        // }
        
        return view('machine.index', compact('machines', 'arcades',  'users'));
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
    public function store(Request $request)
    {
        // 驗證表單數據
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_type' => 'required|string|in:normally,pachinko,claw_machine,beat_em_up,racing_game,light_gun_game,dance_game,basketball_game,air_hockey,slot_machine,light_and_sound_game,labyrinth_game,flight_simulator,punching_machine,water_shooting_game,stacker_machine,mini_golf_game,interactive_dance_game,electronic_shooting_game,giant_claw_machine,arcade_music_game',
            'arcade_id' => 'required|exists:arcades,id',
            'owner_id' => 'required|exists:users,id',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                Machine::create([
                    'name' => $validated['name'],
                    'machine_type' => $validated['machine_type'],
                    'arcade_id' => $validated['arcade_id'],
                    'owner_id' => $validated['owner_id'],
                    'created_by' => auth()->id(), // 自動設置為當前用戶
                    'is_active' => false, // 默認值
                    'revenue_split' => 0.45, // 默認值
                ]);
            });

            return redirect()->back()->with('success', __('msg.machine_added_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('msg.error_adding_machine') . ': ' . $e->getMessage());
        }

        // return redirect()->route('arcade.index')->with('success', 'Arcade created successfully.');
    }

    public function edit(Machine $machine)
    {
        $arcades = Arcade::all();
        return view('components.modal.machine-edit', compact('machine', 'arcades'));
    }

    public function update(Request $request, Machine $machine)
    {
        $res=$request->validate([
            'name' => 'required|string|max:255',
            'storeable_id' => 'required|integer',
            'storeable_type' => 'required|string|in:App\Models\Arcade,App\Models\VsArcade',
            'owner_id' => 'required|integer|exists:users,id', // 添加 owner_id 的驗證規則
            'machine_type' => 'required|string|max:255',
            // 其他驗證規則
        ]);

        $machine->update([
            'name' => $request->name,
            'storeable_id' => $request->storeable_id,
            'storeable_type' => $request->storeable_type,
            'owner_id' => $request->owner_id, // 更新 owner_id
            'machine_type' => $request->machine_type, // 添加 machine_type
            // 其他字段
        ]);

        return redirect()->route('machines.index')->with('success', 'Machine updated successfully.');
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
        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }
}