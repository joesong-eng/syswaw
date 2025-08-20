import random
import time
from datetime import datetime, timezone

class PinballMachine:
    def __init__(self, config):
        self.config = config
        self.chip_id = config.get("chip_hardware_id")
        self.auth_key = config.get("auth_key")

        # --- 從資料庫讀取設定 ---
        self.coin_input_value = float(config.get("coin_input_value", 10))  # 一枚代幣多少元
        self.payout_unit_value = float(config.get("payout_unit_value", 1)) # 一顆珠子多少元

        # 設備模擬狀態
        self.start_time = int(time.time() * 1000)  # 模擬設備開機時間 (ms)

        # 累積數據
        self.credit_in = 0
        self.ball_in = 0
        self.ball_out = 0
        self.assign_credit = 0
        self.settled_credit = 0
        self.bill_denomination = 0
        self.coin_out = 0  # 彈珠台不退幣

    def reset_session(self):
        """每位客人進場前，重置本輪數據"""
        self.credit_in = 0
        self.ball_in = 0
        self.ball_out = 0
        self.assign_credit = 0
        self.settled_credit = 0
        self.bill_denomination = 0
        self.coin_out = 0

    def simulate_customer(self):
        """模擬一位客人的完整遊戲過程"""
        self.reset_session()

        # --- Step 1: 決定金額 / 代幣 ---
        money = random.choice(range(100, 1001, 100))  # 100~1000 元
        tokens = int(money / self.coin_input_value)
        self.credit_in = tokens

        # 每枚代幣換出的珠子
        balls_per_token = int(self.coin_input_value / self.payout_unit_value)
        ball_pool = tokens * balls_per_token

        # --- Step 2: 模擬遊戲 ---
        while ball_pool > 0:
            ball_pool -= 1
            self.ball_in += 1

            # 10% 中獎機率，每次中 2~5 顆
            if random.random() < 0.1:
                won = random.randint(2, 5)
                ball_pool += won
                self.ball_out += won

        # --- Step 3: 返回模擬結果 ---
        return {
            "chip_id": self.chip_id,
            "auth_key": self.auth_key,
            "credit_in": self.credit_in,
            "coin_out": self.coin_out,
            "ball_in": self.ball_in,
            "ball_out": self.ball_out,
            "assign_credit": self.assign_credit,
            "settled_credit": self.settled_credit,
            "bill_denomination": self.bill_denomination,
            "last_activity": int(time.time() * 1000) - self.start_time
        }
