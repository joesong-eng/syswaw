import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


// ✅ Axios 基本設定
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
window.Echo = new Echo({ 
    broadcaster: 'reverb',
    key:        import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:     import.meta.env.VITE_REVERB_HOST,
    wsPort:     import.meta.env.VITE_REVERB_PORT,
    wssPort:    import.meta.env.VITE_REVERB_PORT,
    scheme:     import.meta.env.VITE_REVERB_SCHEME,
    encrypted:  import.meta.env.VITE_REVERB_SCHEME === 'wss',
    wsPath:     import.meta.env.VITE_REVERB_PATH,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// // ✅ Pusher 實例註冊
// window.Pusher = Pusher;

// // ✅ Echo 實例建立（使用 .env 裡 VITE_ 開頭的變數）
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'syswaw-key',
//     wsHost: window.location.hostname, // 或者直接使用 'sys.tg25.win'
//     wsPort: 443,
//     wssPort: 443,
//     forceTLS: true,
//     path: '/ws',
//     cluster: 'mt1', // 如果你的 Pusher 設定有 cluster
// });
