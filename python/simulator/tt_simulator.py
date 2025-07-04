import logging, socket, time, random, threading, os, sys
import mysql.connector

# 配置
SERVER_IP = "127.0.0.1"
SERVER_PORT = 39001

# MySQL 資料庫配置
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'your_db_user', # <-- 請替換為您的資料庫用戶名
    'password': 'your_db_password', # <-- 請替換為您的資料庫密碼
    'database': 'syswaw',
    'raise_on_warnings': True
}

# 設置日誌
logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)
# 如果沒有Handler，則添加StreamHandler
if not logger.handlers:
    ch = logging.StreamHandler()
    formatter = logging.Formatter('%(asctime)s - %(levelname)s - [%(name)s] %(message)s')
    ch.setFormatter(formatter)
    logger.addHandler(ch)

# 測試用的 Chip IDs 和 Tokens
TEST_CHIP_IDS = [
    "iot001","iot002","iot003",
    "iot005","iot006","iot007","iot008","iot009","iot010","gmb001",
]
TEST_TOKEN = [
    "4c0c6435","4283c91d","1278a3b5",
    "7b9e2f1a","3c4d8e6b","9a1f5c2d","6e3b7f9a","2d5a1c8e","8f6b3e4d","yT5kpIza", # <-- 請確認 gmb001 對應的是 yT5kpIza 還是 tzVxfN7D
]

# 機型配置 (用於資料庫查找失敗時的預設值)
MACHINE_TYPES = {
    "iot001":"pinball","iot002":"pinball","iot003":"pinball",
    "iot005":"lottery","iot006":"lottery","iot007":"bill","iot008":"bill",
    "iot009":"pinball","iot010":"pinball","gmb001":"gambling",
}

# 紙鈔計數映射（對應 bill_mappings.php 的 TWD）
BILL_DENOMINATION_COUNTS = [1, 5, 10] # 1=100, 5=500, 10=1000

# 發送頻率
SEND_INTERVAL = 3 # 秒

# 全局字典用於存儲機台配置，避免重複查詢資料庫
machine_configs = {}

# 預設機台配置（當資料庫無配置或錯誤時使用）
DEFAULT_MACHINE_CONFIG = {
    'machine_type': 'pinball',
    'units_per_credit': 10,
    'payout_type': 'none',
    'payout_unit_value': 0,
    'is_active': True # 預設為啟用
}

