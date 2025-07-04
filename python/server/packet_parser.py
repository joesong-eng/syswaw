import logging

logger = logging.getLogger(__name__)

class PacketParser:
    def __init__(self):
        self.status = "running"
        self.error = None

    def parse(self, data: str) -> dict:
        try:
            header, body = data.split('#', 1)
            chip_id, token = header[1:].split(':', 1)
            fields = body.split()
            return {
                "chip_id": chip_id,
                "token": token,
                "ball_in": int(fields[0]) if fields else 0,
                "credit_in": int(fields[1]) if len(fields) > 1 else 0,
                "ball_out": int(fields[2]) if len(fields) > 2 else 0,
                "return_value": int(fields[3]) if len(fields) > 3 else 0,
                "assign_credit": int(fields[4]) if len(fields) > 4 else 0,
                "settled_credit": int(fields[5]) if len(fields) > 5 else 0,
                "bill_denomination": int(fields[6]) if len(fields) > 6 else 0
            }
        except Exception as e:
            logger.error(f"[parser] Failed to parse packet: {data}, error: {e}")
            self.status = "error"
            self.error = str(e)
            return {}

    def get_status(self):
        return {"status": self.status, "error": self.error}