import os
import sys
import time
import threading
import json
import ssl
from datetime import datetime, timezone

import paho.mqtt.client as mqtt

from machines import fetch_machine_configs
from pinball import PinballMachine

# --- 設定 ---
SCRIPT_DIR = os.path.dirname(os.path.realpath(__file__))
MQTT_HOST = "direct-mqtt.tg25.win"
MQTT_PORT = 8883
CA_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'ca.crt')
CLIENT_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.crt')
CLIENT_KEY_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.key')

SEND_INTERVAL = 5  # 秒, 縮短間隔以利測試

def create_mqtt_client(chip_id, auth_key):
    """為單個設備創建並配置 MQTT 客戶端，並等待連接成功"""
    connected_event = threading.Event()
    client_id = f"sim_{chip_id}_{os.urandom(4).hex()}"
    client = mqtt.Client(client_id=client_id, callback_api_version=mqtt.CallbackAPIVersion.VERSION2)
    client.tls_set(
        ca_certs=CA_CERT_PATH,
        certfile=CLIENT_CERT_PATH,
        keyfile=CLIENT_KEY_PATH,
        tls_version=ssl.PROTOCOL_TLS
    )

    status_topic = f"device/{chip_id}/status"

    # 設定遺囑 (Last Will)
    lwt_payload = json.dumps({
        "message_type": "status",
        "status": "offline",
        "chip_id": chip_id,
        "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
    })
    client.will_set(status_topic, lwt_payload, qos=1, retain=False)

    def on_connect(c, userdata, flags, rc, properties=None):
        if rc == 0:
            print(f"[{chip_id}] MQTT connected successfully.")
            # 發送出生訊息 (Birth Message)
            birth_payload = json.dumps({
                "message_type": "status",
                "status": "online",
                "chip_id": chip_id,
                "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
            })
            c.publish(status_topic, birth_payload, qos=1)
            print(f"[{chip_id}] Birth message sent to {status_topic}.")
            connected_event.set() # 發送連接成功信號
        else:
            print(f"[{chip_id}] MQTT connection failed with code: {rc}")
            connected_event.set() # 同樣發送信號，以防主線程永遠等待

    def on_disconnect(client, userdata, disconnect_flags, reason_code, properties):
        print(f"[{chip_id}] MQTT disconnected with reason code: {reason_code}")

    client.on_connect = on_connect
    client.on_disconnect = on_disconnect

    try:
        client.connect(MQTT_HOST, MQTT_PORT, 60)
        client.loop_start()

        # 等待 on_connect 回調設置事件，最多等待 10 秒
        if connected_event.wait(timeout=10):
            if client.is_connected():
                return client
            else:
                print(f"[{chip_id}] Failed to connect within the timeout period (on_connect reported error).")
                client.loop_stop()
                return None
        else:
            print(f"[{chip_id}] MQTT connection timed out.")
            client.loop_stop()
            return None

    except Exception as e:
        print(f"[{chip_id}] Error connecting to MQTT: {e}")
        return None

def connect_and_stay_online(config):
    """連接 MQTT，發送上線訊息，並保持在線"""
    machine = PinballMachine(config)
    client = create_mqtt_client(machine.chip_id, machine.auth_key)

    if not client:
        print(f"[{machine.chip_id}] Could not create MQTT client. Exiting thread.")
        return

    # 執行緒將會因為 client.loop_start() 而持續運行
    # 我們可以在這裡加入一個事件來優雅地停止它，但為了簡單起見，
    # 主執行緒的 KeyboardInterrupt 會直接終止所有 daemon 執行緒。
    # 這裡不需要再做任何事，客戶端會在背景保持連接。
    print(f"[{machine.chip_id}] Is online and will stay connected.")


if __name__ == "__main__":
    all_machines = fetch_machine_configs()
    pinball_machines = all_machines.get('pinball', [])

    if not pinball_machines:
        print("No pinball machines found to simulate.")
        sys.exit(0)

    print(f"Found {len(pinball_machines)} pinball machines. Starting simulators...")

    threads = []
    for config in pinball_machines:
        # 將執行緒設置為 daemon，這樣主程式結束時它們會自動退出
        thread = threading.Thread(target=connect_and_stay_online, args=(config,), daemon=True)
        threads.append(thread)
        thread.start()
        time.sleep(0.2) # 錯開一點啟動時間

    try:
        # 保持主執行緒運行，等待用戶中斷
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\nKeyboardInterrupt received. Shutting down.")

    print("Program finished.")
