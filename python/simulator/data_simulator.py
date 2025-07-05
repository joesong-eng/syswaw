# data_simulator.py
import logging, socket, time, threading, os, sys
import mysql.connector

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
SERVER_IP = "127.0.0.1"
SERVER_PORT = 39001
# <<< 唯一的修改點：縮短發送間隔 >>>
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
    'money_slot': 'input_only',
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

    while True:
        try:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as client_socket:
                client_socket.connect((SERVER_IP, SERVER_PORT))
                # print(f"[{machine.chip_id}] 連接成功！")
                while True:
                    machine.update_state()
                    packet_to_send = machine.get_formatted_packet()
                    # print(f"[{machine.chip_id}] 發送: {packet_to_send.strip()}")
                    client_socket.sendall(packet_to_send.encode('utf-8'))
                    client_socket.recv(1024)
                    time.sleep(SEND_INTERVAL)
        except (socket.error, ConnectionRefusedError, socket.timeout) as e:
            print(f"[{machine.chip_id}] 連接或通訊錯誤: {e}")
        except Exception as e:
            print(f"[{machine.chip_id}] 發生未預期錯誤: {e}, on line {sys.exc_info()[-1].tb_lineno}")
        # print(f"[{machine.chip_id}] 將在 {SEND_INTERVAL * 2} 秒後重連...")
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
