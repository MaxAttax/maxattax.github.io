import socket

# create an ipv4 (AF_INET) socket object using the tcp protocol (SOCK_STREAM)
client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# connect the client
# client.connect((target, port))
client.connect(('192.168.1.179', 9999))

# send some data (in this case a HTTP GET request)
while True:
    my_text = input("Waiting for input: ")
    print("Input Text: ")
    print(my_text)
    try:
        bytedata = my_text.encode('utf-8')
        ack = client.send(bytedata)

        # receive the response data (4096 is recommended buffer size)
        response = client.recv(1024)
        print(response)

        print("Return Text:")
        print(response.decode('utf-8'))
        print("-----------------------------------------------------")
    except ValueError as e:
        print(e)
        print("Something is wrong")