def get_machine_config_from_db(chip_id):
    if chip_id in machine_configs:
        return machine_configs[chip_id]

    conn = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        # 1. 根據 chip_hardware_id (即 simulator 的 chip_id) 查找 auth_key
        # 日誌中的 chip_id，對應資料庫中的 chip_hardware_id
        cursor.execute(f"SELECT auth_key FROM machine_auth_keys WHERE chip_hardware_id = '{chip_id}'")
        auth_key_from_chip = cursor.fetchone()

        if not auth_key_from_chip:
            logger.warning(f"[{chip_id}] 在 machine_auth_keys 中找不到 chip_hardware_id 為 '{chip_id}' 的驗證金鑰。使用預設配置。")
            machine_configs[chip_id] = DEFAULT_MACHINE_CONFIG
            return DEFAULT_MACHINE_CONFIG

        token_value = auth_key_from_chip['auth_key']

        # 2. 根據 auth_key 再次查找其完整資訊（包含 status 和 machine_id）
        cursor.execute(f"SELECT id, machine_id, status FROM machine_auth_keys WHERE auth_key = '{token_value}'")
        auth_key_details = cursor.fetchone()

        if not auth_key_details:
            # 正常情況下不會進入此處，除非數據不一致
            logger.error(f"[{chip_id}] 內部邏輯錯誤：找到 chip_hardware_id 但無法通過 auth_key '{token_value}' 再次查詢。")
            machine_configs[chip_id] = DEFAULT_MACHINE_CONFIG
            return DEFAULT_MACHINE_CONFIG

        auth_key_id = auth_key_details['id']
        auth_key_status = auth_key_details['status']
        linked_machine_id = auth_key_details['machine_id']

        # 檢查驗證金鑰本身的狀態
        if auth_key_status != 'active':
            logger.info(f"[{chip_id}] 驗證金鑰 '{token_value}' 狀態為 '{auth_key_status}'，機台未啟用。暫停模擬。")
            machine_configs[chip_id] = {'is_active': False}
            return {'is_active': False}

        # 檢查是否關聯了機台
        if linked_machine_id is None:
            logger.warning(f"[{chip_id}] 驗證金鑰 '{token_value}' (ID:{auth_key_id}) 未關聯任何機台。使用預設配置。")
            machine_configs[chip_id] = DEFAULT_MACHINE_CONFIG
            return DEFAULT_MACHINE_CONFIG

        # 3. 查找 machines 表中的機台配置
        cursor.execute(f"SELECT machine_type, payout_type, payout_unit_value, coin_input_value, is_active FROM machines WHERE id = {linked_machine_id}")
        machine_result = cursor.fetchone()

        if machine_result:
            # 檢查 machines 表中機台的 is_active 狀態
            if not bool(machine_result['is_active']):
                logger.info(f"[{chip_id}] 機台 (ID:{linked_machine_id}) 未啟用 (is_active=false)。暫停模擬。")
                machine_configs[chip_id] = {'is_active': False}
                return {'is_active': False}

            config = {
                'machine_type': machine_result['machine_type'],
                'units_per_credit': machine_result['coin_input_value'] if machine_result['coin_input_value'] is not None else 10,
                'payout_type': machine_result['payout_type'],
                'payout_unit_value': machine_result['payout_unit_value'],
                'is_active': True
            }
            logger.info(f"[{chip_id}] 成功從資料庫加載配置。")
            machine_configs[chip_id] = config
            return config
        else:
            # 驗證金鑰關聯的機台在 machines 表中不存在
            logger.warning(f"[{chip_id}] 驗證金鑰 '{token_value}' 關聯的機台 (ID:{linked_machine_id}) 不存在於 machines 表中。使用預設配置。")
            machine_configs[chip_id] = DEFAULT_MACHINE_CONFIG
            return DEFAULT_MACHINE_CONFIG

    except mysql.connector.Error as err:
        logger.error(f"[{chip_id}] 資料庫連接或查詢錯誤: {err}")
        machine_configs[chip_id] = DEFAULT_MACHINE_CONFIG # 發生 DB 錯誤時，仍使用預設配置
        return DEFAULT_MACHINE_CONFIG
    finally:
        if conn:
            conn.close()

