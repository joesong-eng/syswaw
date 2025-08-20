<?php

namespace App\Http\Controllers;

use App\Models\MachineAuthKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\MachineStatusUpdated;
use App\Events\MachineDataReceived;

class MachineStatusController extends Controller
{
    public function updateStatus(Request $request)
    {
        Log::info('MachineStatusController: updateStatus method entered.');

        // payload = {'chip_id': chip_id, 'status': status}
        // 從 MQTT Payload 中獲取正確的鍵名：chip_id 和 auth_key
        $mqttChipId = $request->input('chip_id');
        $status = $request->input('status'); // 狀態仍然是 status

        Log::info("MachineStatusController: Received data.", [
            'chip_id' => $mqttChipId,
            'status' => $status
        ]);

        // 驗證是否收到必要的識別符
        if (!$mqttChipId) {
            Log::warning('MachineStatusController: chip_id or auth_key is missing from MQTT payload.');
            return response()->json(['error' => 'chip_id and auth_key are required.'], 400);
        }

        // 使用從 MQTT 獲取的 chip_id 和 auth_key 進行資料庫查詢
        // 這樣可以確保設備的合法性，並且認證金鑰是正確的
        Log::info("MachineStatusController: Querying for chip_id: {$mqttChipId}");
        $machineAuth = MachineAuthKey::where('chip_hardware_id', $mqttChipId)->first();

        if ($machineAuth) {
            // 如果找到匹配的記錄，就使用 chip_hardware_id 來廣播事件
            $chipHardwareId = $machineAuth->chip_hardware_id;
            Log::info("MachineStatusController: Auth key and chip ID found.", ['chip_hardware_id' => $chipHardwareId]);
            broadcast(new MachineStatusUpdated($chipHardwareId, $status));
            // event(new  MachineDataReceived($validated));

            Log::info("Status update for chip_id: {$mqttChipId} -> chip_hardware_id: {$chipHardwareId} ({$status}). Event broadcasted.");
            return response()->json(['message' => 'Status update processed and broadcasted with chip_hardware_id.']);
        } else {
            // 如果找不到，只記錄日誌，不廣播事件
            Log::warning("Received status update for an unknown or invalid combination of chip_id and auth_key: {$mqttChipId}");
            return response()->json(['message' => 'Auth key or chip ID not found/invalid.'], 404);
        }
    }
}
