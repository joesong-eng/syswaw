import socket, threading, re, redis, mysql.connector, json, time, signal, sys, logging
from datetime import datetime, timedelta
import requests # 新增：用於向 Laravel 發送通知的 HTTP 請求

# 引入配置檔，請確保 config.py 中包含所有需要的變數
from config import (
    SERVER_IP, SERVER_PORT,
    REDIS_HOST, REDIS_PORT, REDIS_DB,
    MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE,
    # 新增：Laravel 通知 API 的 URL
    LARAVEL_NOTIFICATION_API_URL # 請在 config.py 中定義此變數
)

# 記錄每個 machine_id 的上一筆數據 (用於簡單的重複數據判斷)
last_data = {}

# Redis 連線
try:
    redis_client = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB, decode_responses=True) # decode_responses=True 直接解碼為字串
    redis_client.ping() # 測試連線
    print("成功連接到 Redis")
except redis.exceptions.ConnectionError as e:
    print(f"連接到 Redis 失敗: {e}")
    logging.error(f"連接到 Redis 失敗: {e}")
    sys.exit(1) # 連接 Redis 失敗，程式退出

# 用於追蹤需要抓取下一筆數據的 Chip ID 列表
# { chip_id: True } 表示該 chip_id 正在等待下一筆數據來觸發抓取
# 我們使用字典而不是集合，方便未來擴展（例如記錄請求時間或請求 ID）
chip_ids_to_capture = {}
# 由於 handle_client 和 redis_listener 執行在不同線程，需要鎖來保護共用資源
capture_lock = threading.Lock()


