# data_simulator.py
import logging, time, threading, os, sys
import mysql.connector
import paho.mqtt.client as mqtt
import json
import ssl

# --- Import all simulator classes ---
from pinball_simulator import PinballMachine
from claw_machine_simulator import ClawMachine
from simple_io_simulator import SimpleIOMachine
from gambling_simulator import GamblingLikeMachine
# <<< 新增 >>>
from input_only_simulator import InputOnlyMachine

time.sleep(2)
logger = logging.getLogger(__name__)

# --- 配置 ---
MQTT_HOST = "mqtt.tg25.win"
MQTT_PORT = 443
MQTT_PATH = "/mqtt"
MQTT_USERNAME = "joesong"
MQTT_PASSWORD = "" # 空字串
MQTT_TOPIC = "secure/test"
SEND_INTERVAL = 10 # 從 60 改成 10 秒

DB_CONFIG = {
    'user': 'syswaw', 'password': '2a@684240',
    'host': '127.0.0.1', 'database': 'syswaw',
    'unix_socket': '/tmp/mysql.sock'
}

# --- Behavioral Template Mapping ---
BEHAVIOR_MAP = {
    'pinball': 'pinball_like', 'pachinko': 'pinball_like',
    'claw_machine': 'claw_like', 'giant_claw_machine': 'claw_like', 'stacker_machine': 'claw_like',
    'slot_machine': 'gambling_like', 'gambling': 'gambling_like',
    'normally': 'simple_io', 'racing_game': 'simple_io', 'dance_game': 'simple_io',
    'basketball_game': 'simple_io',
    'money_slot': 'input_only', # <<< 新增這一行
}

# --- Machine Class Factory ---
MACHINE_CLASSES = {
    "pinball_like": PinballMachine,
    "claw_like": ClawMachine,
    "simple_io": SimpleIOMachine,
    "gambling_like": GamblingLikeMachine,
    "input_only": InputOnlyMachine, # <<< 新增這一行
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
        """
        cursor.execute(query)
        machines = cursor.fetchall()
        print(f"成功獲取 {len(machines)} 台機台的配置。")
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

    client = mqtt.Client(client_id=f"simulator_{machine.chip_id}", transport="websockets")

    def on_connect(client, userdata, flags, rc):
        if rc == 0:
            print(f"[{machine.chip_id}] MQTT 連接成功！")
        else:
            print(f"[{machine.chip_id}] MQTT 連接失敗，返回碼: {rc}")

    def on_disconnect(client, userdata, rc):
        print(f"[{machine.chip_id}] MQTT 連線斷開，返回碼: {rc}")

    client.on_connect = on_connect
    client.on_disconnect = on_disconnect

    client.ws_set_options(path=MQTT_PATH)
    client.tls_set(tls_version=ssl.PROTOCOL_TLS) # 使用預設的 TLS 版本
    client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)

    while True:
        try:
            client.connect(MQTT_HOST, MQTT_PORT)
            client.loop_start() # 在背景執行迴圈以處理連線和訊息

            while client.is_connected():
                machine.update_state()
                packet_data = machine.get_formatted_packet()

                # 將數據包轉換為 JSON 格式
                # 假設 packet_data 是一個可以解析為字典的字串，或者需要進一步處理
                # 這裡我們假設 get_formatted_packet() 返回的是一個類似 "key=value&key2=value2" 的字串
                # 或者直接返回一個字典

                # 為了簡化，我們假設 packet_data 是一個簡單的字串，我們將其包裝在一個 JSON 物件中
                # 如果 packet_data 已經是 JSON 字串，則直接使用 json.loads() 轉換
                try:
                    # 嘗試將原始數據包解析為字典，如果失敗則作為純文字處理
                    # 這裡需要根據實際的 packet_data 格式進行調整
                    # 假設 packet_data 是一個簡單的字串，我們將其作為一個值放入 JSON
                    payload = {"chip_id": machine.chip_id, "data": packet_data.strip()}
                    json_payload = json.dumps(payload)
                except json.JSONDecodeError:
                    # 如果不是有效的 JSON，則作為純文字處理
                    payload = {"chip_id": machine.chip_id, "data": packet_data.strip()}
                    json_payload = json.dumps(payload)

                print(f"[{machine.chip_id}] 發送 MQTT 訊息到 '{MQTT_TOPIC}': {json_payload}")
                client.publish(MQTT_TOPIC, json_payload)
                time.sleep(SEND_INTERVAL)
        except Exception as e:
            print(f"[{machine.chip_id}] MQTT 連線或通訊錯誤: {e}, on line {sys.exc_info()[-1].tb_lineno}")
        finally:
            if client.is_connected():
                client.loop_stop()
                client.disconnect()
        print(f"[{machine.chip_id}] 將在 {SEND_INTERVAL * 2} 秒後重連...")
        time.sleep(SEND_INTERVAL * 2)

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
