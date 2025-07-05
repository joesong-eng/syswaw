# mqtt_to_redis.py
import paho.mqtt.client as mqtt
import redis,sys
import json
import ssl
import time
import logging

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# --- MQTT 配置 ---
MQTT_HOST = "mqtt.tg25.win"
MQTT_PORT = 443
MQTT_PATH = "/mqtt"
MQTT_USERNAME = "joesong"
MQTT_PASSWORD = "we123123"
MQTT_TOPIC = "secure/test"

# --- Redis 配置 ---
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
REDIS_DB = 0
REDIS_CHANNEL = "mqtt_data_channel"

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        logger.info("MQTT 連接成功！")
        client.subscribe(MQTT_TOPIC)
        logger.info(f"已訂閱主題: {MQTT_TOPIC}")
    else:
        logger.error(f"MQTT 連接失敗，返回碼: {rc}")

def on_message(client, userdata, msg):
    logger.info(f"從 '{msg.topic}' 收到訊息: {msg.payload.decode()}")
    try:
        # 嘗試解析 JSON 數據
        data = json.loads(msg.payload.decode('utf-8'))
        # 將數據發布到 Redis
        redis_client = userdata['redis_client']
        redis_client.publish(REDIS_CHANNEL, json.dumps(data))
        logger.info(f"已將數據發布到 Redis 頻道 '{REDIS_CHANNEL}': {json.dumps(data)}")
    except json.JSONDecodeError:
        logger.error(f"無法解析為 JSON: {msg.payload.decode('utf-8')}")
    except Exception as e:
        logger.error(f"處理訊息時發生錯誤: {e}")

def run_mqtt_to_redis_bridge():
    # 初始化 Redis 客戶端
    redis_client = redis.StrictRedis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB)
    try:
        redis_client.ping()
        logger.info("成功連接到 Redis。")
    except redis.exceptions.ConnectionError as e:
        logger.error(f"無法連接到 Redis: {e}")
        sys.exit(1)

    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="mqtt_to_redis_bridge", transport="websockets")
    client.on_connect = on_connect
    client.on_message = on_message
    client.user_data_set({'redis_client': redis_client}) # 將 redis_client 傳遞給回調函式

    client.ws_set_options(path=MQTT_PATH, subprotocols=['mqtt'])
    client.tls_set(tls_version=ssl.PROTOCOL_TLS)
    client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)

    while True:
        try:
            client.connect(MQTT_HOST, MQTT_PORT)
            client.loop_forever() # 阻塞式迴圈，處理網路流量、重新連線和回調
        except Exception as e:
            logger.error(f"MQTT 連線或迴圈錯誤: {e}")
            time.sleep(5) # 等待一段時間後重試連線

if __name__ == "__main__":
    logger.info("啟動 MQTT 到 Redis 橋接服務...")
    run_mqtt_to_redis_bridge()
