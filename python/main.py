import subprocess
import time
import redis
import signal
import sys
import os
import logging
from config import REDIS_HOST, REDIS_PORT, REDIS_DB

redis_client = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, db=REDIS_DB)
tcp_server_process = None
auto_restart = True

# 設定日誌
logging.basicConfig(filename='main.log', level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def start_tcp_server():
    logging.info("啟動 tcp_server.py...")
    global tcp_server_process
    python_path = "/www/wwwroot/syswaw/.venv/bin/python3"
    tcp_server_process = subprocess.Popen([python_path, "tcp_server.py"], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    return tcp_server_process

def stop_tcp_server(process):
    global tcp_server_process
    if process:
        logging.info("停止 tcp_server.py...")
        process.terminate()
        process.wait()
        tcp_server_process = None
    else:
        logging.info("tcp_server.py 沒有運行")

def restart_tcp_server(process):
    logging.info("重新啟動 tcp_server.py...")
    stop_tcp_server(process)
    return start_tcp_server()

def update_machine_id_whitelist(machine_ids):
    logging.info(f"更新 machine_id 白名單: {machine_ids}")
    redis_client.delete("machine_id_whitelist")
    for machine_id in machine_ids:
        redis_client.sadd("machine_id_whitelist", machine_id)

def broadcast_tcp_status(status):
    logging.info(f"廣播 TCP 狀態: {status}")
    redis_client.publish("tcp_status_channel", status)

def signal_handler(sig, frame):
    logging.info("收到 Ctrl+C，正在停止 tcp_server.py...")
    stop_tcp_server(tcp_server_process)
    logging.info("tcp_server.py 已停止，main.py 結束。")
    sys.exit(0)

def get_tcp_status():
    if tcp_server_process is None:
        return "stopped"
    elif tcp_server_process.poll() is None:
        return "running"
    else:
        return "stopped"

# def main():
#     global tcp_server_process, auto_restart
#     tcp_server_process = start_tcp_server()
#     broadcast_tcp_status("running")

#     signal.signal(signal.SIGINT, signal_handler)

#     while True:
#         command = redis_client.blpop("tcp_control", timeout=1)
#         if command:
#             command = command[1].decode('utf-8')
#             logging.info(f"收到 Redis 命令: {command}")
#             if command == "start":
#                 if tcp_server_process is None or tcp_server_process.poll() is not None:
#                     tcp_server_process = start_tcp_server()
#                     broadcast_tcp_status("running")
#                     auto_restart = True
#             elif command == "stop":
#                 if tcp_server_process is not None and tcp_server_process.poll() is None:
#                     stop_tcp_server(tcp_server_process)
#                     broadcast_tcp_status("stopped")
#                     auto_restart = False
#             elif command == "restart":
#                 tcp_server_process = restart_tcp_server(tcp_server_process)
#                 broadcast_tcp_status("running")
#                 auto_restart = True
#             elif command.startswith("query:"):
#                 machine_ids = command[6:].split(",")
#                 update_machine_id_whitelist(machine_ids)
#             elif command == "status":
#                 status = get_tcp_status()
#                 broadcast_tcp_status(status)

#         # 監控 tcp_server.py
#         if auto_restart and tcp_server_process is not None and tcp_server_process.poll() is not None:
#             logging.info("tcp_server.py 已停止，重新啟動...")
#             tcp_server_process = start_tcp_server()
#             broadcast_tcp_status("running")

#         # 讀取並顯示 tcp_server.py 的輸出
#         if tcp_server_process:
#             try:
#                 stdout_line = tcp_server_process.stdout.readline()
#                 stderr_line = tcp_server_process.stderr.readline()
#                 if stdout_line:
#                     logging.info(f"tcp_server.py stdout: {stdout_line.strip()}")
#                 if stderr_line:
#                     logging.error(f"tcp_server.py stderr: {stderr_line.strip()}")
#             except ValueError:
#                 pass # 處理錯誤

#         time.sleep(1)

# if __name__ == "__main__":
#     main()