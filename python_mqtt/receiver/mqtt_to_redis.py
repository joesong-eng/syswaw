# mqtt_to_redis.py
import paho.mqtt.client as mqtt
import redis,sys
import json
import ssl
import time
import logging

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
MQTT_TOPIC = "secure/test"
CA_CERT_PATH = "python_mqtt/certs/ca.crt"
CLIENT_CERT_PATH = "python_mqtt/certs/client.crt"
CLIENT_KEY_PATH = "python_mqtt/certs/client.key"

# --- Redis 配置 ---
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
REDIS_DB = 0
# REDIS_STREAM_NAME = "tcpstream" # 不再使用 Stream

def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        logger.info("MQTT 連接成功！")
        client.subscribe(MQTT_TOPIC)
        logger.info(f"已訂閱主題: {MQTT_TOPIC}")
    else:
        logger.error(f"MQTT 連接失敗，返回碼: {rc}")

def on_message(client, userdata, msg):
    try:
        message_data_str = msg.payload.decode('utf-8')
        message_json = json.loads(message_data_str) # 解析 JSON 數據

        # 從 MQTT 消息中提取 auth_key 作為唯一識別符
        auth_key = message_json.get('auth_key')
        if not auth_key:
            logger.warning(f"收到的 MQTT 消息中未找到 'auth_key' 字段: {message_data_str}")
            return

        redis_client = userdata['redis_client']
        # 使用 SET 命令，以 auth_key 為 key，覆蓋舊數據
        redis_key = f"machine_data:{auth_key}" # Key 格式改為 machine_data:{auth_key}
        redis_client.set(redis_key, message_data_str)
        logger.info(f"已將機器 '{auth_key}' 的最新數據儲存到 Redis Key '{redis_key}'")

    except json.JSONDecodeError as e:
        logger.error(f"解析 MQTT 消息為 JSON 時發生錯誤: {e} - 原始消息: {msg.payload.decode('utf-8')}")
    except Exception as e:
        logger.error(f"處理訊息並儲存到 Redis 時發生錯誤: {e}")

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
