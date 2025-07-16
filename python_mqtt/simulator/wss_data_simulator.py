# wss_data_simulator.py
import logging, time, threading, os, sys
import mysql.connector
import paho.mqtt.client as mqtt
import json
import ssl
from datetime import datetime, timezone # 確保這行存在

# --- Import all simulator classes ---
# 假設這些類別檔案已經複製到 python_mqtt/simulator/ 目錄下
from pinball_simulator import PinballMachine
from claw_machine_simulator import ClawMachine
from simple_io_simulator import SimpleIOMachine
from gambling_simulator import GamblingLikeMachine
from input_only_simulator import InputOnlyMachine

# 取得腳本所在的目錄
SCRIPT_DIR = os.path.dirname(os.path.realpath(__file__))

MQTT_HOST = "direct-mqtt.tg25.win"
MQTT_PORT = 8883
MQTT_TOPIC = "secure/test"
# 使用絕對路徑來確保檔案能被找到
CA_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'ca.crt')
CLIENT_CERT_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.crt')
CLIENT_KEY_PATH = os.path.join(SCRIPT_DIR, '..', 'certs', 'client.key')
SEND_INTERVAL = 10 # 從 60 改成 10 秒
HEARTBEAT_INTERVAL_SECONDS = 300 # 5 分鐘發送一次心跳包

DB_CONFIG = {
    'database': 'sxswaw',
    'user': 'sxswaw',
    'password': '2a@684240',
    'host': '127.0.0.1',
    'unix_socket': '/tmp/mysql.sock'
}

# --- Behavioral Template Mapping ---
BEHAVIOR_MAP = {
    'pinball': 'pinball_like',
    'pachinko': 'pinball_like',
    'claw_machine': 'claw_like',
    'giant_claw_machine': 'claw_like',
    'stacker_machine': 'claw_like',
    'slot_machine': 'gambling_like',
    'gambling': 'gambling_like',
    'normally': 'simple_io',
    'racing_game': 'simple_io',
    'dance_game': 'simple_io',
    'basketball_game': 'simple_io',
    'money_slot': 'input_only',
    'ball': 'pinball_like',
}

# --- Machine Class Factory ---
MACHINE_CLASSES = {
    "pinball_like": PinballMachine,
    "claw_like": ClawMachine,
    "simple_io": SimpleIOMachine,
    "gambling_like": GamblingLikeMachine,
    "input_only": InputOnlyMachine,
}

def fetch_machine_configs_from_db():
    print("正在從資料庫獲取機台配置...")
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        query = """
            SELECT
                m.id, m.name, m.machine_type, m.coin_input_value, m.payout_unit_value,
                m.credit_button_value, m.bill_acceptor_enabled, m.accepted_denominations,
                a.chip_hardware_id, a.auth_key
            FROM machines AS m
            JOIN machine_auth_keys AS a ON m.auth_key_id = a.id
            WHERE m.is_active = 1 AND m.deleted_at IS NULL
              AND a.chip_hardware_id IS NOT NULL AND a.auth_key IS NOT NULL
              AND a.status = 'active'
        """
        cursor.execute(query)
        machines = cursor.fetchall()
        print(f"成功獲取 {len(machines)} 台機台的配置。")
        for machine in machines: # <-- 新增這段來檢查
            print(f"機台名稱: {machine['name']}, 機型: '{machine['machine_type']}'") # 用引號包住，檢查有無多餘空格
        return machines
    except mysql.connector.Error as err:
        print(f"資料庫錯誤: {err}")
        return None
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

