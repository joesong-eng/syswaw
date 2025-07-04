# /www/wwwroot/syswaw/python/utils/log_config.py
import logging
import os
import datetime
from logging.handlers import RotatingFileHandler

LOG_DIR = '/www/wwwroot/syswaw/storage/logs'
os.makedirs(LOG_DIR, exist_ok=True)

current_date = datetime.datetime.now().strftime('%Y-%m-%d')
LOG_FILE_NAME = f'redis_cmd-{current_date}.log'
LOG_FILE = os.path.join(LOG_DIR, LOG_FILE_NAME)

LOG_FORMAT = "[%(asctime)s] %(levelname)s: %(message)s"
DATE_FORMAT = '%y%m%d %H:%M:%S'

# 創建一個格式化器，這個格式化器可以被多個 handler 共用
formatter = logging.Formatter(LOG_FORMAT, datefmt=DATE_FORMAT)

# --- 預設日誌器 redis_logger ---
redis_logger = logging.getLogger('redis_cmd')
redis_logger.setLevel(logging.INFO)
if not redis_logger.handlers: # 防止重複添加 handler
    file_handler = logging.FileHandler(LOG_FILE)
    file_handler.setLevel(logging.INFO)
    file_handler.setFormatter(formatter)
    redis_logger.addHandler(file_handler)
    redis_logger.propagate = False

# --- 自定義日誌器 redis_logger_custom (帶 .cmd() 方法) ---
# 確保這個類別和實例化在頂層
class RedisCommandLogger(logging.Logger):
    def __init__(self, name, level=logging.NOTSET):
        super().__init__(name, level)

    def cmd(self, command_string, context=None):
        if context:
            context_str = " ".join(f"{k}:{v}" for k, v in context.items())
            self.info(f"py{command_string} {context_str}")
        else:
            self.info(f"py{command_string}")

# 設置自定義 Logger 類，這是關鍵步驟
# 注意：setLoggerClass 應該盡早調用，且只調用一次
logging.setLoggerClass(RedisCommandLogger)

# 獲取或創建自定義日誌器實例
# 確保這個變數是頂層定義的，以便可以被 import
redis_logger_custom = logging.getLogger('redis_cmd_custom')
redis_logger_custom.setLevel(logging.INFO)

# 確保不重複添加 handler
if not redis_logger_custom.handlers:
    # 這裡可以共用上面的 formatter
    file_handler_custom = logging.FileHandler(LOG_FILE)
    file_handler_custom.setLevel(logging.INFO)
    file_handler_custom.setFormatter(formatter) # 使用同一個 formatter
    redis_logger_custom.addHandler(file_handler_custom)
    redis_logger_custom.propagate = False

# --- 輔助函數 (如果您也想導入這個函數) ---
def log_redis_cmd(command_string, context=None):
    if context:
        context_str = " ".join(f"{k}={v}" for k, v in context.items())
        redis_logger.info(f"{command_string} {context_str}") # 或者用 redis_logger_custom.info
    else:
        redis_logger.info(f"{command_string}") # 或者用 redis_logger_custom.info

# 這裡不應該有測試性的調用，除非您將它們放在 if __name__ == "__main__": 塊中
# 否則每次導入時，這些測試日誌都會被執行
# For testing the config directly:
# if __name__ == "__main__":
#    print("Running log_config.py directly for testing...")
#    redis_logger.info("This is a test message from redis_logger.")
#    log_redis_cmd("TEST_CMD FLUSHALL", {'source': 'config_test'})
#    redis_logger_custom.cmd("TEST_CUSTOM_CMD PING", {'stage': 'setup'})