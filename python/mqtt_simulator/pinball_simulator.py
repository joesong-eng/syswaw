# pinball_simulator.py
import random

class BaseMachine:
    # ... (BaseMachine 的內容完全不變) ...
    def __init__(self, config):
        self.config = config
        self.chip_id = config.get('chip_hardware_id')
        self.token = config.get('auth_key')
        def to_float(value, default=0.0):
            if value is None: return default
            return float(value)
        self.coin_input_value = to_float(config.get('coin_input_value'))
        self.payout_unit_value = to_float(config.get('payout_unit_value'))
        self.credit_button_value = to_float(config.get('credit_button_value'))
        self.bill_acceptor_enabled = bool(config.get('bill_acceptor_enabled', False))
        self.accepted_denominations = config.get('accepted_denominations', '[]')
        self.ball_in, self.credit_in, self.ball_out, self.coin_out, self.assign_credit, self.settled_credit, self.bill_denomination = 0, 0, 0, 0, 0, 0, 0
        self.ASSIGN_CREDIT_PROBABILITY = 0.02
    def is_assign_credit_triggered(self):
        return self.credit_button_value > 0 and random.random() < self.ASSIGN_CREDIT_PROBABILITY
    def update_state(self):
        pass
    def get_formatted_packet(self):
        return (f"@{self.chip_id}:{self.token}#"
                f"{int(self.ball_in):06d} {int(self.credit_in):06d} {int(self.ball_out):06d} "
                f"{int(self.coin_out):06d} {int(self.assign_credit):06d} {int(self.settled_credit):06d} "
                f"{int(self.bill_denomination):06d}\n")


class PinballMachine(BaseMachine):
    def __init__(self, config):
        super().__init__(config)

        # --- 模擬參數設定 ---
        self.COIN_INPUT_RANGE = [2, 15] # 投幣次數範圍
        # 遊戲返還率(RTP)，例如 80% ~ 110%。這代表玩家投入的球，平均能贏回多少
        self.RTP_RANGE = [0.8, 1.1]
        self.exchange_rate = (self.coin_input_value / self.payout_unit_value) if self.payout_unit_value > 0 else 0

    def update_state(self):
        # --- Step 1: 模擬輸入，並計算出本輪要用來玩的「本金球」---
        delta_credit_in = random.randint(*self.COIN_INPUT_RANGE)

        # 來自投幣兌換的本金球
        balls_from_coins = delta_credit_in * self.exchange_rate

        # 來自開分的本金球
        balls_from_assign = 0
        if self.is_assign_credit_triggered():
            self.assign_credit += 1
            if self.payout_unit_value > 0:
                balls_from_assign = self.credit_button_value / self.payout_unit_value

        # 總共要用來玩的球 (本金球)
        balls_to_play = balls_from_coins + balls_from_assign

        # --- Step 2: 模擬遊玩過程 ---
        # 玩家把所有本金球都投進去
        delta_ball_in = balls_to_play

        # --- Step 3: 模擬返獎 ---
        # 根據返還率，計算玩家贏回了多少球
        rtp = random.uniform(*self.RTP_RANGE)
        won_balls = balls_to_play * rtp

        # --- Step 4: 計算總出球 ---
        # 總出球 = 機器吐出的本金球 + 玩家贏得的獎勵球
        delta_ball_out = balls_to_play + won_balls

        # --- Step 5: 更新累計計數 ---
        self.credit_in += delta_credit_in
        self.ball_in += delta_ball_in
        self.ball_out += delta_ball_out
