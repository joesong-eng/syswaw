import logging, time, threading, os, sys
import mysql.connector
import json
from datetime import datetime, timezone, timedelta
import random # 確保 random 模組被導入

# --- Import all simulator classes ---
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
    'pinball': 'pinball_like',         # 彈珠/柏青哥機台 (鍵名從 'pinball_pachinko' 修改為 'pinball')
    'gambling': 'gambling_like',       # 博弈機台
    'utility': 'input_only',           # 純輸入型設備 (如紙鈔機)
    'entertainment_only': 'simple_io', # 新增：純娛樂機，映射到簡單輸入輸出機
}

# --- 機台類別映射 (用於動態創建實例) ---
MACHINE_CLASSES = {
    'pinball_like': PinballMachine,
    'claw_like': ClawMachine,
    'simple_io': SimpleIOMachine,
    'gambling_like': GamblingLikeMachine,
    'input_only': InputOnlyMachine,
}


def fetch_machine_configs_from_db():
    """從資料庫獲取所有機台配置，包含 chip_hardware_id 和 auth_key"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        # 執行 JOIN 查詢以獲取所有相關信息
        query = """
            SELECT
                m.*, -- Select all columns from machines table
                mak.chip_hardware_id, -- Select chip_hardware_id from machine_auth_keys
                mak.auth_key -- Select auth_key from machine_auth_keys (assuming this is the token)
            FROM
                machines m
            JOIN
                machine_auth_keys mak ON m.auth_key_id = mak.id
            WHERE
                m.is_active = TRUE
        """
        cursor.execute(query)
        configs = cursor.fetchall()
        print(f"成功獲取 {len(configs)} 台機台的配置。")
        for config in configs:
            # 確保使用 .get() 訪問可能不存在的鍵，增加程式健壯性
            print(f"機台 ID: {config.get('id')}, 名稱: {config.get('name')}, 分類: {config.get('machine_category')}, Chip ID: {config.get('chip_hardware_id', 'N/A')}")
        return configs
    except mysql.connector.Error as err:
        print(f"資料庫連接或查詢失敗: {err}")
        return None
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def get_latest_daily_data(machine_id):
    """獲取指定機台的最新日報數據"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        # 獲取最新一筆日報數據
        query = """
            SELECT * FROM machine_data
            WHERE machine_id = %s
            ORDER BY timestamp DESC
            LIMIT 1
        """
        cursor.execute(query, (machine_id,))
        latest_data = cursor.fetchone()
        return latest_data
    except mysql.connector.Error as err:
        print(f"查詢最新日報數據失敗: {err}")
        return None
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def insert_machine_data(data):
    """將機台數據插入資料庫"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        add_data = ("INSERT INTO machine_data "
                    "(machine_id, arcade_id, auth_key_id, machine_type, credit_in, ball_in, ball_out, coin_out, assign_credit, settled_credit, bill_denomination, error_code, timestamp) "
                    "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
        data_tuple = (
            data['machine_id'], data['arcade_id'], data['auth_key_id'], data['machine_type'],
            data['credit_in'], data['ball_in'], data['ball_out'], data['coin_out'],
            data['assign_credit'], data['settled_credit'], data['bill_denomination'],
            data['error_code'], data['timestamp']
        )
        cursor.execute(add_data, data_tuple)
        conn.commit()
        # print(f"數據成功插入: {data['chip_id']} - {data['timestamp']}")
    except mysql.connector.Error as err:
        print(f"插入數據失敗: {err}")
        conn.rollback() # 回滾事務
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def generate_historical_data_for_machine(config, start_date, end_date, interval_seconds):
    chip_id = config.get('chip_hardware_id') # 使用 .get()
    machine_id = config.get('id') # 使用 .get()
    machine_category = config.get('machine_category') # 使用 .get()

    if not machine_category or machine_category not in BEHAVIOR_MAP:
        print(f"[{chip_id}] 未知的機台類別或未配置行為映射: {machine_category}")
        return

    behavior_template = BEHAVIOR_MAP[machine_category]
    MachineClass = MACHINE_CLASSES.get(behavior_template)

    if not MachineClass:
        print(f"[{chip_id}] 未找到對應行為模板 '{behavior_template}' 的機台類別。")
        return

    # 獲取最新的歷史數據作為初始狀態
    latest_data = get_latest_daily_data(machine_id)

    # 初始化機台模擬器
    machine = MachineClass(config)

    if latest_data:
        # 如果有歷史數據，從歷史數據中恢復機台的累計狀態
        print(f"[{chip_id}] 找到 {latest_data['timestamp']} 的歷史數據，從該點繼續模擬。")
        machine.credit_in = latest_data.get('credit_in', 0)
        machine.ball_in = latest_data.get('ball_in', 0)
        machine.ball_out = latest_data.get('ball_out', 0)
        machine.coin_out = latest_data.get('coin_out', 0)
        machine.assign_credit = latest_data.get('assign_credit', 0)
        machine.settled_credit = latest_data.get('settled_credit', 0)
        machine.bill_denomination = latest_data.get('bill_denomination', 0)

        # 從最新數據的時間戳開始，只生成其後缺失的數據
        current_simulated_date = latest_data['timestamp'] + timedelta(seconds=interval_seconds)
        print(f"[{chip_id}] 將從 {current_simulated_date.strftime('%Y-%m-%d')} 開始生成日報數據。")

    else:
        print(f"[{chip_id}] 未找到 {start_date.strftime('%Y-%m-%d')} 之前的歷史數據，從零開始模擬。")
        current_simulated_date = start_date

    print(f"[{chip_id}] 正在為 {current_simulated_date.strftime('%Y-%m-%d')} 生成日報數據...")

    # 確保模擬日期不會超過結束日期
    while current_simulated_date <= end_date:
        try:
            # 讓模擬器更新當天的數據 (內部會累加)
            # 注意: update_state 不返回數據，只更新 machine 物件的屬性
            machine.update_state()

            # 從機台物件中讀取更新後的累計數據
            data_to_insert = {
                'machine_id': machine_id,
                'arcade_id': config.get('arcade_id'),
                'auth_key_id': config.get('auth_key_id'),
                'machine_type': config.get('machine_type'),
                'credit_in': machine.credit_in,
                'ball_in': machine.ball_in,
                'ball_out': machine.ball_out,
                'coin_out': machine.coin_out,
                'assign_credit': machine.assign_credit,
                'settled_credit': machine.settled_credit,
                'bill_denomination': machine.bill_denomination,
                'error_code': getattr(machine, 'current_error', None), # 如果有 error_code 屬性，則獲取
                'timestamp': current_simulated_date.replace(hour=0, minute=0, second=0, microsecond=0) # 日報數據統一為當天 00:00:00
            }

            # 插入數據到資料庫
            insert_machine_data(data_to_insert)

            current_simulated_date += timedelta(seconds=interval_seconds)

        except Exception as e:
            print(f"[{chip_id}] 在歷史數據生成迴圈中發生錯誤: {e}")
            time.sleep(5)

    print(f"[{chip_id}] 歷史數據生成完成。")


if __name__ == "__main__":
    print(f"[{datetime.now(timezone.utc).strftime('%Y-%m-%d %H:%M:%S UTC')}] 歷史數據生成器啟動。")

    # 請根據您實際需要生成的日期範圍來設定 START_DATE 和 END_DATE
    # 建議您從一個較早的日期開始，一次性生成到目前或未來的數據，
    # 避免分批次、不連續地生成，否則還是可能導致數據重複或混亂。
    START_DATE = datetime(2025, 5, 1, 0, 0, 0, tzinfo=timezone.utc) # 例如從2025年5月1日開始
    # END_DATE 設定為當天結束 (23:59:59)，確保包含當天完整數據
    END_DATE = datetime.now(timezone.utc).replace(hour=23, minute=59, second=59, microsecond=0)

    SIMULATION_INTERVAL_SECONDS_DAILY = SIMULATION_INTERVAL_SECONDS

    print(f"\n準備為 {END_DATE.strftime('%Y-%m-%d')} 生成日報數據。")

    machine_configs = fetch_machine_configs_from_db()
    if not machine_configs:
        sys.exit(1)

    threads = []
    for config in machine_configs:
        thread = threading.Thread(target=generate_historical_data_for_machine,
                                  args=(config, START_DATE, END_DATE, SIMULATION_INTERVAL_SECONDS_DAILY,),
                                  daemon=True)
        threads.append(thread)
        thread.start()
        # time.sleep(0.1) # 避免啟動過快，可選

    print(f"\n已啟動 {len(threads)} 個歷史數據生成器線程。等待所有線程完成。")

    for thread in threads:
        thread.join()

    print("所有歷史數據生成器線程已完成。")
    print("歷史數據生成器已關閉。")