def handle_client(client_socket, client_address):
    print(f"接受來自 {client_address} 的連線")
    logging.info(f"接受來自 {client_address} 的連線")
    with client_socket:
        try:
            client_socket.sendall(b"connected")
        except socket.error as e:
            print(f"發送連接成功訊息失敗: {e}")
            logging.error(f"發送連接成功訊息失敗: {e}")
            return

        while True:
            try:
                # 設置接收超時，防止線程阻塞在 recv
                client_socket.settimeout(60) # 例如 60 秒
                data = client_socket.recv(1024).decode('utf-8').strip()
                if not data:
                    # 連線已關閉或對端發送了空數據
                    break
                client_socket.settimeout(None) # 恢復阻塞模式

            except socket.timeout:
                # 接收超時，可能連線已斷開，或者對端長時間沒有發送數據
                print(f"接收來自 {client_address} 的數據超時，關閉連線")
                logging.warning(f"接收來自 {client_address} 的數據超時，關閉連線")
                break
            except socket.error as e:
                 print(f"接收來自 {client_address} 的數據時發生錯誤: {e}")
                 logging.error(f"接收來自 {client_address} 的數據時發生錯誤: {e}")
                 break
            except Exception as e:
                 print(f"處理接收數據時發生未知錯誤: {e}")
                 logging.error(f"處理接收數據時發生未知錯誤: {e}")
                 break


            print(f"收到數據: {data}")
            logging.info(f"收到原始數據: {data}")

            # 數據解析和驗證
            # 假設數據格式為 @晶片ID:Token#進球 投入金額 出球\n? (注意這裡的 ball_in 和 credit_in 位置與你前面 Schema 提供的不一致)
            # 根據你提供的 Schema: machine_id, token, ball_in, ball_out, credit_in
            # 根據你的數據格式: @(晶片ID):(Token)#(進球) (投入金額) (出球)
            # 我們需要重新對應捕獲組
            match = re.match(r'@(.*?):(.*?)#(.*?) (.*?) (.*?)\n?', data)
             #              ^1^   ^2^    ^3^   ^4^   ^5^
             # 應該對應到: ChipID, Token, BallIn, CreditIn, BallOut
            if not match:
                print(f"數據格式錯誤，不符合 @ChipID:Token#BallIn CreditIn BallOut 格式: {data}")
                logging.warning(f"數據格式錯誤: {data}")
                continue

            # 根據新的對應關係獲取數據
            chip_id, token, ball_in_str, credit_in_str, ball_out_str = match.groups()

            # 數據重複判斷 (使用 Chip ID 作為判斷依據)
            if chip_id in last_data and last_data[chip_id] == data:
                print(f"數據重複，Chip ID: {chip_id}")
                logging.info(f"數據重複，Chip ID: {chip_id}")
                continue

            last_data[chip_id] = data # 更新該 Chip ID 的上一筆數據

            # Chip ID 白名單判斷 (可選，如果需要的話)
            # if not redis_client.sismember("chip_id_whitelist", chip_id): # 假設白名單鍵改為 chip_id_whitelist
            #     print(f"Chip ID {chip_id} 不在白名單中，忽略數據")
            #     logging.warning(f"Chip ID {chip_id} 不在白名單中，忽略數據")
            #     continue

            # 數據類型轉換和驗證
            try:
                ball_in = int(ball_in_str) if ball_in_str else 0
                ball_out = int(ball_out_str) if ball_out_str else 0
                credit_in = int(credit_in_str) if credit_in_str else 0
            except ValueError as e:
                print(f"數據類型轉換錯誤 (進球/投入金額/出球)，Chip ID: {chip_id}, 錯誤: {e}")
                logging.warning(f"數據類型轉換錯誤，Chip ID: {chip_id}, 錯誤: {e}")
                continue

            current_timestamp = time.strftime("%Y-%m-%d %H:%M:%S")

            # ====== 新增：檢查是否需要觸發數據抓取流程 ======
            should_trigger_capture = False
            with capture_lock:
                # 檢查這個 Chip ID 是否在待抓取列表中並且標記為 True
                if chip_id in chip_ids_to_capture and chip_ids_to_capture[chip_id] is True:
                    should_trigger_capture = True
                    # 標記為已處理，以便下一筆數據不再觸發
                    chip_ids_to_capture[chip_id] = False # 或者可以直接 del chip_ids_to_capture[chip_id]


            if should_trigger_capture:
                print(f"【觸發抓取】收到待抓取機台的數據，Chip ID: {chip_id}")
                logging.info(f"【觸發抓取】收到待抓取機台的數據，Chip ID: {chip_id}")

                # Step 5: 將這筆被觸發抓取的數據暫存到 Redis
                captured_record_data = {
                    "chip_id": chip_id,
                    "token": token,
                    "ball_in": ball_in,
                    "ball_out": ball_out,
                    "credit_in": credit_in,
                    "timestamp": current_timestamp # 使用當前伺服器時間或數據中的時間（如果包含）
                }
                # 設計 Redis Key 結構，例如： capture:【Chip ID】:【時間戳_毫秒】
                # 使用一個唯一的後綴確保 Key 的唯一性
                redis_capture_key = f"captured_data:{chip_id}:{int(time.time() * 1000)}_{token}" # 結合時間戳和 Token 增加唯一性
                try:
                    # 將數據寫入 Redis，設置一個過期時間（例如 300 秒 = 5 分鐘），防止數據殘留
                    redis_client.set(redis_capture_key, json.dumps(captured_record_data), ex=300)
                    print(f"【觸發抓取】數據暫存到 Redis Key: {redis_capture_key}")
                    logging.info(f"【觸發抓取】數據暫存到 Redis Key: {redis_capture_key}")

                    # Step 6: 通知 Laravel 後端數據已抓取並暫存
                    notification_payload = {
                        "chip_id": chip_id,
                        "redis_key": redis_capture_key,
                        # 可以包含其他需要立即通知的資訊，但數據本身 Laravel 會去 Redis 讀取
                    }
                    try:
                        # 發送 POST 請求通知 Laravel
                        response = requests.post(LARAVEL_NOTIFICATION_API_URL, json=notification_payload, timeout=10) # 設置超時
                        if response.status_code == 200:
                            print(f"【觸發抓取】成功通知 Laravel ({chip_id})")
                            logging.info(f"【觸發抓取】成功通知 Laravel ({chip_id})")
                        else:
                            print(f"【觸發抓取】通知 Laravel 失敗 ({chip_id}), 狀態碼: {response.status_code}, 響應: {response.text}")
                            logging.error(f"【觸發抓取】通知 Laravel 失敗 ({chip_id}), 狀態碼: {response.status_code}, 響應: {response.text}")
                            # TODO: 增加錯誤處理，例如記錄失敗的通知，考慮重試機制

                    except requests.exceptions.RequestException as e:
                        print(f"【觸發抓取】通知 Laravel 發生異常 ({chip_id}): {e}")
                        logging.error(f"【觸發抓取】通知 Laravel 發生異常 ({chip_id}): {e}")
                        # TODO: 增加異常處理，記錄異常，考慮重試

                except Exception as e:
                    print(f"【觸發抓取】暫存數據到 Redis 或通知 Laravel 失敗 ({chip_id}): {e}")
                    logging.error(f"【觸發抓取】暫存數據到 Redis 或通知 Laravel 失敗 ({chip_id}): {e}")

            # ====== 現有數據處理邏輯 (對於非觸發抓取的數據，以及觸發抓取後的同一 Chip ID 的後續數據) ======
            # 這部分保留原有的邏輯，用於將所有收到的數據（無論是否觸發）的最新一筆寫入 machine_data Hash
            # 這樣 transfer_old_data 函數仍然可以處理那些沒有通過觸發抓取流程的數據
            redis_latest_data = {
                "chip_id": chip_id, # 使用 chip_id
                "token": token,
                "ball_in": ball_in,
                "ball_out": ball_out,
                "credit_in": credit_in,
                "timestamp": current_timestamp # 使用當前伺服器時間
            }
            # 將該 Chip ID 的最新數據儲存到 machine_data Hash
            try:
                redis_client.hset("machine_data", chip_id, json.dumps(redis_latest_data))
                # print(f"Chip ID {chip_id} 最新數據已儲存到 machine_data Hash")
                # logging.debug(f"Chip ID {chip_id} 最新數據已儲存到 machine_data Hash") # 避免過多日誌
            except Exception as e:
                print(f"儲存最新數據到 machine_data Hash 失敗 ({chip_id}): {e}")
                logging.error(f"儲存最新數據到 machine_data Hash 失敗 ({chip_id}): {e}")


            # print(f"處理完成數據，Chip ID: {chip_id}") # 避免過多打印

        # while 迴圈結束，連線關閉
        print(f"與 {client_address} 的連線已關閉")
        logging.info(f"與 {client_address} 的連線已關閉")


