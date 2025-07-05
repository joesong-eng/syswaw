# gambling_simulator.py
import random
import json
from pinball_simulator import BaseMachine

class GamblingLikeMachine(BaseMachine):
    def __init__(self, config):
        super().__init__(config)

        # --- 模擬參數設定 ---
        self.COIN_INPUT_RANGE = [10, 50]  # 每輪模擬的「投幣次數」範圍
        self.BILL_INPUT_RANGE = [0, 2]    # 每輪模擬的「投紙鈔張數」範圍

        # --- 核心參數：計算「平均每次遊玩返獎次數」 ---
        # 這是實現返還率(RTP)的關鍵
        # 假設目標 RTP 是 85%
        target_rtp = 0.85

        # 玩一次的成本價值
        play_cost_value = self.coin_input_value

        # 平均玩一次，應返還的價值
        avg_payout_value_per_play = play_cost_value * target_rtp

        # 將應返還價值，換算成「返獎次數」 (例如彩票張數)
        if self.payout_unit_value > 0:
            self.avg_payout_count_per_play = avg_payout_value_per_play / self.payout_unit_value
        else:
            self.avg_payout_count_per_play = 0

        # 為了增加隨機性，我們讓實際返獎在 0 到 平均值*2 的範圍內波動
        self.payout_count_range = [0, int(self.avg_payout_count_per_play * 2)]

        # 紙鈔面額到計數的映射 (用於 bill_denomination)
        self.DENOMINATION_VALUE_TO_CODE = { '100': 1, '200': 2, '500': 5, '1000': 10, '2000': 20 }

    def update_state(self):
        # --- Step 1: 計算本輪總共「可玩幾次」 (Total Plays) ---

        # a. 來自玩家投幣的次數
        delta_credit_in = random.randint(*self.COIN_INPUT_RANGE)

        # b. 來自開分的次數
        plays_from_assign = 0
        if self.is_assign_credit_triggered():
            self.assign_credit += 1
            if self.coin_input_value > 0:
                plays_from_assign = int(self.credit_button_value / self.coin_input_value)

        # c. 來自紙鈔的次數
        plays_from_bills = 0
        if self.bill_acceptor_enabled:
            try:
                accepted = json.loads(self.accepted_denominations) if self.accepted_denominations else []
            except (json.JSONDecodeError, TypeError):
                accepted = []

            bill_count = random.randint(*self.BILL_INPUT_RANGE)
            for _ in range(bill_count):
                if accepted:
                    bill_value_str = str(random.choice(accepted))
                    # 累加 bill_denomination 的計數碼
                    if bill_value_str in self.DENOMINATION_VALUE_TO_CODE:
                        self.bill_denomination += self.DENOMINATION_VALUE_TO_CODE[bill_value_str]

                    # 計算這張紙鈔等於多少次遊戲
                    if self.coin_input_value > 0:
                        plays_from_bills += int(float(bill_value_str) / self.coin_input_value)

        total_plays = delta_credit_in + plays_from_assign + plays_from_bills

        # --- Step 2: 根據總遊玩次數，計算總「返獎次數」 ---
        delta_coin_out = 0
        if self.payout_count_range[1] > 0: # 確保有返獎的可能性
            for _ in range(total_plays):
                # 對每一次遊玩，都隨機生成一個返獎次數
                delta_coin_out += random.randint(*self.payout_count_range)

        # --- Step 3: 更新累計「計數」 ---
        self.credit_in += delta_credit_in # 只累加真實的投幣次數
        self.coin_out += delta_coin_out
