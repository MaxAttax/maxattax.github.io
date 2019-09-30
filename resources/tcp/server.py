import socket
import threading

bind_ip = '192.168.1.179'
bind_port = 9999

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server.bind((bind_ip, bind_port))
server.listen(5)  # max backlog of connections

print('Listening on {}:{}'.format(bind_ip, bind_port))


def handle_client_connection(client_socket):
    request = client_socket.recv(1024)
    print('Received {}'.format(request))
    client_socket.send('ACK!')
    client_socket.close()


client_sock, address = server.accept()
print('Accepted connection from {}:{}'.format(address[0], address[1]))
client_handler = threading.Thread(
    target=handle_client_connection,
    args=(client_sock,)
)

while True:
    data = client_sock.recv(1024)
    print("received from client")
    request_str = data.decode('utf-8')
    answer_str = request_str.upper()
    print(answer_str)
    byte_resp = answer_str.encode('utf-8')
    print("send result to client")
    client_sock.sendall(byte_resp)