# 數據轉移函數 (保持原樣，它處理 machine_data Hash 中的數據)
# 它不會處理通過觸發抓取流程儲存的 captured_data: 前綴的 Key
def transfer_old_data():
     # ... 你的 transfer_old_data 函數程式碼 ...
    print("數據轉移線程已啟動")
    logging.info("數據轉移線程已啟動")
    while True:
        try:
            # 取得 3 小時前的時間
            three_hours_ago = datetime.now() - timedelta(hours=3)
            three_hours_ago_str = three_hours_ago.strftime("%Y-%m-%d %H:%M:%S")

            # 取得 Redis 中所有的 machine_data Hash 的 Key (即 Chip ID)
            machine_ids = redis_client.hkeys("machine_data")

            for chip_id in machine_ids: # 這裡的 machine_id 實際上是 Chip ID
                data_str = redis_client.hget("machine_data", chip_id)
                if data_str:
                    try:
                         data = json.loads(data_str)
                         timestamp_str = data.get("timestamp") # 使用 get 防止 key 不存在
                         if timestamp_str and timestamp_str < three_hours_ago_str:
                            # 將資料寫入 MySQL
                            try:
                                # 使用 with 語句確保連接和 cursor 自動關閉
                                with mysql.connector.connect(host=MYSQL_HOST, user=MYSQL_USER, password=MYSQL_PASSWORD, database=MYSQL_DATABASE) as mysql_connection:
                                     with mysql_connection.cursor() as mysql_cursor:
                                        # 確保欄位名稱和數量與你的 machine_data_records 表一致
                                        sql = "INSERT INTO machine_data_records (chip_id, token, ball_in, ball_out, credit_in, timestamp, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())"
                                        val = (data.get("chip_id"), data.get("token"), data.get("ball_in"), data.get("ball_out"), data.get("credit_in"), data.get("timestamp"))
                                        mysql_cursor.execute(sql, val)
                                        mysql_connection.commit()
                                        print(f"【數據轉移】資料 Chip ID: {chip_id} 已轉移到 MySQL")
                                        logging.info(f"【數據轉移】資料 Chip ID: {chip_id} 已轉移到 MySQL")

                                        # 從 Redis 中刪除資料
                                        redis_client.hdel("machine_data", chip_id)
                                        print(f"【數據轉移】已從 Redis machine_data Hash 中刪除 Chip ID: {chip_id}")
                                        logging.info(f"【數據轉移】已從 Redis machine_data Hash 中刪除 Chip ID: {chip_id}")

                            except mysql.connector.Error as error:
                                print(f"【數據轉移】MySQL 錯誤，Chip ID: {chip_id}, 錯誤: {error}")
                                logging.error(f"【數據轉移】MySQL 錯誤，Chip ID: {chip_id}, 錯誤: {error}")
                            except Exception as e:
                                print(f"【數據轉移】處理 Chip ID: {chip_id} 轉移時發生未知錯誤: {e}")
                                logging.error(f"【數據轉移】處理 Chip ID: {chip_id} 轉移時發生未知錯誤: {e}")

                         # else:
                             # print(f"Chip ID {chip_id} 資料時間未超過 3 小時") # 避免過多打印

                    except json.JSONDecodeError:
                        print(f"【數據轉移】解析 Redis 中的 JSON 數據失敗，Chip ID: {chip_id}, 數據: {data_str}")
                        logging.error(f"【數據轉移】解析 Redis 中的 JSON 數據失敗，Chip ID: {chip_id}, 數據: {data_str}")
                        # 可以考慮刪除這個無法解析的數據以防止阻塞
                        # redis_client.hdel("machine_data", chip_id)
                    except Exception as e:
                         print(f"【數據轉移】處理 Chip ID: {chip_id} 時發生未知錯誤: {e}")
                         logging.error(f"【數據轉移】處理 Chip ID: {chip_id} 時發生未知錯誤: {e}")


            # 每 1 小時執行一次資料轉移
            time.sleep(3600)

        except Exception as e:
            print(f"【數據轉移】數據轉移主迴圈發生錯誤: {e}")
            logging.error(f"【數據轉移】數據轉移主迴圈發生錯誤: {e}")
            time.sleep(60)  # 發生錯誤時，每 1 分鐘重試一次


