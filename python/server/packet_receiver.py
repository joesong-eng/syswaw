import socket
import threading
import logging
import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from settings.config import SERVER_IP, SERVER_PORT

logger = logging.getLogger(__name__)

class PacketReceiver:
    def __init__(self, packet_callback):
        self.server = None
        self.packet_callback = packet_callback  # 回調函數，傳遞接收到的封包
        self.status = "stopped"
        self.error = None

    def start(self):
        try:
            self.server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            self.server.bind((SERVER_IP, SERVER_PORT))
            self.server.listen(5)
            self.status = "running"
            logger.info(f"[receiver] Started on {SERVER_IP}:{SERVER_PORT}")
            threading.Thread(target=self.accept_clients, daemon=True).start()
        except Exception as e:
            self.status = "error"
            self.error = str(e)
            logger.error(f"[receiver] Failed to start: {e}")

    def accept_clients(self):
        try:
            while True:
                client, addr = self.server.accept()
                threading.Thread(
                    target=self.handle_client,
                    args=(client, addr),
                    daemon=True
                ).start()
        except Exception as e:
            self.status = "error"
            self.error = str(e)
            logger.error(f"[receiver] Error in accept_clients: {e}")

    def handle_client(self, client: socket.socket, addr: tuple):
        logger.info(f"[receiver] Connection from {addr}")
        client.settimeout(30)
        try:
            while True:
                data = client.recv(1024).decode('utf-8', errors='replace')
                if not data:
                    logger.info(f"[receiver] Client {addr} disconnected")
                    break
                lines = data.split('\n')
                for line in lines:
                    if line.strip():
                        logger.info(f"[receiver] Received from {addr}: {line.strip()}")
                        self.packet_callback(line.strip(), addr)  # 傳遞原始封包
                        client.sendall(b"OK\n")
        except socket.timeout:
            logger.warning(f"[receiver] Client {addr} timed out")
        except socket.error as e:
            logger.error(f"[receiver] Error with client {addr}: {e}")
        finally:
            client.close()
            logger.info(f"[receiver] Connection closed for {addr}")

    def get_status(self):
        return {"status": self.status, "error": self.error}

    def stop(self):
        self.status = "stopped"
        if self.server:
            self.server.close()
        logger.info("[receiver] Stopped")