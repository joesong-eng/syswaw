# TCP 伺服器設定
SERVER_IP = "127.0.0.1"
SERVER_PORT = 39001

# Redis 連線設定
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
REDIS_DB = 0

# MySQL 連線設定
MYSQL_HOST = "127.0.0.1"
MYSQL_USER = "syswaw"
MYSQL_DATABASE = "syswaw"
MYSQL_PASSWORD = "we123123"

# --- 請在 config.py 中添加這行 ---
# 這個 URL 是 Laravel 後端負責接收 Python 通知數據的 API Endpoint
LARAVEL_NOTIFICATION_API_URL = "http://sys.tg25.win/api/tcp-server/data-captured"
# -----------------------------

