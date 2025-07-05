# simple_io_simulator.py
import random
from pinball_simulator import BaseMachine

class SimpleIOMachine(BaseMachine):
    def __init__(self, config):
        super().__init__(config)
        self.COIN_INPUT_RANGE = [10, 60]
        self.PAYOUT_RATIO = 0.05

    def update_state(self):
        delta_credit_in = random.randint(*self.COIN_INPUT_RANGE)

        plays_from_assign = 0
        if self.is_assign_credit_triggered():
            self.assign_credit += 1
            if self.coin_input_value > 0:
                plays_from_assign = int(self.credit_button_value / self.coin_input_value)

        total_plays = delta_credit_in + plays_from_assign
        self.credit_in += delta_credit_in

        delta_coin_out = 0
        if self.payout_unit_value > 0:
            payout_count = int(total_plays * self.PAYOUT_RATIO)
            for _ in range(payout_count):
                delta_coin_out += random.randint(1, 3)

        self.coin_out += delta_coin_out
