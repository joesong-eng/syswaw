# python/config.py
import os
from dotenv import load_dotenv

load_dotenv()
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
SERVER_IP = os.getenv("SERVER_IP", "127.0.0.1")
SERVER_PORT = int(os.getenv("SERVER_PORT", "39001"))

DB_HOST="127.0.0.1"
DB_NAME = "syswaw"
DB_USER = "syswaw"
DB_PASSWORD = "2a@684240"
DB_PORT=3306

TCP_API_KEY = "2aqaz123123"
LOG_DIR = '/www/wwwroot/syswaw/storage/logs'

# python/config.py
import os
from dotenv import load_dotenv

load_dotenv()
REDIS_HOST = "127.0.0.1"
REDIS_PORT = 6379
SERVER_IP = os.getenv("SERVER_IP", "127.0.0.1")
SERVER_PORT = int(os.getenv("SERVER_PORT", "39001"))

DB_HOST="127.0.0.1"
DB_NAME = "syswaw"
DB_USER = "syswaw"
DB_PASSWORD = "2a@684240"
DB_PORT=3306