# 新增：Redis Pub/Sub 監聽線程函數
def redis_listener():
    """監聽 Redis Pub/Sub Channel 以接收來自 Laravel 的指令。"""
    # 使用一個新的 Redis 連接進行 Pub/Sub，這是推薦的做法
    pubsub_client = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB, decode_responses=True)
    pubsub = pubsub_client.pubsub()

    # 訂閱 Laravel 發佈 Chip ID 列表的 Channel
    pubsub.subscribe("open_gate_channel") # 訂閱 Laravel 指定的頻道
    print("Redis 監聽器已啟動，訂閱頻道：open_gate_channel")
    logging.info("Redis 監聽器已啟動，訂閱頻道：open_gate_channel")

    # 監聽訊息
    for message in pubsub.listen():
        if message["type"] == "message":
            try:
                # 訊息數據是 Laravel 發佈的 Chip ID 列表的 JSON 字串
                chip_ids_json = message["data"] # decode_responses=True 已經處理解碼

                # 解析 JSON 字串為 Python 列表
                chip_ids_list = json.loads(chip_ids_json)

                if not isinstance(chip_ids_list, list):
                    print(f"收到 Redis 訊息格式錯誤，預期為列表: {chip_ids_json}")
                    logging.warning(f"收到 Redis 訊息格式錯誤，預期為列表: {chip_ids_json}")
                    continue

                print(f"收到來自 Redis 的待抓取 Chip IDs 列表: {chip_ids_list}")
                logging.info(f"收到來自 Redis 的待抓取 Chip IDs 列表: {chip_ids_list}")

                # 使用鎖保護對 chip_ids_to_capture 的修改
                with capture_lock:
                    for chip_id in chip_ids_list:
                        # 將收到的 Chip ID 添加到待抓取列表中，並標記為 True
                        chip_ids_to_capture[str(chip_id)] = True # 確保字典的 key 是字串類型

                print(f"更新後待抓取列表 (Chip IDs): {list(chip_ids_to_capture.keys())}")
                logging.info(f"更新後待抓取列表 (Chip IDs): {list(chip_ids_to_capture.keys())}")

            except json.JSONDecodeError:
                 print(f"解析 Redis 訊息中的 JSON 數據失敗: {message['data']}")
                 logging.error(f"解析 Redis 訊息中的 JSON 數據失敗: {message['data']}")
            except Exception as e:
                print(f"處理 Redis 訊息時發生未知錯誤: {e}, 訊息數據: {message['data']}")
                logging.error(f"處理 Redis 訊息時發生未知錯誤: {e}, 訊息數據: {message['data']}")


