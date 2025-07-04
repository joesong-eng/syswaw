import redis
import json
import logging

logging.basicConfig(
    filename='/www/wwwroot/syswaw/storage/logs/tcp_main.log',
    level=logging.WARNING,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

redis_client = redis.Redis(host='localhost', port=6379, db=0, decode_responses=True)
message = {
    "status": "running",
    "action": "start",
    "timestamp": "2025-05-30 09:20:00"
}
logger.info(f"Test publishing: {message}")
redis_client.publish("tcp_server_status", json.dumps(message))
logger.info("Test publish completed")
