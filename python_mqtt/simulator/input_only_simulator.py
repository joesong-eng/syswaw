# input_only_simulator.py
import random
import json
from pinball_simulator import BaseMachine

class InputOnlyMachine(BaseMachine):
    """專門模擬純輸入機，如紙鈔兌換機"""
    def __init__(self, config):
        super().__init__(config)
        # 紙鈔機使用頻率可能更高
        self.BILL_INPUT_RANGE = [1, 20]
        # 這個映射關係很重要，假設 100元->1, 500元->5, 1000元->10
        self.DENOMINATION_VALUE_TO_CODE = {
            '100': 1, '200': 2, '500': 5, '1000': 10, '2000': 20
        }

    def update_state(self):
        # 純輸入機只有紙鈔輸入
        if self.bill_acceptor_enabled:
            try:
                # 嘗試解析 JSON 字串
                accepted = json.loads(self.accepted_denominations) if self.accepted_denominations else []
            except (json.JSONDecodeError, TypeError):
                accepted = []

            bill_count = random.randint(*self.BILL_INPUT_RANGE)

            for _ in range(bill_count):
                if accepted:
                    bill_value_str = str(random.choice(accepted))
                    if bill_value_str in self.DENOMINATION_VALUE_TO_CODE:
                        # 累加 bill_denomination 的計數碼
                        self.bill_denomination += self.DENOMINATION_VALUE_TO_CODE[bill_value_str]

        # 其他所有欄位增量為 0
        self.credit_in += 0
        self.assign_credit += 0
        self.coin_out += 0
