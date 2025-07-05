<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Events\MachineDataReceived;
use Illuminate\Support\Facades\Log;

class MqttListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for MQTT messages on secure/test topic via WSS.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MQTT listener...');

        $server = 'mqtt.tg25.win';
        $port = 443;
        $clientId = 'laravel-mqtt-listener-' . uniqid();
        $username = 'joesong';
        $password = ''; // 空字串
        $topic = 'secure/test';

        try {
            $connectionSettings = (new ConnectionSettings())
                ->setUsername($username)
                ->setPassword($password)
                ->setConnectTimeout(5)
                ->setSocketTimeout(5)
                ->setUseTls(true)
                ->setTlsVerifyPeer(false) // 根據您的伺服器配置，可能需要設定為 true
                ->setTlsVerifyPeerName(false) // 根據您的伺服器配置，可能需要設定為 true
                ->setTlsAlpn(['mqtt']); // 關鍵：設定 ALPN 協議為 mqtt

            $mqtt = new MqttClient($server, $port, $clientId);

            $mqtt->connect($connectionSettings, true);
            $this->info('Connected to MQTT broker.');

            $mqtt->subscribe($topic, function ($topic, $message) {
                $this->info(sprintf('Received message on topic [%s]: %s', $topic, $message));
                Log::info(sprintf('Received MQTT message on topic [%s]: %s', $topic, $message));

                try {
                    $data = json_decode($message, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Invalid JSON received: ' . json_last_error_msg());
                    }

                    // 觸發事件，將數據傳遞給監聽器
                    event(new MachineDataReceived($data));
                    $this->info('Dispatched MachineDataReceived event.');
                } catch (\Exception $e) {
                    $this->error('Error processing MQTT message: ' . $e->getMessage());
                    Log::error('Error processing MQTT message: ' . $e->getMessage(), ['message' => $message]);
                }
            }, 0); // QoS 0

            $mqtt->loop(true); // 運行事件迴圈，保持連線並處理訊息

        } catch (\Exception $e) {
            $this->error('MQTT connection failed: ' . $e->getMessage());
            Log::error('MQTT connection failed: ' . $e->getMessage());
        } finally {
            if (isset($mqtt) && $mqtt->isConnected()) {
                $mqtt->disconnect();
                $this->info('Disconnected from MQTT broker.');
            }
        }
    }
}
