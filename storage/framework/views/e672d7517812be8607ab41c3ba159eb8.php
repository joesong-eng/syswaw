<?php $__env->startSection('content'); ?>
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 w-full">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg ">
            <!-- 頁首 + 控制按鈕區 -->
            <div class="flex flex-wrap items-center justify-between mb-2 px-6 pt-6 w-full">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    <?php echo e(__('數據伺服器')); ?>

                </h2>
                <div id="btn-ctrl" class="flex flex-col items-end gap-2 ">
                    <div class="flex flex-wrap gap-2"><!-- 按鈕群組 -->
                        <button id="start-tcp" onclick="triggerRedis('start')"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-green-500">
                            <?php echo e(__('msg.start')); ?>

                        </button>
                        <button id="stop-tcp" onclick="triggerRedis('stop')"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-red-500">
                            <?php echo e(__('msg.stop')); ?>

                        </button>
                        <button id="restart-tcp" onclick="triggerRedis('restart')"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-blue-500">
                            <?php echo e(__('msg.restart')); ?>

                        </button>
                        <button id="status-tcp" onclick="triggerRedis('status')"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-yellow-500">
                            <?php echo e(__('msg.status')); ?>

                        </button>
                    </div>
                </div>
                <div class="text-sm text-right mt-0 w-full flex flex-col items-end">
                    <div id="redis_msg" class="mt-1 text-blue-600 dark:text-blue-400"></div>
                    <div id="redis_status" class="ms-1 mt-1 text-gray-700 dark:text-gray-200 font-medium">
                        <?php if($status === 'running'): ?>
                            已啟動 ✅
                        <?php elseif($status === 'stopped'): ?>
                            已停止 ❌
                        <?php else: ?>
                            目前狀態：未知
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Redis 狀態訊息區 -->
            </div>
            <!-- WebSocket 測試（預設隱藏） -->
            <div class="hidden websock_test space-y-2">
                <p id="Reverb_status" class="text-gray-800 dark:text-gray-200">連線中...</p>
                <p id="Reverb_msg" class="text-gray-800 dark:text-gray-200">尚無訊息</p>
                <div class="text-right space-x-2">
                    <?php if (isset($component)) { $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.secondary-button','data' => ['onclick' => 'triggerEvent()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('secondary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'triggerEvent()']); ?>Trigger Event <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $attributes = $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $component = $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['onclick' => 'triggerBroadcast()','style' => 'background-color: cadetblue;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'triggerBroadcast()','style' => 'background-color: cadetblue;']); ?>Trigger
                        Broadcast <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                </div>
            </div>
            
            <div class="container mx-auto px-2 pb-3 mt-4"> 
                <label for="machineIds" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Machine IDs（逗號分隔）:
                </label>
                <input type="text" name="machineIds" id="machineIds"
                    class="p-2 border rounded-md w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    placeholder="例如：1,2,3">

                <div class="mt-2 text-right">
                    <button id="capture-data-btn"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('開門抓取數據')); ?>

                    </button>
                </div>
                <form id="filterForm" action="<?php echo e(route('admin.tcp-server')); ?>" method="GET"
                    class="flex flex-wrap items-center gap-4">
                    
                    <div>
                        <label for="arcade_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('msg.arcade')); ?> / 店家:
                        </label>
                        <select name="arcade_id" id="arcade_id"
                            class="border rounded-md px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all"><?php echo e(__('msg.all')); ?> / 全部</option>
                            <?php $__currentLoopData = $arcades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arcade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($arcade->id); ?>"
                                    <?php echo e((string) $filterArcadeId === (string) $arcade->id ? 'selected' : ''); ?>>
                                    <?php echo e($arcade->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('msg.machine')); ?> / 機器:
                        </label>
                        
                        
                        <select name="machine_id" id="machine_id"
                            class="border rounded-md px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all"><?php echo e(__('msg.all')); ?> / 全部</option>
                            <?php $__currentLoopData = $machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if(!$filterArcadeId || $filterArcadeId === 'all' || (string) $filterArcadeId === (string) $machine->arcade_id): ?>
                                    <option value="<?php echo e($machine->id); ?>"
                                        <?php echo e((string) $filterMachineId === (string) $machine->id ? 'selected' : ''); ?>>
                                        <?php echo e($machine->name); ?> (店舖: <?php echo e($machine->arcade->name ?? '未知'); ?>)
                                        
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                </form>


            </div>
            
            <div id="transactions-list" class="container mx-auto px-2 pb-3">
                <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">抓取數據歷史記錄</h2>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-x-auto"> 
                    <div
                        class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 p-2 min-w-max">
                        
                        <div class="w-[5%] text-center">ID</div> 
                        <div class="w-[10%] text-center">店舖家</div>
                        <div class="w-[15%] text-center">機器名稱</div>
                        <div class="w-[15%] text-center">晶片 ID</div> 
                        <div class="w-[8%] text-center">進球數</div>
                        <div class="w-[8%] text-center">出球數</div>
                        <div class="w-[8%] text-center">投入金額</div>
                        <div class="w-[15%] text-center">抓取時間</div>
                        <div class="w-[16%] text-center">Token</div> 
                    </div>

                    <div class="overflow-y-auto max-h-[calc(100vh-400px)] sm:max-h-[calc(100vh-350px)]">
                        
                        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div
                                class="flex items-center my-2 mx-0 border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 p-2 min-w-max">
                                <div class="w-[5%] text-center">
                                    <?php echo e(($records->currentPage() - 1) * $records->perPage() + $loop->iteration); ?></div>
                                
                                <div class="w-[10%] text-center"><?php echo e($record->machine->arcade->name ?? '未知店舖'); ?></div>
                                <div class="w-[15%] text-center"><?php echo e($record->machine->name ?? '未知機器'); ?></div>
                                <div class="w-[15%] text-center break-words"><?php echo e($record->chip_id); ?></div>
                                
                                <div class="w-[8%] text-center"><?php echo e($record->ball_in); ?></div>
                                <div class="w-[8%] text-center"><?php echo e($record->ball_out); ?></div>
                                <div class="w-[8%] text-center"><?php echo e($record->credit_in); ?></div>
                                <div class="w-[15%] text-center">
                                    <?php echo e($record->timestamp ? \Carbon\Carbon::parse($record->timestamp)->format('Y-m-d H:i:s') : 'N/A'); ?>

                                </div>
                                <div class="w-[16%] text-center break-words"><?php echo e($record->token); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                暫無歷史數據記錄。
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="mt-4">
                    <?php echo e($records->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initialStatus = <?php echo json_encode($status, 15, 512) ?>;
            updateRedisButtons(initialStatus); // ← 頁面載入時即刻設定
            if (window.Echo) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    document.getElementById('Reverb_status').innerText = '✅ 已連線 Reverb';
                });
                console.log('[Reverb] Echo 初始化完成');
                window.Echo.channel('channel-Reverb')
                    .listen('.event-Reverb', (e) => {
                        console.log('📡 廣播接收:', e);
                        document.getElementById('Reverb_msg').innerText = `✅ 收到：${e.message}`;
                        const initialStatus = <?php echo json_encode($status, 15, 512) ?>;
                        console.log(initialStatus);
                        updateRedisButtons(initialStatus); // ← 頁面載入時即刻設定
                        hideLoadingOverlay();
                    });
            } else {
                console.error('❌ window.Echo 尚未初始化');
            }
        });

        window.triggerBroadcast = function() {
            const postData = {
                action: 'By Broadcast'
            };
            showLoadingOverlay();
            fetch('/admin/ethereal/broadcast', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(postData)
            }).then(response => response.json()).then(data => console.log('Response:', data)).catch(error => console
                .error('Error:', error));
        }
        window.triggerEvent = function() {
            showLoadingOverlay();
            const postData = {
                action: 'By Event'
            };
            fetch('/admin/ethereal/event', { //不會直接返回值,就只是廣播出去,
                    method: 'POST', // 明确指定 POST
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => console.log('Response:', data))
                .catch(error => console.error('Error:', error));
        }

        const xstate = {
            'stopped': '已停止 ❌',
            'running': '已啟動 ✅',
            'restarting': '已重新啟動 ✅'
        }
        document.addEventListener('DOMContentLoaded', () => {
            window.Echo.channel('redis-status-channel')
                .listen('.tcp.status.updated', (e) => {
                    console.log('✅ [指定事件] 收到: ', e);
                    document.getElementById('redis_status').innerText = xstate[e.status] || '未知狀態';
                    updateRedisButtons(e.status); // ← 加這行
                    document.getElementById('redis_msg').innerText =
                        `TCP 狀態：${e.status}，動作：${e.action}`; // 修改顯示內容
                    hideLoadingOverlay();

                })
        })
        const actionFeedback = {
            start: '啟動中...',
            stop: '停止中...',
            restart: '重啟中...',
            status: '查詢狀態中...'
        };

        window.triggerRedis = function(status) {
            showLoadingOverlay();
            // 即時顯示動作提示
            document.getElementById('redis_msg').innerText = actionFeedback[status] || '執行中...';
            const postData = {
                action: status
            };
            fetch('/admin/ethereal/redis_ctrl', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response:', data);
                    // 可選：這裡不要再處理顯示，由 Echo 廣播更新狀態
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('redis_msg').innerText = `❌ 請求失敗：${error.message}`;
                });
        }

        function updateRedisButtons(status) {
            document.getElementById('start-tcp').disabled = (status === 'running');
            document.getElementById('stop-tcp').disabled = (status === 'stopped');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... 你現有的抓取指令 JavaScript 程式碼 ...
            const captureDataBtn = document.getElementById('capture-data-btn');
            const machineIdsInput = document.getElementById('machineIds');
            const redisMsgDiv = document.getElementById('redis_msg'); // 用來顯示訊息，你現有的元素

            if (captureDataBtn && machineIdsInput) {
                captureDataBtn.addEventListener('click', function() {
                    const machineIdsString = machineIdsInput.value.trim();

                    if (!machineIdsString) {
                        alert('請輸入機器 ID，多個 ID 請用逗號分隔。');
                        return;
                    }

                    // 將逗號分隔的字串轉換為數字陣列
                    const machineIds = machineIdsString.split(',').map(id => parseInt(id.trim())).filter(
                        id => !isNaN(id));

                    if (machineIds.length === 0) {
                        alert('請輸入有效的機器 ID。');
                        return;
                    }

                    // 禁用按鈕，避免重複點擊
                    captureDataBtn.disabled = true;
                    captureDataBtn.textContent = '發送中...';
                    if (redisMsgDiv) {
                        redisMsgDiv.textContent = '正在發送抓取指令...';
                    }


                    // 發送 POST 請求到 Laravel 後端 API
                    // 請根據你的路由定義確認正確的 URL
                    fetch('<?php echo e(route('admin.tcp-server.openDataGate')); ?>', { // 使用路由名稱生成 URL
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' // 確保你有 CSRF token
                            },
                            body: JSON.stringify({
                                machine_ids: machineIds
                            }) // 將機器 ID 陣列作為 JSON 發送
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw new Error(errorData.message || '伺服器錯誤');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Success:', data);
                            if (redisMsgDiv) {
                                redisMsgDiv.textContent = data.message || '指令發送成功！';
                            }
                            // 成功發送指令後，可以考慮刷新頁面或使用廣播接收新數據並局部更新
                            alert(data.message || '指令發送成功！');
                            // 這裡暫時不自動刷新，等待 WebSocket 推送或手動刷新
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            if (redisMsgDiv) {
                                redisMsgDiv.textContent = '發送指令失敗: ' + error.message;
                            }
                            alert('發送指令失敗: ' + error.message);
                        })
                        .finally(() => {
                            captureDataBtn.disabled = false;
                            captureDataBtn.textContent = '開門抓取數據';
                        });
                });
            }


            // ====== Machine 篩選選項動態過濾 (可選功能，增加 UX) ======
            // 這個腳本用於根據選擇的 Arcade 過濾 Machine 下拉框的選項
            const arcadeSelect = document.getElementById('arcade_id');
            const machineSelect = document.getElementById('machine_id');
            // 將原始的 machine 選項保存下來
            const originalMachineOptions = Array.from(machineSelect.options);

            if (arcadeSelect && machineSelect) {
                arcadeSelect.addEventListener('change', function() {
                    const selectedArcadeId = this.value;

                    // 清空當前 Machine 選項 (保留 "全部" 選項)
                    machineSelect.innerHTML = '<option value="all"><?php echo e(__('msg.all')); ?> / 全部</option>';

                    if (selectedArcadeId === 'all') {
                        // 如果選擇 "全部 Arcade"，則恢復所有 Machine 選項 (除了 "全部")
                        originalMachineOptions.forEach(option => {
                            if (option.value !== 'all') {
                                machineSelect.appendChild(option.cloneNode(true));
                            }
                        });
                    } else {
                        // 如果選擇了特定的 Arcade，則只添加屬於該 Arcade 的 Machine 選項
                        const machinesData = <?php echo json_encode($machines, 15, 512) ?>; // 將 $machines 數據從 PHP 傳遞到 JS

                        machinesData.forEach(machine => {
                            if (parseInt(machine.arcade_id) === parseInt(selectedArcadeId)) {
                                const option = document.createElement('option');
                                option.value = machine.id;
                                option.textContent = machine.name + ' (店舖: ' + (machine.arcade ?
                                        machine.arcade.name : '未知') +
                                    ')'; // 注意：這裡 arcade name 可能需要額外處理如果沒有預加載
                                // 設置選中狀態
                                if (parseInt(option.value) === parseInt(
                                        '<?php echo e($filterMachineId); ?>')) {
                                    option.selected = true;
                                }
                                machineSelect.appendChild(option);
                            }
                        });
                    }
                    // 注意：這裡只是動態過濾前端選項，篩選的實際應用是在表單提交到後端後
                    // 如果你需要自動提交，onchange 事件已經處理了
                });

                // 初始化時觸發一次，確保 Machine 選項根據初始選定的 Arcade 正確顯示
                // 如果 $filterArcadeId 已經有值，這裡會過濾選項
                if (arcadeSelect.value !== 'all') {
                    // 手動構建 Machine 選項，因為 $machines 數據在 PHP 中已經過濾（如果 $filterArcadeId 有值的話）
                    // 為了和上面的邏輯一致，我們用 JS 重新根據 $machines 數據來構建
                    const selectedArcadeId = arcadeSelect.value;
                    machineSelect.innerHTML = '<option value="all"><?php echo e(__('msg.all')); ?> / 全部</option>';
                    const machinesData = <?php echo json_encode($machines, 15, 512) ?>;
                    machinesData.forEach(machine => {
                        if (parseInt(machine.arcade_id) === parseInt(selectedArcadeId)) {
                            const option = document.createElement('option');
                            option.value = machine.id;
                            option.textContent = machine.name + ' (店舖: ' + (machine.arcade ? machine.arcade
                                .name : '未知') + ')';
                            if (parseInt(option.value) === parseInt('<?php echo e($filterMachineId); ?>')) {
                                option.selected = true;
                            }
                            machineSelect.appendChild(option);
                        }
                    });
                } else {
                    // 如果初始沒有 Arcade 篩選，確保 Machine 選項是完整的 (除了 "全部")
                    machineSelect.innerHTML = '<option value="all"><?php echo e(__('msg.all')); ?> / 全部</option>';
                    originalMachineOptions.forEach(option => {
                        if (option.value !== 'all') {
                            machineSelect.appendChild(option.cloneNode(true));
                        }
                    });
                    // 設置初始 Machine 選中狀態
                    if ('<?php echo e($filterMachineId); ?>' !== '' && '<?php echo e($filterMachineId); ?>' !== 'all') {
                        machineSelect.value = '<?php echo e($filterMachineId); ?>';
                    }

                }

                // 由於 onchange 事件已經提交表單，這個動態過濾只影響用戶看到的下拉框選項，
                // 實際的篩選邏輯仍然在後端處理。
                // 如果你想要更複雜的無頁面刷新篩選，需要使用 AJAX 替換表單提交。
            }

        });
    </script>
    
    
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/admin/tcp/index.blade.php ENDPATH**/ ?>