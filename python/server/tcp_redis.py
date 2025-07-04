import redis
import logging
import json,time
from settings.config import REDIS_HOST, REDIS_PORT
from utils.log_config import log_redis_cmd

logger = logging.getLogger(__name__)

class RedisSV:
    def __init__(self):
        self._redis = None
        self.connect()

    def connect(self):
        try:
            self._redis = redis.Redis(
                host=REDIS_HOST,
                port=REDIS_PORT,
                decode_responses=False,
                retry_on_timeout=True
            )
            self._redis.ping()
            # # logger.info("[rds]Redis connected successfully")
        except redis.RedisError as e:
            logger.error(f"[rds]Redis connection failed: {e}")
            self._redis = None

    @property
    def redis(self):
        if self._redis is None or not self._redis.ping():
            # logger.warning("[rds]Redis not connected, attempting reconnect")
            self.connect()
        return self._redis

    def publish(self, channel, message):
        # # log_redis_cmd("-2.x1[rds]- redis Published")
        try:
            if self.redis is None:
                # # log_redis_cmd("-2.x1.1[rds]- redis is None")
                raise redis.RedisError("Redis not connected")
            if isinstance(message, dict):
                # logger.info(f"-2.x1.2[rds]- isinstance")
                message = json.dumps(message)
            # logger.info(f"-2.x1.3[rds]- point")
            self.redis.publish(channel, message)
            # logger.info(f"-2.x1.4[rds]- Published to {channel}")
        except redis.RedisError as e:
            logger.error(f"-2.x3[rds]- Failed to publish to {channel}: {e}")

    def xadd(self, stream, fields, id='*', maxlen=None):
        try:
            # # logger.info(f"[rds]Attempting to add to stream {stream}")
            if self.redis is None:
                raise redis.RedisError("Redis not connected")
            stream_id = self.redis.xadd(stream, fields, id=id, maxlen=maxlen)
            # logger.info(f"-3.x1[rds]- Added to stream {stream} with id: {stream_id}")
            return stream_id
        except redis.RedisError as e:
            logger.error(f"[rds]Failed to add to stream {stream}: {e}")
            raise

    def subscribe(self, channel, callback):
        try:
            self.pubsub = self.redis.pubsub()
            self.pubsub.subscribe(channel)
            # # logger.info(f"[rds]Subscribed to channel: {channel}")
            self.callback = callback
        except redis.RedisError as e:
            logger.error(f"[rds]Failed to subscribe to {channel}: {e}")
            raise

    def listen(self):
        try:
            for message in self.pubsub.listen():
                if message['type'] == 'message':
                    data = json.loads(message['data'].decode('utf-8'))
                    # # logger.info(f"[rds]Received message from {message['channel']}: {data}")
                    self.callback(data)
        except redis.RedisError as e:
            logger.error(f"[rds]Failed to listen on pubsub: {e}")
            time.sleep(1)  # Retry after delay
            self.listen()
        except json.JSONDecodeError as e:
            logger.error(f"[rds]Invalid JSON in message: {e}")
