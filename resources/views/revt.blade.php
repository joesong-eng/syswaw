<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Reverb 測試連線</title>
    {{-- <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
        }
        #status {
            font-weight: bold;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <h1>Reverb 測試連線頁面</h1>
    <p id="status">🔌 正在嘗試連線中...</p>
    <p id="reverbmsg">...</p>
    <script>//window.Echo 已在bootstrap.js裡初始化
        document.addEventListener('DOMContentLoaded', () => {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                document.getElementById('status').innerText = '✅ 已成功連線至 Reverb Server';
                console.log('[Reverb] 已連線');
            });
    
            window.Echo.connector.pusher.connection.bind('error', (err) => {
                document.getElementById('status').innerText = '❌ 發生錯誤，請查看 Console';
                console.error('[Reverb] 錯誤:', err);
            });
    
            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                document.getElementById('status').innerText = '⚠️ 連線中斷';
                console.warn('[Reverb] 已中斷連線');
            });
        })
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                window.Echo.channel('test-channel')
                    .listen('.TestBroadcast', (e) => {
                        console.log('[Reverb] 收到事件:', e);
                        document.getElementById('reverbmsg').innerText = "收到訊息：" + e.message;
                    });
    
                console.log('[Reverb] 正在監聽 test-channel > .TestBroadcast');
            } else {
                console.warn('⚠️ window.Echo 尚未初始化');
            }
        });
    </script>
</body>
</html>
