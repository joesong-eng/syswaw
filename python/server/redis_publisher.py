import logging
import time
from tcp_redis import RedisSV

logger = logging.getLogger(__name__)

class RedisPublisher:
    def __init__(self):
        self.redis_service = RedisSV()
        self.status = "running" if self.redis_service.is_connected() else "error"
        self.error = None if self.redis_service.is_connected() else "Redis connection failed"

    def publish_packet(self, packet: dict):
        try:
            packet["timestamp"] = time.strftime("%Y-%m-%d %H:%M:%S")
            self.redis_service.publish("tcp_data_stream", packet)
            logger.info(f"[publisher] Published to tcp_data_stream: {packet}")
            self.status = "running"
        except Exception as e:
            logger.error(f"[publisher] Failed to publish: {e}")
            self.status = "error"
            self.error = str(e)

    def get_status(self):
        return {"status": self.status, "error": self.error}