def simulate_esp32(chip_id, token):
    # 此處的 token 是來自 TEST_TOKEN 陣列，用於發送給伺服器。
    # 資料庫查詢邏輯會使用 chip_id 查找實際的 auth_key。

    # 移除開頭的 time.sleep(8)，在多線程中不適用
    logger.info(f"[{chip_id}] 模擬器啟動。")

    while True:
        client_socket = None
        try:
            # 從資料庫獲取機台配置
            config = get_machine_config_from_db(chip_id)

            if not config['is_active']:
                logger.info(f"[{chip_id}] 機台未啟用，等待 60 秒後重試。")
                time.sleep(60)
                continue # 跳過本次循環，重新檢查配置

            # 使用從資料庫獲取的實際 token，而不是 TEST_TOKEN[idx]
            # 為了讓這裡的 token 與資料庫匹配，需要修改 get_machine_config_from_db 返回 token。
            # 或者，更簡單地，確認 TEST_TOKEN 陣列與資料庫是同步的。
            # For now, let's just use the 'token' passed in from TEST_TOKEN array for sending.
            # The DB lookup inside get_machine_config_from_db uses chip_id.

            machine_type = config['machine_type']
            units_per_credit = config['units_per_credit']
            payout_type = config['payout_type']
            payout_unit_value = config['payout_unit_value']

            client_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            client_socket.settimeout(10) # 連接和接收超時時間
            client_socket.connect((SERVER_IP, SERVER_PORT))
            logger.info(f"[{chip_id}] 連接到伺服器。")

            while True:
                # 模擬數據
                credit_in = random.randint(0, 5) * units_per_credit
                ball_in = 0
                ball_out = random.randint(0, 10) * units_per_credit
                coin_out = random.randint(0, 2) * units_per_credit
                assign_credit = 0
                settled_credit = 0
                bill_denomination = 0

                # 模擬紙鈔投入 (iot007, iot008 是 bill acceptor)
                if machine_type == "bill":
                    if random.random() < 0.3: # 30% 機會投入紙鈔
                        bill_count = random.choice(BILL_DENOMINATION_COUNTS)
                        # 這需要一個實際的 bill_mappings 查找，這裡只是簡單演示
                        # 假設 bill_count 1 -> 100, 5 -> 500, 10 -> 1000
                        bill_denomination = {1:100, 5:500, 10:1000}.get(bill_count, 0)
                        logger.info(f"[{chip_id}] 模擬投入紙鈔: {bill_denomination} 元 ({bill_count} 張/枚)")


                data_payload = {
                    "chip_id": chip_id,
                    "token": token, # 使用傳入的 token
                    "machine_type": machine_type,
                    "credit_in": credit_in,
                    "ball_in": ball_in,
                    "ball_out": ball_out,
                    "coin_out": coin_out,
                    "assign_credit": assign_credit,
                    "settled_credit": settled_credit,
                    "bill_denomination": bill_denomination,
                    "timestamp": int(time.time()),
                }
                data_to_send = json.dumps(data_payload) + "\n" # 添加換行符以標記消息結束

                logger.info(f"[{chip_id}] 發送: {data_to_send.strip()}")
                client_socket.sendall(data_to_send.encode('utf-8'))

                try:
                    response = client_socket.recv(1024).decode('utf-8').strip()
                    logger.info(f"[{chip_id}] 收到: {response}")
                    if response != "OK":
                        logger.warning(f"[{chip_id}] 伺服器發送非 OK 回應: {response}. 重新連接...")
                        break # 非 OK 回應，重新連接
                except socket.timeout:
                    logger.warning(f"[{chip_id}] 等待回應超時。")
                    break
                except socket.error as e:
                    logger.warning(f"[{chip_id}] 接收回應失敗: {e}. 重新連接...")
                    break
                except Exception as e:
                    logger.warning(f"[{chip_id}] 解析回應錯誤: {e}. 重新連接...")
                    break

                time.sleep(SEND_INTERVAL)

        except ConnectionRefusedError:
            logger.warning(f"[{chip_id}] 無法連接伺服器 ({SERVER_IP}:{SERVER_PORT})，請確認伺服器正在運行。")
        except socket.timeout:
            logger.warning(f"[{chip_id}] 連接伺服器超時。")
        except Exception as e:
            logger.error(f"[{chip_id}] 發生未預期錯誤: {e}")

        logger.info(f"[{chip_id}] 將在 {SEND_INTERVAL * 2} 秒後重連...")
        time.sleep(SEND_INTERVAL * 2)

if __name__ == "__main__":
    # 需要先安裝 mysql-connector-python: pip install mysql-connector-python
    # 確保資料庫連接配置正確

    # 設置 Python 的日誌
    logging.basicConfig(level=logging.WARNING, format='%(asctime)s - %(levelname)s - [%(name)s] %(message)s')
    # 確保不會重複添加 handler
    if not logger.handlers:
        ch = logging.StreamHandler()
        formatter = logging.Formatter('%(asctime)s - %(levelname)s - [%(name)s] %(message)s')
        ch.setFormatter(formatter)
        logger.addHandler(ch)

    threads = []
    for idx, chip_id in enumerate(TEST_CHIP_IDS):
        token = TEST_TOKEN[idx] # 從 TEST_TOKEN 獲取對應的 token
        thread = threading.Thread(
            target=simulate_esp32,
            args=(chip_id, token),
            daemon=True # 設置為守護線程，主程序結束時自動停止
        )
        threads.append(thread)
        thread.start()
        # 讓線程錯開一點時間啟動，避免同時連接造成壓力
        time.sleep(0.1)

    try:
        # 主線程持續運行，保持子線程活躍
        while True:
            # 可以添加一些檢查，例如有多少活躍線程
            active_threads = [t for t in threads if t.is_alive()]
            if not active_threads:
                logger.info("所有模擬器線程都已停止。")
                break
            # 每隔一段時間檢查一次
            time.sleep(5)
    except KeyboardInterrupt:
        logger.info("檢測到 Ctrl+C，正在關閉模擬器...")
    finally:
        # 這裡不需要顯式 join 守護線程，它們會隨主線程結束
        logger.info("模擬器已關閉。")
