import socket
import time
import random
import threading

SERVER_IP = "127.0.0.1"
SERVER_PORT = 39001
NUM_SIMULATORS = 3

def generate_random_increment():
    return random.choice([0, 1])

def generate_random_data(machine_id, token):
    ball_in = random.randint(0, 100)
    ball_out = random.randint(0, 50)
    credit_in = random.randint(0, 200)

    def update_data():
        nonlocal ball_in, ball_out, credit_in
        ball_in += generate_random_increment()
        ball_out += generate_random_increment()
        credit_in += generate_random_increment()

    def get_data():
        return f"@{machine_id}:{token}#{ball_in:06} {ball_out:06} {credit_in:06}\n"

    return update_data, get_data

def simulate_esp32(machine_id, token):
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as client_socket:
            client_socket.connect((SERVER_IP, SERVER_PORT))
            print(f"模擬器 {machine_id} 連接伺服器.....")

            response = client_socket.recv(1024)
            print(f"模擬器 {machine_id} 伺服器回應: {repr(response)}")

            if response.decode('utf-8').strip() == "connected":
                print(f"模擬器 {machine_id} 連線成功，開始發送數據...\n")

                update_data, get_data = generate_random_data(machine_id, token)

                while True:
                    update_data()
                    data = get_data()
                    print(f"模擬器 {machine_id} 發送數據: {data}")

                    client_socket.sendall(data.encode('utf-8'))
                    time.sleep( 3)
            else:
                print(f"模擬器 {machine_id} 連線失敗，伺服器未返回正確的回應。")
    except ConnectionRefusedError:
        print(f"模擬器 {machine_id} 無法連接到伺服器，請檢查伺服器是否在運行。")
    except Exception as e:
        print(f"模擬器 {machine_id} 發生錯誤: {e}")

if __name__ == "__main__":
    threads = []
    for i in range(NUM_SIMULATORS):
        machine_id = f"sim_{i:03}"
        token = f"token_{i:03}"
        thread = threading.Thread(target=simulate_esp32, args=(machine_id, token))
        threads.append(thread)
        thread.start()

    for thread in threads:
        thread.join()