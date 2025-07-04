#python/server/tcp_server.py
import socket, threading, logging, sys, os, time, json
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from utils.log_config import log_redis_cmd # Assuming this exists or can be removed if not needed
from tcp_redis import RedisSV
from settings.config import SERVER_IP, SERVER_PORT

logger = logging.getLogger(__name__)
LOG_FILE_PATH = '/www/wwwroot/syswaw/storage/logs/tcp_server.log'
os.makedirs(os.path.dirname(LOG_FILE_PATH), mode=0o755, exist_ok=True)

if not os.path.exists(LOG_FILE_PATH):
    with open(LOG_FILE_PATH, 'a', encoding='utf-8') as f:
        pass
    os.chmod(LOG_FILE_PATH, 0o775)

logging.basicConfig(
    level=logging.WARNING,
    format='%(asctime)s|%(threadName)s|%(levelname)s|%(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE_PATH, mode='a'),
        logging.StreamHandler(sys.stdout)
    ]
)

redis_service = RedisSV()

# 原始的 parse_packet 函數，現在專用於解析 ESP32 的格式
def parse_esp32_packet(data: str) -> dict:
    try:
        # 檢查是否符合 ESP32 的格式：以 '@' 開頭並包含 '#'
        if not (data.startswith('@') and '#' in data):
            # logger.debug("[parse_esp32]Not ESP32 format, trying next parser.")
            return None

        header, payload = data.split('#', 1)
        chip_hardware_id, auth_key = header[1:].split(':', 1)
        values = payload.strip().split()

        # ESP32 格式期望 7 個整數值
        if len(values) != 7:
            logger.warning(f"[parse_esp32]Invalid number of values for ESP32 format: {len(values)} in {data}")
            return None

        # 確保每個值都能轉換為整數
        ball_in, credit_in, ball_out, return_value, assign_credit, settled_credit, bill_denomination = map(int, values)

        packet = {
            'chip_hardware_id': chip_hardware_id.strip(),
            'auth_key': auth_key.strip(),
            'ball_in': ball_in,
            'credit_in': credit_in,
            'ball_out': ball_out,
            'return_value': return_value,
            'assign_credit': assign_credit,
            'settled_credit': settled_credit,
            'bill_denomination': bill_denomination
        }
        logger.info(f"[parse_esp32]Successfully parsed ESP32 packet.")
        return packet
    except ValueError as ve:
        logger.warning(f"[parse_esp32]Data conversion error for ESP32 format: {ve}, Data: {data}")
        return None
    except Exception as e:
        logger.warning(f"[parse_esp32]Failed to parse ESP32 packet: {data}, error: {e}")
        return None

# 新增的 parse_simulator_packet 函數，現在用於解析 Python 模擬器的 JSON 格式
def parse_simulator_packet(data: str) -> dict:
    try:
        # 首先嘗試解析為 JSON
        packet = json.loads(data)

        # 檢查模擬器 JSON 中所需的關鍵字段 (chip_id, token)
        required_keys = ['chip_id', 'token']
        for key in required_keys:
            if key not in packet:
                logger.warning(f"[parse_sim]Missing required key: {key} in JSON packet: {data}")
                return None

        # 將 chip_id 和 token 映射到 chip_hardware_id 和 auth_key，以便後續處理統一
        packet['chip_hardware_id'] = packet.pop('chip_id')
        packet['auth_key'] = packet.pop('token')

        logger.info(f"[parse_sim]Successfully parsed simulator JSON packet.")
        return packet
    except json.JSONDecodeError:
        logger.warning(f"[parse_sim]Data is not valid JSON: {data}")
        # 如果不是有效的 JSON，則不屬於模擬器期望的格式，返回 None
        return None
    except Exception as e:
        logger.warning(f"[parse_sim]Failed to parse simulator packet (general error): {e}, Data: {data}")
        return None

