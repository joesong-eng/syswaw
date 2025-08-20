<?php

require __DIR__ . '/vendor/autoload.php';

use Pusher\Pusher;

$options = [
    'host' => '127.0.0.1',
    'port' => 6001,
    'scheme' => 'http',
    'encrypted' => false,
    'useTLS' => false,
];

$pusher = new Pusher(
    'sxs-key',
    'sxs-secret',
    'sxs-app',
    $options
);

try {
    $response = $pusher->trigger('test-channel', 'test-event', ['message' => 'Hello, Reverb!']);
    if ($response === true) {
        echo "Successfully connected and sent message to Reverb server.\n";
    } else {
        echo "Failed to send message to Reverb server. Response:\n";
        print_r($response);
    }
} catch (Exception $e) {
    echo "Failed to connect to Reverb server: " . $e->getMessage() . "\n";
}
