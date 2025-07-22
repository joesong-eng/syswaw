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
    'pure_game': 'simple_io',          # 純遊戲機 (例如競速遊戲)
    'redemption': 'claw_like',         # 獎勵型遊戲機 (例如夾娃娃機、推幣機)
    'pinball_pachinko': 'pinball_like',# 如果資料庫最終會返回這個標準 category 值，則保留
    'gambling': 'gambling_like',       # 賭博型機台

    # 根據您最新執行輸出中實際從資料庫獲取的 `machine_category` 值進行補充映射
    'pinball': 'pinball_like',         # <-- 新增：處理資料庫返回的 'pinball'
    'utility': 'input_only',           # <-- 新增：處理資料庫返回的 'utility' (例如帳單機通常只有簡單的輸入行為)
    'entertainment_only': 'simple_io', # <-- 新增：處理資料庫返回的 'entertainment_only' (類似純遊戲機)

    # 'other': 'input_only',             # 如果未來還有無法明確分類的，可以使用 'other'
}

# --- Machine Class Factory --- (保持不變，因為行為範本名稱沒有改變)
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
                m.id, m.name, m.machine_category, m.coin_input_value, m.payout_unit_value,
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
            print(f"機台名稱: {machine['name']}, 機型: '{machine['machine_category']}'") # 用引號包住，檢查有無多餘空格
        return machines
    except mysql.connector.Error as err:
        print(f"資料庫錯誤: {err}")
        return None
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

def simulate_esp32(config):
    machine_category = config.get('machine_category')
    behavior = BEHAVIOR_MAP.get(machine_category, 'unknown')

    if behavior not in MACHINE_CLASSES:
        print(f"[{config.get('chip_hardware_id')}] 警告：跳過機型 '{machine_category}' (行為範本 '{behavior}')，因未註冊模擬器。")
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
            # ****** 核心修正開始 ******
            # 1. 調用模擬器的 update_state 方法來更新機台的內部計數
            machine.update_state()
            # 2. 從機台物件的屬性中直接獲取最新的累計數據來構建 payload
            current_payload_data = {
                "chip_id": machine.chip_id, # 數據更新時依然攜帶 chip_id
                "ball_in": int(machine.ball_in), # 從機台物件讀取 ball_in 累計值
                "credit_in": int(machine.credit_in), # 從機台物件讀取 credit_in 累計值
                "ball_out": int(machine.ball_out), # 從機台物件讀取 ball_out 累計值
                "coin_out": int(machine.coin_out), # 從機台物件讀取 coin_out 累計值
                "assign_credit": int(machine.assign_credit), # 從機台物件讀取 assign_credit 累計值
                "settled_credit": int(machine.settled_credit), # 從機台物件讀取 settled_credit 累計值
                "bill_denomination": int(machine.bill_denomination), # 從機台物件讀取 bill_denomination 累計值
            }
            # ****** 核心修正結束 ******

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
