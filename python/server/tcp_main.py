import subprocess, redis, psutil, logging, time, sys, os, json, atexit, signal
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from typing import Dict
from tcp_redis import RedisSV
from settings.config import LOG_DIR, SERVER_IP, SERVER_PORT
from utils.log_config import redis_logger
from utils.log_config import log_redis_cmd
import datetime
import signal

# --- PID 文件處理邏輯 (放在這裡即可) ---
PID_FILE = '/www/wwwroot/syswaw/storage/logs/tcp_main.log'

# 檢查 PID 文件是否存在並處理
if os.path.exists(PID_FILE):
    with open(PID_FILE, 'r') as f:
        old_pid = f.read().strip()
    try:
        # 檢查舊進程是否仍在運行
        os.kill(int(old_pid), 0)
        print(f"[tcp_server] Already running with PID {old_pid}")
        # 如果舊進程仍在運行，則退出新實例
        sys.exit(1)
    except (OSError, ValueError):
        # 如果舊進程不存在或 PID 無效，則移除舊的 PID 文件
        print(f"[tcp_server] Stale PID file found, removing...")
        os.remove(PID_FILE)

with open(PID_FILE, 'w') as f:
    f.write(str(os.getpid()))
def cleanup():
    if os.path.exists(PID_FILE):
        os.remove(PID_FILE)
        print("[tcp_server] Cleaned up PID file")

atexit.register(cleanup)
signal.signal(signal.SIGTERM, lambda signum, frame: sys.exit(0))
signal.signal(signal.SIGINT, lambda signum, frame: sys.exit(0))