# 信號處理函數 (保持原樣)
def signal_handler(sig, frame):
    print("收到 Ctrl+C，正在停止 TCP 伺服器...")
    logging.info("收到 Ctrl+C，正在停止 TCP 伺服器，正在退出...")
    sys.exit(0)


def main():
    # 配置日誌
    logging.basicConfig(filename='/www/wwwroot/syswaw/storage/logs/tcp_server.log', level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
    print("TCP 伺服器主程式啟動")
    logging.info("TCP 伺服器主程式啟動")

    # 初始化 TCP 伺服器 Socket
    server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

    try:
        server_socket.bind((SERVER_IP, SERVER_PORT))
        server_socket.listen(5)
        print(f"TCP 伺服器在 {SERVER_IP}:{SERVER_PORT} 上監聽...")
        logging.info(f"TCP 伺服器在 {SERVER_IP}:{SERVER_PORT} 上監聽...")
    except OSError as e:
        logging.error(f"綁定端口失敗: {e}")
        print(f"綁定端口失敗: {e}")
        server_socket.close() # 確保 socket 在失敗時關閉
        sys.exit(1) # 綁定失敗，程式退出

    # 啟動數據轉移線程 (處理 machine_data Hash 中的數據)
    transfer_thread = threading.Thread(target=transfer_old_data)
    transfer_thread.daemon = True # 設置為守護線程，主線程退出時它也會退出
    transfer_thread.start()

    # 啟動 Redis Pub/Sub 監聽線程
    redis_thread = threading.Thread(target=redis_listener)
    redis_thread.daemon = True # 設置為守護線程
    redis_thread.start()

    # 註冊信號處理器
    signal.signal(signal.SIGINT, signal_handler)
    # signal.signal(signal.SIGTERM, signal_handler) # 可選：處理 TERM 信號

    # 主迴圈：只負責接受新的 TCP 連線並為其創建處理線程
    while True:
        try:
            print("等待新的 TCP 連線...")
            client_socket, client_address = server_socket.accept()
            # 為每個客戶端連線創建一個新的線程來處理
            thread = threading.Thread(target=handle_client, args=(client_socket, client_address))
            thread.daemon = True # 設置為守護線程
            thread.start()
            print(f"已為 {client_address} 啟動新的客戶端處理線程")
            logging.info(f"已為 {client_address} 啟動新的客戶端處理線程")

        except OSError as e:
             # accept 發生錯誤，可能是伺服器 socket 已關閉
             print(f"TCP 伺服器 accept 發生錯誤: {e}")
             logging.error(f"TCP 伺服器 accept 發生錯誤: {e}")
             break # 退出主迴圈

        except Exception as e:
             # 捕獲主迴圈中的其他潛在異常
             print(f"主迴圈發生未知異常: {e}")
             logging.error(f"主迴圈發生未知異常: {e}")
             time.sleep(1) # 稍作等待，避免緊密迴圈


    # 主迴圈結束，關閉伺服器 Socket
    server_socket.close()
    print("TCP 伺服器已停止。")
    logging.info("TCP 伺服器已停止。")

    # 等待其他非守護線程結束（如果有的話），但我們已經將線程設置為守護線程
    # transfer_thread.join()
    # redis_thread.join()

    print("程式已安全退出。")


if __name__ == "__main__":
    main()