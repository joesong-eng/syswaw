import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;


// ✅ Axios 基本設定
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
if (window.Echo.connector.pusher) { // 確保 pusher connector 存在
    const statusElement = document.getElementById('global-reverb-status');

    const updateStatus = (text, bgColor = 'bg-gray-500') => {
        if (statusElement) {
            statusElement.textContent = text;
            // 移除舊的背景色 class
            statusElement.classList.remove('bg-gray-500', 'bg-green-500', 'bg-red-500', 'bg-yellow-500');
            // 添加新的背景色 class
            statusElement.classList.add(bgColor);
        }
        console.log(`Reverb Status: ${text}`); // 保留控制台日誌
    };

    window.Echo.connector.pusher.connection.bind('connecting', () => {
        updateStatus('連接中...', 'bg-yellow-500');
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        updateStatus('已連接 ✅', 'bg-green-500');
    });

    window.Echo.connector.pusher.connection.bind('unavailable', () => {
        updateStatus('服務不可用 ⚠️', 'bg-red-500');
    });

    window.Echo.connector.pusher.connection.bind('failed', () => {
        updateStatus('連接失敗 ❌', 'bg-red-500');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        updateStatus('已斷開連接 🔌', 'bg-gray-500');
    });

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('Reverb Connection Error:', err);
        let errorMsg = '連接錯誤 ❌';
        if (err.error && err.error.data && err.error.data.code === 4004) {
            errorMsg = 'App Key 錯誤';
        } else if (err.error && err.error.data && err.error.data.code === 4100) {
            errorMsg = 'App Over Limit';
        }
        updateStatus(errorMsg, 'bg-red-500');
    });

} else {
    console.error("Pusher connector not found on Echo instance.");
}
// // ✅ Pusher 實例註冊
// window.Pusher = Pusher;

// // ✅ Echo 實例建立（使用 .env 裡 VITE_ 開頭的變數）
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'syswaw-key',
//     wsHost: window.location.hostname, // 或者直接使用 'sxs.tg25.win'
//     wsPort: 443,
//     wssPort: 443,
//     forceTLS: true,
//     path: '/ws',
//     cluster: 'mt1', // 如果你的 Pusher 設定有 cluster
// });