def handle_client(client: socket.socket, addr: tuple):
    client.settimeout(60)
    try:
        while True:
            try:
                data = client.recv(1024).decode('utf-8', errors='replace').strip()
                if not data:
                    logger.info(f"[client]Client {addr} disconnected")
                    break

                packet_data = None

                # 優先嘗試解析 ESP32 格式
                packet_data = parse_esp32_packet(data)

                if packet_data is None:
                    # 如果不是 ESP32 格式，再嘗試解析模擬器 JSON 格式
                    packet_data = parse_simulator_packet(data)

                if not packet_data:
                    # 如果兩種格式都無法解析
                    client.sendall(b"ERROR: Invalid Data\n")
                    logger.warning(f"[error]Could not parse data from {addr} with any known format: {data}")
                    continue

                redis = redis_service.redis
                if redis is None:
                    raise ConnectionError("Redis not connected")

                # 以下的 Redis 處理邏輯保持不變
                stream_data = redis.xrevrange('tcpstream', '+', '-', count=100)
                latest_entry = None
                ids_to_delete = []

                for entry in stream_data:
                    try:
                        fields = {k.decode('utf-8'): v.decode('utf-8') for k, v in entry[1].items()}
                        chip_id = fields.get('chip_hardware_id')
                        # 從 Redis 讀取出來的數據是 JSON 字串，需要解析
                        latest_entry_from_redis = json.loads(fields.get('data', '{}'))

                        if chip_id and chip_id == packet_data.get('chip_hardware_id'):
                            # 比較所有關鍵數據，除了時間戳 (timestamp 每次都會不同)
                            is_same_data = True
                            for key in packet_data:
                                if key != 'timestamp' and latest_entry_from_redis.get(key) != packet_data.get(key):
                                    is_same_data = False
                                    break

                            if is_same_data:
                                latest_entry = latest_entry_from_redis
                                ids_to_delete.append(entry[0].decode('utf-8'))
                                # break # 如果你只想找到最新的一條並刪除，可以 break

                    except Exception as e:
                        logger.error(f"[redis]Error decoding redis entry: {e}")

                should_write = True
                if latest_entry:
                    should_write = False

                if should_write:
                    if ids_to_delete:
                        # 刪除所有找到的重複舊記錄
                        redis.xdel('tcpstream', *ids_to_delete)
                        logger.info(f"[redis]Deleted {len(ids_to_delete)} old entries for {packet_data['chip_hardware_id']}")

                    redis_service.xadd('tcpstream', {
                        'chip_hardware_id': packet_data['chip_hardware_id'],
                        'data': json.dumps(packet_data)
                    }, id='*', maxlen=1000)
                    logger.info(f"[redis]Added new record for {packet_data['chip_hardware_id']} to tcpstream.")

                redis_service.publish("tcp_live_channel", json.dumps(packet_data)) # 將數據以 JSON 字串形式發布
                client.sendall(b"OK\n")

            except Exception as e:
                logger.error(f"[client]Error processing data for {addr}: {e}")
                client.sendall(b"ERROR: Server Error\n")
    except socket.timeout:
        logger.warning(f"[client]Client {addr} timed out")
    except socket.error as e:
        logger.error(f"[client]Socket error with {addr}: {e}")
    finally:
        client.close()
        logger.info(f"[client]Connection closed for {addr}")

def start_server():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    print(f"Starting server on {SERVER_IP}:{SERVER_PORT}") # 打印實際綁定的 IP 和端口
    server.bind((SERVER_IP, SERVER_PORT))
    server.listen(1000)
    logger.info(f"[server]TCP server listening on {SERVER_IP}:{SERVER_PORT}")
    try:
        while True:
            try:
                client_socket, addr = server.accept()
                logger.info(f"[server]Accepted connection from {addr}")
                threading.Thread(target=handle_client, args=(client_socket, addr), daemon=True).start()
            except socket.error as e:
                logger.error(f"[server]Accept connection error: {e}")
                time.sleep(0.1)
    except KeyboardInterrupt:
        logger.info("[server]TCP server interrupted by user")
    finally:
        server.close()
        logger.info("[server]Server socket closed")

if __name__ == "__main__":
    start_server()
