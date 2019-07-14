import paho.mqtt.client as mqtt
import json
import time


def on_connect(client, userdata, flags, rc):
    print("Connected with result code "+str(rc))

    # Subscribing in on_connect() means that if we lose the connection and
    # reconnect then subscriptions will be renewed.
    client.subscribe("seminar/panasonic/iot")


def on_message(client, userdata, message):
    payload = str(message.payload.decode("utf-8"))
    print("message received: ", payload)
    print("message topic = ", message.topic)
    print("message qos = ", message.qos)
    print("message retain flag = ", message.retain)

    parsed_message = json.loads(payload)
    # print(parsed_message)

    print("Current Time:         " + time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime(time.time())))
    print("Temperature Value 1:  " + str(parsed_message["temp_1"]))
    print("Temperature Value 2:  " + str(parsed_message["temp_2"]))
    print("Pressure Value:       " + str(parsed_message["pressure"]))
    print("-----------------------------------------")


print("Setting up new client instance to subscribe")
client = mqtt.Client("my_publisher")

client.on_connect = on_connect
client.on_message = on_message

print("Connect client to public broker")
client.connect(host="broker.hivemq.com", port=1883, keepalive=60, bind_address="")

print("Pick topic and subscribe")
topic = "seminar/panasonic/iot"
client.subscribe(topic=topic)

client.loop_forever()