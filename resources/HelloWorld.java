package rwth.ima.mqtt;

import org.eclipse.paho.client.mqttv3.IMqttDeliveryToken;
import org.eclipse.paho.client.mqttv3.MqttCallback;
import org.eclipse.paho.client.mqttv3.MqttClient;
import org.eclipse.paho.client.mqttv3.MqttException;
import org.eclipse.paho.client.mqttv3.MqttMessage;

public class HelloWorld {
	
	public static void main(String[] args) throws MqttException {
		
		MqttClient mqttClient = new MqttClient("tcp://broker.hivemq.com", MqttClient.generateClientId());
		
		mqttClient.setCallback(new MqttCallback() {
			
			@Override
			public void messageArrived(String arg0, MqttMessage arg1) throws Exception {
				// TODO Auto-generated method stub
				System.out.println(arg1.toString());
			}
			
			@Override
			public void deliveryComplete(IMqttDeliveryToken arg0) {
				// TODO Auto-generated method stub
				
			}
			
			@Override
			public void connectionLost(Throwable arg0) {
				// TODO Auto-generated method stub
				
			}
		});
		
		mqttClient.connect();
		mqttClient.subscribe("Aachen/test/+");
		
	}

}