def simulate_esp32(config):
    machine_type = config.get('machine_type')
    behavior = BEHAVIOR_MAP.get(machine_type, 'unknown')

    if behavior not in MACHINE_CLASSES:
        print(f"[{config.get('chip_hardware_id')}] 警告：跳過機型 '{machine_type}' (行為範本 '{behavior}')，因未註冊模擬器。")
        return

    MachineClass = MACHINE_CLASSES[behavior]
    machine = MachineClass(config)
    print(f"[{machine.chip_id}] 模擬器啟動，機型: {machine.__class__.__name__}")

    # 修改 client_id 以包含 auth_key 的部分，確保唯一性
    client_id = f"simulator_{machine.chip_id}_{config.get('auth_key')[:8]}"
    client = mqtt.Client(client_id=client_id)
    client.tls_set(
        ca_certs=CA_CERT_PATH,
        certfile=CLIENT_CERT_PATH,
        keyfile=CLIENT_KEY_PATH,
        tls_version=ssl.PROTOCOL_TLS
    )

    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            print(f"[{machine.chip_id}] MQTT 連接成功！")
            # --- 新增：在連接成功時發送認證信息 ---
            auth_payload = {
                "message_type": "authenticate", # 標識為認證消息
                "chip_id": machine.chip_id,
                "auth_key": config.get('auth_key'), # 從 config 中獲取 auth_key
                "timestamp": datetime.now(timezone.utc).isoformat(timespec='seconds')
            }
            json_auth_payload = json.dumps(auth_payload)
            print(f"[{machine.chip_id}] 發送認證 MQTT 訊息到 '{MQTT_TOPIC}': {json_auth_payload}")
            client.publish(MQTT_TOPIC, json_auth_payload, qos=1)
            # --- 認證信息發送結束 ---
        else:
            print(f"[{machine.chip_id}] MQTT 連接失敗，返回碼: {rc}")

    def on_disconnect(client, userdata, flags, rc, properties=None):
        print(f"[{machine.chip_id}] MQTT 連線斷開，返回碼: {rc}")

    client.on_connect = on_connect
    client.on_disconnect = on_disconnect

    try:
        client.connect(MQTT_HOST, MQTT_PORT, 60)
    except Exception as e:
        print(f"[{machine.chip_id}] 無法啟動 MQTT 連線: {e}")
        return

    client.loop_start()

    last_payload_data = None # 用於儲存上一次發送的數據（不含時間戳和 message_type）
    heartbeat_counter = 0
    heartbeat_threshold = HEARTBEAT_INTERVAL_SECONDS // SEND_INTERVAL # 計算需要多少個 SEND_INTERVAL 才能達到心跳間隔

    while True:
        try:
            machine.update_state()
            current_payload_data = {
                "chip_id": machine.chip_id, # 數據更新時依然攜帶 chip_id
                "ball_in": int(getattr(machine, "ball_in", 0)),
                "credit_in": int(getattr(machine, "credit_in", 0)),
                "ball_out": int(getattr(machine, "ball_out", 0)),
                "coin_out": int(getattr(machine, "coin_out", 0)),
                "assign_credit": int(getattr(machine, "assign_credit", 0)),
                "settled_credit": int(getattr(machine, "settled_credit", 0)),
                "bill_denomination": int(getattr(machine, "bill_denomination", 0)),
            }

            send_data_update = False
            # 判斷數據是否有變化
            if last_payload_data is None or current_payload_data != last_payload_data:
                send_data_update = True
                last_payload_data = current_payload_data.copy()

            # 檢查是否需要發送心跳包 (即使數據沒變化，到時間也發送)
            heartbeat_counter += 1
            if heartbeat_counter >= heartbeat_threshold:
                send_data_update = True # 強制發送心跳包
                heartbeat_counter = 0 # 重置計數器

            if client.is_connected():
                if send_data_update:
                    # 構建最終的 payload，包含時間戳和 message_type
                    final_payload = current_payload_data.copy()
                    final_payload["message_type"] = "data_update" # 標識為數據更新消息
                    final_payload["timestamp"] = datetime.now(timezone.utc).isoformat(timespec='seconds')
                    final_payload["auth_key"] = config.get('auth_key') # 新增：在數據更新消息中包含 auth_key
                    json_payload = json.dumps(final_payload)
                    # print(f"[{machine.chip_id}] 發送 MQTT 訊息到 '{MQTT_TOPIC}': {json_payload}")
                    client.publish(MQTT_TOPIC, json_payload, qos=1) # 確保 QoS 為 1
                # else:
                    # print(f"[{machine.chip_id}] 數據無變化，跳過發送。")
            else:
                print(f"[{machine.chip_id}] 連線中斷，等待自動重連...")

            time.sleep(SEND_INTERVAL)
        except Exception as e:
            print(f"[{machine.chip_id}] 在主迴圈中發生錯誤: {e}")
            time.sleep(SEND_INTERVAL)

if __name__ == "__main__":
    machine_configs = fetch_machine_configs_from_db()
    if not machine_configs:
        sys.exit(1)

    threads = []
    for config in machine_configs:
        thread = threading.Thread(target=simulate_esp32, args=(config,), daemon=True)
        threads.append(thread)
        thread.start()
        time.sleep(0.1)

    print(f"\n已啟動 {len(threads)} 個模擬器線程。按 Ctrl+C 停止。")
    try:
        while any(t.is_alive() for t in threads):
            time.sleep(5)
    except KeyboardInterrupt:
        print("\n模擬器停止中...")
    finally:
        print("模擬器已關閉。")
