# claw_machine_simulator.py
import random
from pinball_simulator import BaseMachine

class ClawMachine(BaseMachine):
    def __init__(self, config):
        super().__init__(config)

        # --- 模擬參數設定 ---
        self.COIN_INPUT_RANGE = [1, 10]      # 每輪模擬的「投幣次數」範圍
        self.PRIZE_PROBABILITY = 0.10      # 每次遊玩的「夾中機率」

    def update_state(self):
        # --- Step 1: 計算本輪總共「可玩幾次」 (Total Plays) ---
        # 娃娃機的遊玩次數來源於「投幣」和「開分」

        # a. 來自玩家投幣的次數
        delta_credit_in = random.randint(*self.COIN_INPUT_RANGE)

        # b. 來自開分的次數
        plays_from_assign = 0
        if self.is_assign_credit_triggered():
            self.assign_credit += 1 # 累加開分計數
            if self.coin_input_value > 0:
                # 計算這次開分等於多少次免費遊戲
                plays_from_assign = int(self.credit_button_value / self.coin_input_value)

        # 總遊玩次數 = 投幣次數 + 開分換來的免費次數
        total_plays = delta_credit_in + plays_from_assign

        # --- Step 2: 根據總遊玩次數，計算總「出獎次數」 ---
        delta_coin_out = 0
        for _ in range(total_plays):
            # 對每一次遊玩，都進行一次機率判斷
            if random.random() < self.PRIZE_PROBABILITY:
                delta_coin_out += 1

        # --- Step 3: 更新累計「計數」 ---
        self.credit_in += delta_credit_in # 只累加真實的投幣次數
        self.coin_out += delta_coin_out

        # 確保紙鈔計數器不被錯誤累加 (雖然它本來就不會)
        self.bill_denomination += 0
