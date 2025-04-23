import subprocess
import time
import logging
import redis
import requests
import os
import psutil
import signal
import sys

# Redis 設定
redis_client = redis.Redis(host='localhost', port=6379, db=0)
pubsub = redis_client.pubsub()
pubsub.subscribe('tcp_server_control')

# 狀態變數
tcp_server_process = None
TCP_API_KEY = os.getenv('TCP_API_KEY', '2aqaz123123')

def update_status(status, action=None):
    data = {"status": status}
    if action:
        data["action"] = action
    try:
        headers = {"X-AUTH-TOKEN": TCP_API_KEY}
        requests.post("https://sys.tg25.win/api/tcp-status", headers=headers, json=data, timeout=3)
        logging.info(f"✅ 訪問 api Laravel: {data}")
    except Exception as e:
        logging.error(f"❌ Laravel 廣播失敗: {e}")

def is_server_running():
    for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
        try:
            if 'python3' in proc.info['name'] and 'tcp_server.py' in ' '.join(proc.info['cmdline']):
                return True
        except (psutil.NoSuchProcess, psutil.AccessDenied):
            pass
    return False

def start_server():
    global tcp_server_process
    if tcp_server_process is not None or is_server_running():
        logging.info("🚀 TCP Server 已在運行，無需重複啟動")
        update_status("running", "already_running")
        return
    try:
        tcp_server_process = subprocess.Popen(["/www/wwwroot/syswaw/.venv/bin/python", "/www/wwwroot/syswaw/python/tcp_server.py"])
        update_status("running", "start")
        redis_client.set('tcp_status', "running")
        logging.info(f"🚀 TCP Server 已啟動，PID: {tcp_server_process.pid}")
    except Exception as e:
        logging.error(f"❌ 啟動 TCP Server 失敗: {e}")
        update_status("stopped", "start_failed")

def stop_server():
    global tcp_server_process
    if tcp_server_process:
        try:
            tcp_server_process.terminate()
            tcp_server_process.wait(timeout=5)
            logging.info(f"🛑 TCP Server (PID: {tcp_server_process.pid}) 已終止")
        except subprocess.TimeoutExpired:
            tcp_server_process.kill()
            logging.warning(f"🛑 TCP Server (PID: {tcp_server_process.pid}) 強制終止")
        tcp_server_process = None
    for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
        try:
            if 'python3' in proc.info['name'] and 'tcp_server.py' in ' '.join(proc.info['cmdline']):
                proc.terminate()
                proc.wait(timeout=5)
                logging.info(f"🛑 終止額外的 TCP Server 進程 (PID: {proc.pid})")
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.TimeoutExpired):
            proc.kill()
            logging.warning(f"🛑 強制終止額外的 TCP Server 進程 (PID: {proc.pid})")
    update_status("stopped", "stop")
    redis_client.set('tcp_status', "stopped")
    logging.info("🛑 TCP Server 已停止")

def status_server():
    global tcp_server_process
    status = "running" if tcp_server_process and tcp_server_process.poll() is None else "stopped"
    update_status(status, "status")
    logging.info(f"查詢狀態: {status}")

def restart_server():
    stop_server()
    time.sleep(2)
    start_server()
    update_status("running", "restart-finished")
    logging.info("TCP Server 已重啟完成")

def signal_handler(sig, frame):
    logging.info("收到終止信號，正在關閉 TCP Server...")
    stop_server()
    sys.exit(0)

def main():
    logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s', filename='/www/wwwroot/syswaw/storage/logs/tcp_main.log')
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)

    for message in pubsub.listen():
        if message["type"] != "message":
            continue
        command = message["data"].decode("utf-8")
        logging.info(f"📥 收到 Redis 命令: {command}")

        if command == "start":
            start_server()
        elif command == "stop":
            stop_server()
        elif command == "status":
            status_server()
        elif command == "restart":
            restart_server()

if __name__ == "__main__":
    main()