import logging, time, threading, os, sys
import mysql.connector
import json # 雖然不直接用於MQTT，但InputOnlyMachine可能會用到json.loads
from datetime import datetime, timezone, timedelta

# --- Import all simulator classes ---
# 確保這些檔案 (pinball_simulator.py, claw_machine_simulator.py 等) 在相同目錄或可導入的路徑中
from pinball_simulator import PinballMachine
from claw_machine_simulator import ClawMachine
from simple_io_simulator import SimpleIOMachine
from gambling_simulator import GamblingLikeMachine
from input_only_simulator import InputOnlyMachine

# 取得腳本所在的目錄
SCRIPT_DIR = os.path.dirname(os.path.realpath(__file__))

# 資料庫配置
DB_CONFIG = {
    'database': 'sxswaw',
    'user': 'sxswaw',
    'password': '2a@684240',
    'host': '127.0.0.1',
    'unix_socket': '/tmp/mysql.sock'
}

# 數據生成間隔：每天一次
SIMULATION_INTERVAL_SECONDS = 86400 # 每天 0:00 寫入一次數據 (24小時 * 60分鐘 * 60秒)

# --- Behavioral Template Mapping ---
BEHAVIOR_MAP = {
    'pure_game': 'simple_io',          # 純遊戲機
    'redemption': 'claw_like',         # 獎勵型遊戲機 (如夾娃娃機、推幣機)
    'pinball_pachinko': 'pinball_like',# 彈珠/柏青哥機台 (標準 category 名稱)
    'gambling': 'gambling_like',       # 賭博型機台

    # 以下是根據您實際從資料庫獲取的 machine_category 值進行補充映射
    'pinball': 'pinball_like',         # <-- 處理資料庫中可能仍存在的 'pinball' 類型
    'utility': 'input_only',           # <-- 處理資料庫中可能仍存在的 'utility' 類型 (如帳單機)
    'entertainment_only': 'simple_io', # <-- 處理資料庫中可能仍存在的 'entertainment_only' 類型 (如純娛樂遊戲機)
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
                m.id, m.name, m.machine_category, m.coin_input_value, m.payout_unit_value,
                m.credit_button_value, m.bill_acceptor_enabled, m.accepted_denominations,
                a.chip_hardware_id, a.auth_key,
                m.auth_key_id,
                m.arcade_id
            FROM machines AS m
            JOIN machine_auth_keys AS a ON m.auth_key_id = a.id
            WHERE m.is_active = 1 AND m.deleted_at IS NULL
              AND a.chip_hardware_id IS NOT NULL AND a.auth_key IS NOT NULL
              AND a.status = 'active'
        """
        cursor.execute(query)
        machines = cursor.fetchall()
        print(f"成功獲取 {len(machines)} 台機台的配置。")
        for machine in machines:
            print(f"機台 ID: {machine['id']}, 名稱: {machine['name']}, 分類: {machine['machine_category']}")
        return machines
    except mysql.connector.Error as err:
        print(f"資料庫錯誤: {err}")
        return None
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

def insert_data_into_db(data):
    """將生成的數據插入到資料庫的 machine_data 表中"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # 根據您提供的 Laravel Schema 調整 INSERT 語句和欄位順序
        insert_query = """
            INSERT INTO machine_data (
                machine_id, arcade_id, auth_key_id, machine_type,
                credit_in, ball_in, ball_out, coin_out,
                assign_credit, settled_credit, bill_denomination,
                error_code, timestamp
            ) VALUES (
                %s, %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s,
                %s, %s
            )
        """
        # 根據 Schema 組織值
        values = (
            data['machine_id'],
            data['arcade_id'],
            data['auth_key_id'],
            data['machine_type'],
            data['credit_in'],
            data['ball_in'],
            data['ball_out'],
            data['coin_out'],
            data['assign_credit'],
            data['settled_credit'],
            data['bill_denomination'],
            data['error_code'],
            data['timestamp']
        )
        cursor.execute(insert_query, values)
        conn.commit()
        # print(f"數據已插入資料庫: {data['chip_id']} @ {data['timestamp']}")
    except mysql.connector.Error as err:
        print(f"資料庫插入錯誤: {err} for {data.get('chip_id')} @ {data.get('timestamp')}")
        conn.rollback() # 發生錯誤時回滾
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()


def generate_historical_data_for_machine(config, start_time, end_time, interval_seconds):
    machine_category = config.get('machine_category')
    behavior = BEHAVIOR_MAP.get(machine_category, 'unknown')

    if behavior not in MACHINE_CLASSES:
        print(f"[{config.get('chip_hardware_id')}] 警告：跳過機型 '{machine_category}' (行為範本 '{behavior}')，因未註冊模擬器。")
        return

    MachineClass = MACHINE_CLASSES[behavior]
    machine = MachineClass(config) # 初始化模擬器

    # --- 新增：從資料庫加載機台的初始狀態 ---
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        # 查詢此機台在 start_time 之前的最新一筆數據
        query = """
            SELECT
                credit_in, ball_in, ball_out, coin_out,
                assign_credit, settled_credit, bill_denomination
            FROM machine_data
            WHERE machine_id = %s AND timestamp < %s
            ORDER BY timestamp DESC, id DESC
            LIMIT 1
        """
        cursor.execute(query, (config['id'], start_time))
        last_record = cursor.fetchone()

        if last_record:
            # 使用從資料庫獲取的最後狀態來初始化模擬器
            # 確保您的模擬器類 (PinballMachine, ClawMachine 等) 有這些屬性且可寫入
            machine.credit_in = last_record.get('credit_in', 0)
            machine.ball_in = last_record.get('ball_in', 0)
            machine.ball_out = last_record.get('ball_out', 0)
            machine.coin_out = last_record.get('coin_out', 0)
            machine.assign_credit = last_record.get('assign_credit', 0)
            machine.settled_credit = last_record.get('settled_credit', 0)
            machine.bill_denomination = last_record.get('bill_denomination', 0)
            print(f"[{machine.chip_id}] 已從資料庫加載初始狀態。Credit_in: {machine.credit_in}")
        else:
            print(f"[{machine.chip_id}] 未找到 {start_time} 之前的歷史數據，從零開始模擬。")

    except mysql.connector.Error as err:
        print(f"資料庫錯誤 (加載初始狀態): {err}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()
    # --- 結束：從資料庫加載機台的初始狀態 ---

    print(f"[{machine.chip_id}] 歷史數據生成器啟動，機型: {machine.__class__.__name__}")

    # current_simulated_time 從 start_time 的當天 00:00:00 開始
    current_simulated_time = start_time.replace(hour=0, minute=0, second=0, microsecond=0)

    while current_simulated_time <= end_time:
        try:
            machine.update_state()

            # 構建要插入的數據字典
            data_to_insert = {
                "machine_id": config['id'],
                "arcade_id": config['arcade_id'],
                "auth_key_id": config['auth_key_id'],
                "machine_type": config['machine_category'], # 將 machine_type 改為 machine_category
                "credit_in": int(getattr(machine, "credit_in", 0)),
                "ball_in": int(getattr(machine, "ball_in", 0)),
                "ball_out": int(getattr(machine, "ball_out", 0)),
                "coin_out": int(getattr(machine, "coin_out", 0)),
                "assign_credit": int(getattr(machine, "assign_credit", 0)),
                "settled_credit": int(getattr(machine, "settled_credit", 0)),
                "bill_denomination": int(getattr(machine, "bill_denomination", 0)),
                "error_code": None, # 預設為 NULL
                "timestamp": current_simulated_time,
                "chip_id": config['chip_hardware_id'] # 僅用於打印，不插入資料庫
            }

            insert_data_into_db(data_to_insert)

            print(f"[{data_to_insert['chip_id']}] 已生成數據: {current_simulated_time.strftime('%Y-%m-%d %H:%M:%S')} - CI:{data_to_insert['credit_in']} BO:{data_to_insert['ball_out']} CO:{data_to_insert['coin_out']} BD:{data_to_insert['bill_denomination']}")

            current_simulated_time += timedelta(seconds=interval_seconds)

        except Exception as e:
            print(f"[{config.get('chip_hardware_id')}] 在歷史數據生成迴圈中發生錯誤: {e}")
            time.sleep(5)

    print(f"[{config.get('chip_hardware_id')}] 歷史數據生成完成。")


if __name__ == "__main__":
    # 請根據您實際需要生成的日期範圍來設定 START_DATE 和 END_DATE
    # 建議您從一個較早的日期開始，一次性生成到目前或未來的數據，
    # 避免分批次、不連續地生成，否則還是可能導致數據重複或混亂。
    START_DATE = datetime(2025, 5, 1, 0, 0, 0, tzinfo=timezone.utc) # 例如從2025年5月1日開始
    END_DATE = datetime.now(timezone.utc).replace(hour=23, minute=59, second=59, microsecond=0) #到今天就可以 + timedelta(days=1) # 生成到當前日期的隔天，確保包含今天完整數據

    SIMULATION_INTERVAL_SECONDS_DAILY = SIMULATION_INTERVAL_SECONDS

    print(f"\n準備生成從 {START_DATE} 到 {END_DATE} 的歷史數據，間隔 {SIMULATION_INTERVAL_SECONDS_DAILY} 秒 (每天一次)。")

    machine_configs = fetch_machine_configs_from_db()
    if not machine_configs:
        sys.exit(1)

    threads = []
    for config in machine_configs:
        thread = threading.Thread(
            target=generate_historical_data_for_machine,
            args=(config, START_DATE, END_DATE, SIMULATION_INTERVAL_SECONDS_DAILY),
            daemon=True
        )
        threads.append(thread)
        thread.start()
        time.sleep(0.1)

    print(f"\n已啟動 {len(threads)} 個歷史數據生成器線程。請等待所有線程完成或按 Ctrl+C 停止。")
    try:
        for t in threads:
            t.join()
        print("\n所有歷史數據生成器線程已完成。")
    except KeyboardInterrupt:
        print("\n歷史數據生成器停止中...")
    finally:
        print("歷史數據生成器已關閉。\n")
