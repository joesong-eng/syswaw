import random
from datetime import datetime, timezone

class PinballMachine:
    def __init__(self, config):
        self.chip_id = config['chip_hardware_id']
        self.auth_key = config['auth_key']
        self.name = config['name']
        self.coin_value = float(config.get('coin_input_value', 1.0))
        self.payout_value = float(config.get('payout_unit_value', 1.0))

        # 內部狀態
        self.credits = 0.0
        self.total_coins_in = 0
        self.total_payouts = 0.0
        self.last_event_time = datetime.now(timezone.utc)

    def simulate_event(self):
        """
        模擬一個隨機事件：投幣或中獎
        """
        # 80% 的機率是投幣，20% 是中獎
        if random.random() < 0.8:
            # 模擬投幣
            coins_inserted = random.randint(1, 5)
            self.credits += coins_inserted * self.coin_value
            self.total_coins_in += coins_inserted
            event_type = "coin_in"
            event_value = coins_inserted
        else:
            # 模擬中獎 (前提是要有足夠的 credit)
            if self.credits > 0:
                # 確保 payout 是基於 payout_value 的整數倍
                max_payout_units = int(self.credits / self.payout_value * 0.5)
                if max_payout_units > 0:
                    payout_units = random.randint(1, max_payout_units)
                    payout_amount = payout_units * self.payout_value
                    self.credits -= payout_amount
                    self.total_payouts += payout_amount
                    event_type = "payout"
                    event_value = payout_amount
                else:
                    # 如果 credit 不足以支付最小的 payout，則改為投幣
                    coins_inserted = random.randint(1, 5)
                    self.credits += coins_inserted * self.coin_value
                    self.total_coins_in += coins_inserted
                    event_type = "coin_in"
                    event_value = coins_inserted
            else:
                # 沒有 credit，只能投幣
                coins_inserted = random.randint(1, 5)
                self.credits += coins_inserted * self.coin_value
                self.total_coins_in += coins_inserted
                event_type = "coin_in"
                event_value = coins_inserted

        self.last_event_time = datetime.now(timezone.utc)
        return self.get_payload(event_type, event_value)

    def get_payload(self, event_type, event_value):
        """
        生成要發送到 MQTT 的 payload
        """
        return {
            "chip_id": self.chip_id,
            "machine_name": self.name,
            "event_type": event_type,
            "event_value": event_value,
            "credits": self.credits,
            "total_coins_in": self.total_coins_in,
            "total_payouts": self.total_payouts,
            "timestamp": self.last_event_time.isoformat(timespec='seconds')
        }
