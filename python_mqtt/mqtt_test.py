# mqtt_test.py
import paho.mqtt.client as mqtt

help(mqtt.Client.ws_set_options)

def on_connect(client, userdata, flags, rc):
    print("連線結果:", rc)

client = mqtt.Client(transport="websockets")
client.on_connect = on_connect  # <--- 加這一行
client.username_pw_set("joesong", "we123123")
client.ws_set_options(path="/mqtt")
client.connect("localhost", 9001)
client.loop_start()
import time; time.sleep(3)
client.loop_stop()
client.disconnect()
