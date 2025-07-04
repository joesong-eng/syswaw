#python/simulator/old_simulator.py
import logging, socket, time, random, threading, os, sys
time.sleep(8)
logger = logging.getLogger(__name__)

# 配置
SERVER_IP = "127.0.0.1"
SERVER_PORT = 39001

# 測試用的 Chip IDs 和 Tokens
TEST_CHIP_IDS = [
    "iot001","iot002","iot003",
    "iot005","iot006","iot007","iot008","iot009","iot010","gmb001",
]
TEST_TOKEN = [
    "4c0c6435","4283c91d","1278a3b5",
    "7b9e2f1a","3c4d8e6b","9a1f5c2d","6e3b7f9a","2d5a1c8e","8f6b3e4d","yT5kpIza",
]

# 機型配置
MACHINE_TYPES = {
    "iot001":"pinball","iot002":"pinball","iot003":"pinball",
    "iot005":"lottery","iot006":"lottery","iot007":"bill","iot008":"bill",
    "iot009":"pinball","iot010":"pinball","gmb001":"gambling",
}

# 紙鈔計數映射（對應 bill_mappings.php 的 TWD）
BILL_DENOMINATION_COUNTS = [1, 5, 10]  # 1=100 元, 5=500 元, 10=1000 元
BILL_DENOMINATION_VALUES = {1: 100, 5: 500, 10: 1000}  # TWD 映射

# 日誌設定
LOG_FILE_PATH = '/www/wwwroot/syswaw/storage/logs/simulator.log'
os.makedirs(os.path.dirname(LOG_FILE_PATH), exist_ok=True)

# 數據發送間隔（秒）
SEND_INTERVAL = 10

def generate_data_state(chip_id):
    machine_type = MACHINE_TYPES.get(chip_id, "pinball")
    ball_in = random.randint(0, 100)
    ball_out = random.randint(0, 50)
    credit_in = random.randint(0, 200)
    coin_out = random.randint(0, 50)
    assign_credit = random.randint(0, 200)
    settled_credit = random.randint(0, 150)
    bill_denomination = 0  # 初始化累加計數

    def update():
        """隨機更新數據，增加 0 的機率"""
        nonlocal ball_in, ball_out, credit_in, coin_out, assign_credit, settled_credit, bill_denomination
        if machine_type == "pinball":
            ball_in += random.choice([0, 0, 0, 1])
            ball_out += random.choice([0, 0, 0, 1])
            credit_in += random.choice([0, 0, 0, 10])
            coin_out = 0
            assign_credit = 0
            settled_credit = 0
            bill_denomination = 0
        elif machine_type == "lottery":
            ball_in = 0
            ball_out = 0
            credit_in += random.choice([0, 0, 0, 10])
            coin_out += random.choice([0, 0, 0, 1])
            assign_credit = 0
            settled_credit = 0
            bill_denomination = 0
        elif machine_type == "gambling":
            ball_in = 0
            ball_out = 0
            credit_in += random.choice([0, 0, 0, 10])
            coin_out = 0
            assign_credit += random.choice([0, 0, 0, 10])
            settled_credit += random.choice([0, 0, 0, 10])
            increment = random.choice([0, 0, 0, *BILL_DENOMINATION_COUNTS])
            bill_denomination += increment
            credit_in += increment * BILL_DENOMINATION_VALUES.get(increment, 0)
        elif machine_type == "bill":
            ball_in = 0
            ball_out = 0
            increment = random.choice([0, 0, 0, *BILL_DENOMINATION_COUNTS])
            bill_denomination += increment
            credit_in += increment * BILL_DENOMINATION_VALUES.get(increment, 0)
            coin_out = 0
            assign_credit = 0
            settled_credit = 0

    def get_formatted_data(chip_id, token):
        """回傳格式化字串"""
        return f"@{chip_id}:{token}#{ball_in:06d} {credit_in:06d} {ball_out:06d} {coin_out:06d} {assign_credit:06d} {settled_credit:06d} {bill_denomination:06d}\n"

    return update, get_formatted_data

def simulate_esp32(chip_id, token):
    """模擬單個 ESP32 裝置"""
    print(f"[{chip_id}] 模擬器啟動...")
    update_data_state, get_formatted_data = generate_data_state(chip_id)
    data_to_send = ""
    while True:
        try:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as client_socket:
                print(f"[{chip_id}] 嘗試連接 {SERVER_IP}:{SERVER_PORT}...")
                client_socket.connect((SERVER_IP, SERVER_PORT))
                print(f"[{chip_id}] 連接成功！開始發送資料...")
                while True:
                    update_data_state()
                    data_to_send = get_formatted_data(chip_id, token)
                    print(f"[{chip_id}] 發送: {data_to_send.strip()}")

                    try:
                        client_socket.sendall(data_to_send.encode('utf-8'))
                    except socket.error as e:
                        print(f"[{chip_id}] 發送失敗，錯誤: {e}")
                        break

                    try:
                        response = client_socket.recv(1024)
                        if response:
                            print(f"[{chip_id}] 收到回應: {response.decode('utf-8').strip()}")
                        else:
                            print(f"[{chip_id}] 伺服器斷開連接")
                            break
                    except socket.timeout:
                        print(f"[{chip_id}] 等待回應超時")
                    except socket.error as e:
                        print(f"[{chip_id}] 接收回應失敗: {e}")
                        break

                    time.sleep(SEND_INTERVAL)

        except ConnectionRefusedError:
            print(f"[{chip_id}] 無法連接伺服器 ({SERVER_IP}:{SERVER_PORT})，請確認伺服器正在運行。")
        except socket.timeout:
            print(f"[{chip_id}] 連接伺服器超時。")
        except Exception as e:
            print(f"[{chip_id}] 發生未預期錯誤: {e}")

        if data_to_send:
            logger.info(f"[{chip_id}] 發送: {data_to_send.strip()}")
        print(f"[{chip_id}] 將在 {SEND_INTERVAL * 2} 秒後重連...")
        time.sleep(SEND_INTERVAL * 2)

if __name__ == "__main__":
    threads = []
    for idx, chip_id in enumerate(TEST_CHIP_IDS):
        token = TEST_TOKEN[idx]
        thread = threading.Thread(
            target=simulate_esp32,
            args=(chip_id, token),
            daemon=True
        )
        threads.append(thread)
        thread.start()
        time.sleep(0.1)

    try:
        while True:
            active_threads = [t for t in threads if t.is_alive()]
            if len(active_threads) < len(threads):
                print(f"警告：有 {len(threads) - len(active_threads)} 個模擬器線程已停止。")
                threads = active_threads
            time.sleep(5)
    except KeyboardInterrupt:
        print("\n模擬器停止中...")
    finally:
        print("模擬器已關閉。")