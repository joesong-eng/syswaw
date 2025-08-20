# mqtt_to_redis.py
import paho.mqtt.client as mqtt
import redis,sys
import json
import ssl
import time
import logging
import requests

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    filename='/www/wwwroot/syswaw/storage/logs/mqtt_to_redis.log', # 將日誌寫入指定文件
    filemode='a' # 追加模式
)
logger = logging.getLogger(__name__)

# --- MQTT 配置 ---
MQTT_HOST = "direct-mqtt.tg25.win"
MQTT_PORT = 8883
MQTT_DATA_TOPIC = "device/+/data/update"    # 用於接收所有設備的數據更新
MQTT_AUTH_TOPIC = "device/+/auth/request"    # 用於接收所有設備的認證請求
CA_CERT_PATH = "python_mqtt/certs/ca.crt"
CLIENT_CERT_PATH = "python_mqtt/certs/client.crt"
CLIENT_KEY_PATH = "python_mqtt/certs/client.key"

# --- Redis 配置 ---
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
REDIS_DB = 0
# REDIS_STREAM_NAME = "tcpstream" # 不再使用 Stream

# --- Laravel API 配置 ---
LARAVEL_API_URL = "https://sxs.tg25.win/api/machine-data"
LARAVEL_STATUS_API_URL = "https://sxs.tg25.win/api/machine-status" # 新增狀態更新 API

def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        logger.info("MQTT 連接成功！")
        # 訂閱數據更新主題
        client.subscribe(MQTT_DATA_TOPIC)
        logger.info(f"已訂閱數據主題: {MQTT_DATA_TOPIC}")
        # 訂閱認證請求主題
        client.subscribe(MQTT_AUTH_TOPIC)
        logger.info(f"已訂閱認證主題: {MQTT_AUTH_TOPIC}")

        # 新增：訂閱系統主題以監控客戶端連線/斷線
        client.subscribe("$SYS/broker/clients/connected")
        client.subscribe("$SYS/broker/clients/disconnected")
        logger.info("已訂閱客戶端上下線狀態主題。")
    else:
        logger.error(f"MQTT 連接失敗，返回碼: {rc}")

def post_status_to_laravel_api(chip_id, status):
    payload = {'chip_id': chip_id, 'status': status}
    try:
        response = requests.post(LARAVEL_STATUS_API_URL, json=payload, timeout=5)
        if response.status_code == 200:
            logger.info(f"成功將設備 '{chip_id}' 的狀態 '{status}' 發送到 Laravel API。")
        else:
            logger.error(f"發送狀態到 Laravel API 失敗，狀態碼: {response.status_code}, 回應: {response.text}")
    except requests.exceptions.RequestException as e:
        logger.error(f"呼叫狀態 API 時發生網路錯誤: {e}")

def post_to_laravel_api(data):
    try:
        response = requests.post(LARAVEL_API_URL, json=data, timeout=5) # 設定5秒超時
        if response.status_code == 200:
            logger.info(f"成功將數據發送到 Laravel API: {data.get('chip_id')}")
        else:
            logger.error(f"發送數據到 Laravel API 失敗，狀態碼: {response.status_code}, 回應: {response.text}")
    except requests.exceptions.RequestException as e:
        logger.error(f"呼叫 Laravel API 時發生網路錯誤: {e}")

