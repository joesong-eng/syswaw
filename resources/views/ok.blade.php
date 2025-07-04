<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Test</title>
</head>
<body>
    <h1>WebSocket Test</h1>

    <button id="connectButton">Connect WebSocket</button>
    <div id="output"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const connectButton = document.getElementById('connectButton');
            const outputDiv = document.getElementById('output');
            let websocket;

            connectButton.addEventListener('click', function() {
                if (!websocket || websocket.readyState === WebSocket.CLOSED) {
                    const wsUrl = 'wss://sxs.tg25.win/ws/app/syswaw-key';
                    outputDiv.innerHTML += '<p>Connecting to: ' + wsUrl + '</p>';

                    websocket = new WebSocket(wsUrl);

                    websocket.onopen = function() {
                        outputDiv.innerHTML += '<p>WebSocket connection opened.</p>';
                        websocket.send(JSON.stringify({ message: 'Hello from client!' }));
                    };

                    websocket.onmessage = function(event) {
                        outputDiv.innerHTML += '<p>Received message: ' + event.data + '</p>';
                    };

                    websocket.onclose = function() {
                        outputDiv.innerHTML += '<p>WebSocket connection closed.</p>';
                    };

                    websocket.onerror = function(error) {
                        outputDiv.innerHTML += '<p>WebSocket error: ' + error + '</p>';
                    };
                } else {
                    outputDiv.innerHTML += '<p>WebSocket is already connected or connecting.</p>';
                }
            });
        });
    </script>
</body>
</html>