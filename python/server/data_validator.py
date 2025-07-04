import logging

logger = logging.getLogger(__name__)

class DataValidator:
    def __init__(self):
        self.status = "disabled"  # 尚未實現驗證邏輯
        self.error = None

    def validate(self, packet: dict) -> bool:
        # 未來實現驗證邏輯，例如檢查 chip_id 和 token
        logger.info("[validator] Validation not implemented")
        return True

    def get_status(self):
        return {"status": self.status, "error": self.error}