def on_message(client, userdata, msg):
    try:
        # --- 處理上下線事件 ---
        if msg.topic == "$SYS/broker/clients/connected":
            # 大多數 broker 返回的是純文字 client_id，而非 JSON
            client_id = msg.payload.decode('utf-8').strip()

            # 過濾掉我們自己的監聽器，避免不必要的 API 請求
            if client_id and client_id != "mqtt_to_redis_bridge":
                logger.info(f"設備上線: {client_id}")
                post_status_to_laravel_api(client_id, 'online')
            return

        if msg.topic == "$SYS/broker/clients/disconnected":
            # 大多數 broker 返回的是純文字 client_id，而非 JSON
            client_id = msg.payload.decode('utf-8').strip()

            if client_id and client_id != "mqtt_to_redis_bridge":
                logger.info(f"設備離線: {client_id}")
                post_status_to_laravel_api(client_id, 'offline')
            return

        # 如果您的 broker 確實返回 JSON 格式，可以使用這個版本：
        """
        if msg.topic == "$SYS/broker/clients/connected":
            payload_str = msg.payload.decode('utf-8')
            try:
                # 先嘗試解析為 JSON
                payload = json.loads(payload_str)
                if isinstance(payload, dict):
                    client_id = payload.get('clientid')
                else:
                    client_id = None
            except json.JSONDecodeError:
                # 如果不是 JSON，則當作純文字處理
                client_id = payload_str.strip()

            if client_id and client_id != "mqtt_to_redis_bridge":
                logger.info(f"設備上線: {client_id}")
                post_status_to_laravel_api(client_id, 'online')
            return
        """

        # 其餘的資料處理邏輯保持不變...
        topic_parts = msg.topic.split('/')
        if len(topic_parts) < 3:
            logger.warning(f"收到的 Topic 格式不正確: {msg.topic}")
            return
        chip_id = topic_parts[1]

        message_data_str = msg.payload.decode('utf-8')
        message_json = json.loads(message_data_str)

        if not isinstance(message_json, dict):
            logger.warning(f"收到的消息格式不是預期的字典類型，已忽略。Topic: {msg.topic}, Payload: {message_data_str}")
            return

        if mqtt.topic_matches_sub(MQTT_DATA_TOPIC, msg.topic):
            logger.info(f"收到來自設備 '{chip_id}' 的數據更新: {message_data_str}")

            redis_client = userdata['redis_client']
            redis_key = f"machine_data:{chip_id}"
            redis_client.set(redis_key, message_data_str)

            api_payload = message_json.copy()
            api_payload['chip_id'] = chip_id
            post_to_laravel_api(api_payload)

        elif mqtt.topic_matches_sub(MQTT_AUTH_TOPIC, msg.topic):
            logger.info(f"收到來自設備 '{chip_id}' 的認證請求: {message_data_str}")

    except json.JSONDecodeError as e:
        logger.error(f"解析 MQTT 消息為 JSON 時發生錯誤: {e} - 原始消息: {msg.payload.decode('utf-8')}")
    except Exception as e:
        logger.error(f"處理訊息時發生未知錯誤: {e}")

def run_mqtt_to_redis_bridge():
    # 初始化 Redis 客戶端
    redis_client = redis.StrictRedis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB)
    try:
        redis_client.ping()
        logger.info("成功連接到 Redis。")
    except redis.exceptions.ConnectionError as e:
        logger.error(f"無法連接到 Redis: {e}")
        sys.exit(1)

    client = mqtt.Client(callback_api_version=mqtt.CallbackAPIVersion.VERSION2, client_id="mqtt_to_redis_bridge", protocol=mqtt.MQTTv311) # 顯式指定協議版本
    client.on_connect = on_connect
    client.on_message = on_message
    client.user_data_set({'redis_client': redis_client}) # 將 redis_client 傳遞給回調函式

    # 設定 mTLS 憑證
    client.tls_set(
        ca_certs=CA_CERT_PATH,
        certfile=CLIENT_CERT_PATH,
        keyfile=CLIENT_KEY_PATH,
        tls_version=ssl.PROTOCOL_TLS
    )

    reconnect_delay = 1 # 初始重連延遲
    max_reconnect_delay = 60 # 最大重連延遲

    while True:
        try:
            # 使用 keepalive 參數
            client.connect(MQTT_HOST, MQTT_PORT, keepalive=60)
            client.loop_forever() # 阻塞式迴圈，處理網路流量、重新連線和回調
        except Exception as e:
            logger.error(f"MQTT 連線或迴圈錯誤: {e}")
            logger.info(f"將在 {reconnect_delay} 秒後重試連接...")
            time.sleep(reconnect_delay)
            reconnect_delay = min(reconnect_delay * 2, max_reconnect_delay) # 指數退避
            # 在重連之前，確保客戶端已斷開連接
            client.disconnect() # 顯式斷開連接，避免重複連接問題

if __name__ == "__main__":
    logger.info("啟動 MQTT 到 Redis 橋接服務...")
    run_mqtt_to_redis_bridge()
