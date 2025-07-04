# python/scheduled_data_trigger.py
import redis
import time
import schedule
import logging
import sys
import json
import requests
import threading
import os

# 現在此腳本與 config.py 在同一目錄下，可以直接導入
import config # 導入 /Users/ilawusong/Documents/sysWawIot/sys180/python/config.py

# --- 日誌設定 ---
LOG_FILE_PATH = '/www/wwwroot/syswaw/storage/logs/scheduled_trigger.log' # 日誌文件路徑
os.makedirs(os.path.dirname(LOG_FILE_PATH), exist_ok=True)

logging.basicConfig(
    level=logging.WARNING,
    format='%(asctime)s - %(threadName)s - %(levelname)s - %(message)s', # 添加 threadName
    handlers=[
        logging.FileHandler(LOG_FILE_PATH, mode='a'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

# --- 設定 ---
REDIS_TRIGGER_CHANNEL = 'open_gate_channel' # 觸發擷取的頻道
REDIS_CONTROL_CHANNEL = 'redis_ctrlScheduleTrigger' # 接收控制命令的頻道
REDIS_STATUS_KEY = 'tcp_schedule_status' # 儲存狀態的 Redis Key
DEFAULT_INTERVAL_HOURS = 6 # 預設間隔
TRIGGER_MESSAGE = "scheduled_trigger" # 發送到 Redis 的訊息內容

# 從 config.py 獲取基礎 URL，然後構建完整的 API 路徑
API_BASE_URL = getattr(config, 'API_BASE_URL', 'https://sxs.tg25.win') # 提供預設值以防萬一
LARAVEL_API_SCHEDULE_STATUS_URL = API_BASE_URL.rstrip('/') + '/api/tcp-schedule-status' # 回報狀態的 API URL

# --- 全局狀態變數 ---
current_schedule_job = None
is_running = False
current_interval = None
redis_client_main = None # 主線程/監聽線程共享的 Redis 連接
redis_connection_lock = threading.Lock() # 保護 redis_client_main 的訪問

# --- 函數 ---
def trigger_data_capture():
    """連接 Redis 並發布觸發訊息到 open_gate_channel"""
    redis_client = None
    try:
        logger.info(f"準備觸發數據擷取，連接 Redis ({config.REDIS_HOST}:{config.REDIS_PORT} DB:{config.REDIS_DB})...")
        redis_client = redis.Redis(host=config.REDIS_HOST, port=config.REDIS_PORT, db=config.REDIS_DB)
        redis_client.ping() # 測試連線
        logger.info(f"Redis 連接成功，發布訊息 '{TRIGGER_MESSAGE}' 到頻道 '{REDIS_TRIGGER_CHANNEL}'")
        result = redis_client.publish(REDIS_TRIGGER_CHANNEL, TRIGGER_MESSAGE)
        logger.info(f"訊息發布完成，影響的訂閱者數量: {result}")
    except redis.exceptions.ConnectionError as e:
        logger.error(f"無法連接到 Redis: {e}")
    except Exception as e:
        logger.error(f"觸發數據擷取時發生錯誤: {e}")
    finally:
        if redis_client:
            redis_client.close()
            logger.debug("Redis 連接已關閉 (trigger_data_capture)")

def report_status_to_laravel(status, interval):
    """向 Laravel API 回報當前定時狀態，並更新 Redis Key"""
    global redis_client_main
    payload = {'status': status, 'interval': interval}
    try:
        logger.info(f"向 Laravel API ({LARAVEL_API_SCHEDULE_STATUS_URL}) 回報狀態: {payload}")
        headers = {"X-AUTH-TOKEN": config.TCP_API_KEY, "Accept": "application/json"} # 加上 API Key
        response = requests.post(LARAVEL_API_SCHEDULE_STATUS_URL, headers=headers, json=payload, timeout=10)
        response.raise_for_status() # 如果狀態碼不是 2xx，則拋出異常
        logger.info(f"Laravel API 回報成功: {response.json()}")
    except requests.exceptions.RequestException as e:
        logger.error(f"向 Laravel API 回報狀態失敗: {e}")

    # 更新 Redis Key (即使 API 失敗也要更新，讓 Laravel 頁面載入時能讀到)
    try:
        with redis_connection_lock: # 確保線程安全地訪問 redis_client_main
            if redis_client_main and redis_client_main.ping():
                 status_json = json.dumps(payload)
                 redis_client_main.set(REDIS_STATUS_KEY, status_json)
                 logger.info(f"已更新 Redis Key '{REDIS_STATUS_KEY}' 為: {status_json}")
            else:
                 logger.warning("無法連接 Redis，未能更新狀態 Key")
    except Exception as e:
        logger.error(f"更新 Redis Key '{REDIS_STATUS_KEY}' 失敗: {e}")


def handle_control_message(message):
    """處理來自 Redis 控制頻道的訊息"""
    global current_schedule_job, is_running, current_interval
    try:
        # 假設 message 已經是解碼後的字串 (如果 redis-py decode_responses=True)
        # 否則需要 message['data'].decode('utf-8')
        if isinstance(message['data'], bytes):
            data_str = message['data'].decode('utf-8')
        else:
            data_str = message['data']

        data = json.loads(data_str)
        action = data.get('action')
        logger.info(f"收到控制命令: {data}")

        if action == 'start_schedule':
            interval = data.get('interval', DEFAULT_INTERVAL_HOURS)
            if not isinstance(interval, int) or interval <= 0:
                logger.warning(f"無效的定時間隔: {interval}，使用預設值 {DEFAULT_INTERVAL_HOURS}")
                interval = DEFAULT_INTERVAL_HOURS

            logger.info(f"準備啟動定時任務，每 {interval} 小時執行一次。")
            schedule.clear() # 清除所有舊任務
            # 使用 schedule.every(interval).hours.do(...)
            current_schedule_job = schedule.every(interval).hours.do(trigger_data_capture)
            is_running = True
            current_interval = interval
            report_status_to_laravel('running', interval)
            logger.info(f"定時任務已啟動。")

        elif action == 'stop_schedule':
            logger.info("準備停止定時任務。")
            schedule.clear() # 清除所有任務
            current_schedule_job = None
            is_running = False
            current_interval = None
            report_status_to_laravel('stopped', None)
            logger.info("定時任務已停止。")

    except json.JSONDecodeError:
        logger.error(f"無法解析收到的控制命令: {data_str}")
    except Exception as e:
        logger.error(f"處理控制命令時發生錯誤: {e}")

def redis_listener_thread():
    """在獨立線程中監聽 Redis 控制頻道"""
    global redis_client_main
    pubsub = None
    while True: # 持續嘗試連接和監聽
        connection_successful = False
        try:
            # 在鎖內嘗試建立或檢查連接，並創建 pubsub 對象
            with redis_connection_lock:
                if not redis_client_main or not redis_client_main.ping():
                     logger.info("監聽線程：重新連接 Redis...")
                     if redis_client_main: # 關閉可能存在的舊連接
                         try: redis_client_main.close()
                         except: pass
                     # decode_responses=False 讓 listen 返回 bytes, handle_control_message 內部解碼
                     redis_client_main = redis.Redis(host=config.REDIS_HOST, port=config.REDIS_PORT, db=config.REDIS_DB, socket_timeout=None, decode_responses=False)
                     redis_client_main.ping()
                     logger.info("監聽線程：Redis 連接成功。")
                     pubsub = redis_client_main.pubsub(ignore_subscribe_messages=True)
                     pubsub.subscribe(**{REDIS_CONTROL_CHANNEL: handle_control_message})
                     logger.info(f"已訂閱 Redis 控制頻道: {REDIS_CONTROL_CHANNEL}")
                     connection_successful = True # 標記連接和訂閱成功
                elif not pubsub: # 連接可能正常，但 pubsub 未創建 (例如上次出錯)
                    logger.info("監聽線程：重新創建 PubSub 對象...")
                    pubsub = redis_client_main.pubsub(ignore_subscribe_messages=True)
                    pubsub.subscribe(**{REDIS_CONTROL_CHANNEL: handle_control_message})
                    logger.info(f"已重新訂閱 Redis 控制頻道: {REDIS_CONTROL_CHANNEL}")
                    connection_successful = True # 標記訂閱成功
                else:
                    connection_successful = True # 連接和 pubsub 都已存在且有效

            # 只有在連接和訂閱成功後才執行 listen
            if connection_successful and pubsub:
                logger.debug("監聽線程：開始 listen...")
                for message in pubsub.listen(): # 這會阻塞
                     logger.debug(f"監聽線程：收到原始訊息: {message}")
                     if message['type'] == 'message':
                         handle_control_message(message) # 處理訊息
                # 如果 listen() 正常退出 (例如連接斷開)
                logger.warning("Redis pubsub 連接斷開或 listen() 結束，將嘗試重新連接...")
            elif not connection_successful:
                 # 如果連接/訂閱不成功
                 logger.error("監聽線程：連接或訂閱 Redis 失敗，將在 10 秒後重試...")
                 # time.sleep(10) # 移到 finally 塊中

        except redis.exceptions.ConnectionError as e:
            logger.error(f"監聽線程 Redis 連接錯誤: {e}。將在 10 秒後重試...")
            # time.sleep(10) # 移到 finally 塊中
        except Exception as e:
            logger.error(f"監聽線程發生未知錯誤: {e}。將在 10 秒後重試...")
            # time.sleep(10) # 移到 finally 塊中
        finally:
            # 無論成功或失敗，重試前都清理狀態並等待
            logger.debug("監聽線程：進入 finally 塊，準備清理和等待...")
            with redis_connection_lock:
                if redis_client_main:
                    try:
                        redis_client_main.close()
                        logger.debug("監聽線程：已關閉舊的 Redis 連接。")
                    except Exception as close_err:
                        logger.error(f"監聽線程：關閉 Redis 連接時出錯: {close_err}")
                redis_client_main = None
                pubsub = None
            logger.debug("監聽線程：清理完成，等待 10 秒後重試...")
            time.sleep(10)

if __name__ == "__main__":
    logger.info(f"啟動定時數據擷取觸發器。")

    # 啟動時先報告停止狀態
    # 需要先建立一個臨時連接來報告初始狀態
    try:
        logger.info("啟動時：嘗試連接 Redis 報告初始狀態...")
        # 使用主連接變數來報告，如果成功，監聽線程就不用再連一次
        with redis_connection_lock:
            redis_client_main = redis.Redis(host=config.REDIS_HOST, port=config.REDIS_PORT, db=config.REDIS_DB, decode_responses=False)
            redis_client_main.ping()
        logger.info("啟動時：Redis 連接成功。")
        report_status_to_laravel('stopped', None)
    except Exception as e:
        logger.error(f"啟動時連接 Redis 或報告狀態失敗: {e}")
        # 即使失敗也繼續，監聽線程會嘗試重連
        with redis_connection_lock: # 確保失敗時連接被設為 None
            if redis_client_main:
                try: redis_client_main.close()
                except: pass
            redis_client_main = None

    # 啟動 Redis 監聽線程
    listener = threading.Thread(target=redis_listener_thread, name="RedisListenerThread", daemon=True)
    listener.start()
    logger.info("Redis 控制命令監聽線程已啟動。")

    logger.info("進入主循環，執行 schedule.run_pending()...")
    while True:
        try:
            schedule.run_pending()
            time.sleep(1)
        except KeyboardInterrupt:
             logger.info("收到 KeyboardInterrupt，準備退出主循環...")
             break
        except Exception as e:
             logger.error(f"主循環發生錯誤: {e}")
             time.sleep(5) # 發生錯誤時稍等片刻

    logger.info("主循環結束，程式退出。")
    # 可以在這裡添加額外的清理邏輯，但因為線程是 daemon，會隨主線程退出