logger = logging.getLogger(__name__)
print("main.py started") # 這行會比 [main]Starting tcp_main.py 先印出
class TcpMain:
    def __init__(self):
        print("Initializing RedisSV...")
        self.redis_service = RedisSV()
        print("RedisSV initialized")
        self.process = None

        self.kill_existing_tcp_server()  # ✅ 加入這行，先清掉 zombie server process
        self.check_initial_status()
        self.subscribe()

    def kill_existing_tcp_server(self):
        try:
            for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
                # 更精確地匹配，避免殺錯
                cmdline_str = ' '.join(proc.info['cmdline'])
                if 'python' in proc.info['name'].lower() and \
                   'tcp_server.py' in cmdline_str and \
                   not 'tcp_main.py' in cmdline_str: # 確保不會殺死 tcp_main.py 自身
                    logger.info(f"[main]Killing existing tcp_server.py process (PID: {proc.pid})")
                    proc.terminate()
                    proc.wait(timeout=3)
        except Exception as e:
            logger.error(f"[main]Error while killing existing tcp_server.py: {e}")

    def check_initial_status(self):
        try:
            status_data = self.redis_service.redis.get('tcp_server_latest_status')
            if status_data:
                status = json.loads(status_data.decode('utf-8'))
                if status.get('status') == 'running' and not self.is_server_running():
                    # # logger.info("[main]Detected running status but no server process, setting to terminated")
                    self.publish_status("terminated", "stop", "Server process not found")
        except redis.RedisError as e:
            logger.error(f"[main]Failed to check initial status: {e}")
        except json.JSONDecodeError as e:
            logger.error(f"[main]Invalid tcp_server_latest_status format: {e}")

    def is_server_running(self):
        try:
            for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
                cmdline_str = ' '.join(proc.info['cmdline'])
                if 'python' in proc.info['name'].lower() and \
                   'tcp_server.py' in cmdline_str and \
                   not 'tcp_main.py' in cmdline_str: # 確保不會判斷到 tcp_main.py 自身
                    return True
            return False
        except psutil.Error as e:
            logger.error(f"[main]Failed to check server process: {e}")
            return False

    def subscribe(self):
        print("Subscribing to tcp_server_cmd...")
        self.redis_service.subscribe("tcp_server_cmd", self.handle_command)
        print("Subscribed, starting listen...")
        self.redis_service.listen()

    def handle_command(self, data: Dict):
        action = data.get("action")
        # # logger.info(f"[main]Received command: {action}")
        log_redis_cmd("*3* Received message", {'action': action})

        print(f"[main]Received command: {action}")
        if action == "start":
            self.start_server()
        elif action == "stop":
            self.stop_server()
        elif action == "restart":
            self.stop_server()
            time.sleep(1) # 等待停止完成
            self.start_server()
        else:
            logger.warning(f"[main]Unknown command: {action}")
            self.publish_status("error", action, "unknown command")

    def start_server(self):
        self.kill_existing_tcp_server()  # ✅ 保險：每次啟動前先清 zombie
        if self.is_server_running(): # 這裡應該檢查 tcp_server.py 是否在運行，而不是 self.is_running()
            logger.info("[main]tcp_server.py is already running, skipping start.")
            self.publish_status("running", "start")
            return
        
        try:
            self.process = subprocess.Popen(
                ["/www/wwwroot/syswaw/.venv/bin/python", "tcp_server.py"],
                cwd="/www/wwwroot/syswaw/python/server",
            )
            time.sleep(1) # 給子進程一點時間啟動
            if self.is_server_running(): # 這裡也應該檢查 tcp_server.py 是否在運行
                logger.info("[main]tcp_server.py started successfully")
                self.publish_status("running", "start")
            else:
                logger.error("[main]tcp_server.py failed to start")
                self.publish_status("error", "start", "failed to start")
        except Exception as e:
            logger.error(f"[main]Failed to start tcp_server.py: {e}")
            self.publish_status("error", "start", str(e))

    def stop_server(self):
        # 這裡不直接用 self.is_running() 因為它只檢查 self.process 是否存在
        # 更好的是直接檢查 psutil 確保 tcp_server.py 確實運行
        if not self.is_server_running(): # 檢查 tcp_server.py 是否在運行
            logger.info("[main]tcp_server.py not running")
            self.publish_status("stopped", "stop")
            return

        try:
            # 遍歷找到並停止 tcp_server.py 進程
            for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
                cmdline_str = ' '.join(proc.info['cmdline'])
                if 'python' in proc.info['name'].lower() and \
                   'tcp_server.py' in cmdline_str and \
                   not 'tcp_main.py' in cmdline_str:
                    logger.info(f"[main]Stopping tcp_server.py process (PID: {proc.pid})")
                    proc.terminate()
                    try:
                        proc.wait(timeout=5) # 給點時間讓它停止
                        logger.info(f"[main]tcp_server.py (PID: {proc.pid}) stopped successfully")
                    except psutil.TimeoutExpired:
                        logger.warning(f"[main]tcp_server.py (PID: {proc.pid}) did not terminate in time, killing.")
                        proc.kill()
                        proc.wait(timeout=3)
            
            # 清理 self.process 引用，即使之前沒有通過 Popen 啟動
            self.process = None # 重置 process 引用
            self.publish_status("stopped", "stop")

        except Exception as e:
            logger.error(f"[main]Failed to stop tcp_server.py: {e}")
            self.publish_status("error", "stop", str(e))

    def is_running(self):
        return self.process is not None and self.process.poll() is None

    def publish_status(self, status: str, action: str, error: str = None):
        message = {
            'status': status,
            'action': action,
            'timestamp': datetime.datetime.now().isoformat(),
            'error': error
        }
        # # logger.info(f"[main]Publishing to tcp_server_status: {message}")
        try:
            log_redis_cmd("*[main]1* Preparing to publish to tcp_server_status", message)
            self.redis_service.publish("tcp_server_status", message)
            log_redis_cmd("*[main]2* Published to tcp_server_status", message)
            self.redis_service.redis.set('tcp_server_latest_status', json.dumps(message))
            log_redis_cmd("*[main]3* Wrote to tcp_server_latest_status", message)
        except redis.RedisError as e:
            logger.error(f"[main]Failed to publish or save status: {e}")

    def cleanup(self):
        # 這個 cleanup 函數是 TcpMain 實例的方法，用於清理它啟動的子進程。
        # 全局的 cleanup 函數用於清理 PID 文件。
        # 兩者作用不同，可以保留，但需要明確。
        logger.info("[main]Cleaning up tcp_main.py (instance cleanup)")
        if self.process is not None and self.process.poll() is None: # 檢查是否還有子進程在運行
            try:
                # 這裡使用 self.process 而不是 psutil.Process 遍歷，因為這是對特定子進程的清理
                process = psutil.Process(self.process.pid)
                process.terminate()
                process.wait(timeout=3)
                logger.info("[main]tcp_server.py terminated during instance cleanup")
                # self.publish_status("terminated", "stop", "Main process terminated") # 這行可能會觸發在退出時的 RedisError
                self.process = None
            except Exception as e:
                logger.error(f"[main]Failed to terminate tcp_server.py during instance cleanup: {e}")
                # self.publish_status("error", "stop", str(e)) # 同上，可能觸發 RedisError
        else:
            logger.info("[main]No active tcp_server.py process for instance cleanup.")
            # self.publish_status("terminated", "stop", "No server process running") # 同上

    def handle_signal(self, signum, frame):
        logger.info(f"[main]Received signal {signum}, attempting graceful exit.")
        # 在信號處理器中調用 self.cleanup()，這會嘗試停止由這個 TcpMain 實例啟動的 tcp_server.py
        self.cleanup()
        sys.exit(0) # 正常退出

if __name__ == "__main__":
    print("[main]Starting tcp_main.py")
    # 從這裡開始，移除重複的 PID 文件處理邏輯
    # logger.info("[main]Starting tcp_main.py") # 這裡重複了，可以刪除

    try:
        main_app = TcpMain() # 創建實例並讓它運行
        # 這裡不需要額外的 while True 或 time.sleep
        # 因為 self.redis_service.listen() 是一個阻塞調用，會一直運行，直到被中斷。
        # 一旦 listen 啟動，程式就會停在那裡等待消息。
    except Exception as e:
        logger.error(f"[main]Fatal error during TcpMain initialization: {e}")
        # 如果初始化失敗，確保 PID 文件被刪除
        if os.path.exists(PID_FILE): # 使用全局定義的 PID_FILE
            os.remove(PID_FILE)
        sys.exit(1)