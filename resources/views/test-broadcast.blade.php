<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverb Broadcast Test</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <div>
        <h1>Reverb Broadcast Test</h1>
        <p>Listening for events on <strong>test-channel</strong>...</p>
        <ul id="messages"></ul>
        <button onclick="triggerBroadcast()">Trigger Broadcast</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo === 'undefined') {
                console.error('Echo is not initialized!');
                return;
            }
            console.log('Subscribing to test-channel...');
            window.Echo.channel('channel-Reverb')
                .listen('.test-event', (e) => {
                    console.log('Received event:', e);
                    const messageList = document.getElementById('messages');
                    const listItem = document.createElement('li');
                    listItem.textContent = e.message;
                    messageList.appendChild(listItem);
                });
        });
                // 觸發廣播的函數
        function triggerBroadcast() {
            fetch('/test-broadcast')
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>