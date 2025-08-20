import os
import sys
import time
import threading
import json
import ssl
import random
from datetime import datetime, timezone

import mysql.connector
import paho.mqtt.client as mqtt

from python_mqtt.simulator_bk.pinball_simulator import PinballMachine

# --- 設定 ---
SCRIPT_DIR = os.path.dirname(os.path.realpath(__file__))

MQTT_HOST = "direct-mqtt.tg25.win"
MQTT_PORT = 8883
CA_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'ca.crt')
CLIENT_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.crt')
CLIENT_KEY_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.key')

SEND_INTERVAL = 10  # 秒
HEARTBEAT_INTERVAL_SECONDS = 300

DB_CONFIG = {
    'database': 'sxswaw',
    'user': 'sxswaw',
    'password': '2a@684240',
    'host': '127.0.0.1',
    'unix_socket': '/tmp/mysql.sock'
}

# --- 取得機台配置 ---
def fetch_pinball_configs():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT m.id, m.name, m.machine_category, m.coin_input_value, m.payout_unit_value,
                   a.chip_hardware_id, a.auth_key
            FROM machines m
            JOIN machine_auth_keys a ON m.auth_key_id = a.id
            WHERE m.is_active=1 AND m.deleted_at IS NULL
              AND a.chip_hardware_id IS NOT NULL AND a.auth_key IS NOT NULL
              AND a.status='active' AND a.id BETWEEN 1 AND 10
        """)
        rows = cursor.fetchall()
        configs = [r for r in rows if r['machine_category'] in ['pinball_pachinko', 'pinball']]
        print(f"Found {len(configs)} pinball machines.")
        return configs
    except Exception as e:
        print(f"DB error: {e}")
        return []
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

# --- 模擬單台機台 ---
def simulate_machine(config):
    machine = PinballMachine(config)
    data_topic = f"device/{machine.chip_id}/data/update"
    client = create_mqtt_client(machine.chip_id, machine.auth_key, data_topic)
    if client is None:
        return

    last_payload_data = None
    heartbeat_counter = 0
    heartbeat_threshold = HEARTBEAT_INTERVAL_SECONDS // SEND_INTERVAL

    try:
        while True:
            payload = machine.simulate_customer()
            heartbeat_counter += 1
            send_data = False

            # 判斷是否發送
            if last_payload_data is None or payload != last_payload_data:
                send_data = True
                last_payload_data = payload.copy()

            if heartbeat_counter >= heartbeat_threshold:
                send_data = True
                heartbeat_counter = 0

            if client.is_connected() and send_data:
                final_payload = payload.copy()
                final_payload["message_type"] = "data_update"
                final_payload["timestamp"] = datetime.now(timezone.utc).isoformat(timespec='seconds')
                client.publish(data_topic, json.dumps(final_payload), qos=1)

            time.sleep(SEND_INTERVAL)
    except KeyboardInterrupt:
        print(f"[{machine.chip_id}] Simulator stopped.")
    except Exception as e:
        print(f"[{machine.chip_id}] Error: {e}")

# --- MQTT 連線 ---
def create_mqtt_client(chip_id, auth_key, data_topic):
    client_id = f"sim_{chip_id}_{auth_key[:8]}"
    client = mqtt.Client(client_id=client_id, callback_api_version=mqtt.CallbackAPIVersion.VERSION2)
    client.tls_set(
        ca_certs=CA_CERT_PATH,
        certfile=CLIENT_CERT_PATH,
        keyfile=CLIENT_KEY_PATH,
        tls_version=ssl.PROTOCOL_TLS
    )

    command_topic = f"device/{chip_id}/command"
    auth_resp_topic = f"device/{chip_id}/auth/response"
    status_topic = f"device/{chip_id}/status" # 新增狀態主題

    def on_connect(c, userdata, flags, rc, properties=None):
        if rc == 0:
            print(f"[{chip_id}] MQTT connected!")

            # 1️⃣ 發送認證消息 (仍然發送到 data_topic，因為這是數據流的一部分)
            auth_payload = {
                "message_type": "authenticate",
                "chip_id": chip_id,
                "auth_key": auth_key,
                "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
            }
            c.publish(data_topic, json.dumps(auth_payload), qos=1)

            # 2️⃣ 發送 Birth Message (online) 到正確的狀態主題
            online_payload = {
                "message_type": "status",
                "status": "online",
                "chip_id": chip_id,
                "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
            }
            c.publish(status_topic, json.dumps(online_payload), qos=1) # 修改為 status_topic
            print(f"[{chip_id}] Sent Birth Message (online) to {status_topic}.")

            # 3️⃣ 訂閱 command & auth response topic
            c.subscribe(command_topic, qos=1)
            c.subscribe(auth_resp_topic, qos=1)
            print(f"[{chip_id}] Subscribed to {command_topic} and {auth_resp_topic}")
        else:
            print(f"[{chip_id}] MQTT connect failed: {rc}")

    def on_message(c, userdata, msg):
        print(f"[{chip_id}] Received message from topic '{msg.topic}': {msg.payload.decode()}")

    def on_disconnect(c, userdata, rc):
        print(f"[{chip_id}] MQTT disconnected: {rc}")
        # 發送 Last Will and Testament (offline) 到正確的狀態主題
        offline_payload = {
            "message_type": "status",
            "status": "offline",
            "chip_id": chip_id,
            "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
        }
        # 注意：LWT 通常在 connect 之前設定，這裡只是模擬斷線時發送
        # 真正的 LWT 需要在 client.will_set() 中設定
        try:
            c.publish(status_topic, json.dumps(offline_payload), qos=1) # 修改為 status_topic
            print(f"[{chip_id}] Sent Last Will (offline) to {status_topic}.")
        except Exception as e:
            print(f"[{chip_id}] Error sending offline status: {e}")


    client.on_connect = on_connect
    client.on_disconnect = on_disconnect
    client.on_message = on_message

    # 設定 LWT (Last Will and Testament)
    # 這確保即使模擬器非正常斷開，也會發送 offline 狀態
    lwt_topic = f"device/{chip_id}/status"
    lwt_payload = json.dumps({
        "message_type": "status",
        "status": "offline",
        "chip_id": chip_id,
        "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
    })
    client.will_set(lwt_topic, lwt_payload, qos=1, retain=False) # retain=True 可以讓 broker 保留最後一條狀態

    try:
        client.connect(MQTT_HOST, MQTT_PORT, 60)
    except Exception as e:
        print(f"[{chip_id}] MQTT connection error: {e}")
        return None

    client.loop_start()
    return client



# --- 主程式 ---
if __name__ == "__main__":
    configs = fetch_pinball_configs()
    if not configs:
        print("No pinball machines found.")
        sys.exit(0)

    threads = []
    for cfg in configs:
        t = threading.Thread(target=simulate_machine, args=(cfg,), daemon=True)
        threads.append(t)
        t.start()
        time.sleep(0.1)

    print(f"Started {len(threads)} pinball simulator threads.")
    try:
        while any(t.is_alive() for t in threads):
            time.sleep(5)
    except KeyboardInterrupt:
        print("Simulator stopped by user.")
