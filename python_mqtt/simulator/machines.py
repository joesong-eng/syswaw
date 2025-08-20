import mysql.connector
from collections import defaultdict

DB_CONFIG = {
    'database': 'sxswaw',
    'user': 'sxswaw',
    'password': '2a@684240',
    'host': '127.0.0.1',
    'unix_socket': '/tmp/mysql.sock'
}

def fetch_machine_configs():
    """
    從資料庫中獲取 ID 小於 10 的機器配置，並按類型分類。
    """
    categorized_machines = defaultdict(list)
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT m.id, m.name, m.machine_category, m.coin_input_value, m.payout_unit_value,
                   a.chip_hardware_id, a.auth_key
            FROM machines m
            JOIN machine_auth_keys a ON m.auth_key_id = a.id
            WHERE m.is_active=1 AND m.deleted_at IS NULL
              AND a.chip_hardware_id IS NOT NULL AND a.auth_key IS NOT NULL
              AND a.status='active' AND a.id < 10
        """)
        rows = cursor.fetchall()

        for row in rows:
            category = row['machine_category']
            categorized_machines[category].append(row)

        print(f"Found and categorized {len(rows)} machines.")

    except Exception as e:
        print(f"DB error: {e}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

    return dict(categorized_machines)

if __name__ == '__main__':
    # 用於直接測試此模塊
    all_machines = fetch_machine_configs()
    if all_machines:
        for category, machines in all_machines.items():
            print(f"\n--- Category: {category} ---")
            for machine in machines:
                print(f"  Chip Hardware ID: {machine['chip_hardware_id']}")
    else:
        print("No machines found.")
