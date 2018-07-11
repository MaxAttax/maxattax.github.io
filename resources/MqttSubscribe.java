package rwth.ima.seminar.mqtt;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Date;

import org.eclipse.paho.client.mqttv3.IMqttDeliveryToken;
import org.eclipse.paho.client.mqttv3.MqttCallback;
import org.eclipse.paho.client.mqttv3.MqttClient;
import org.eclipse.paho.client.mqttv3.MqttException;
import org.eclipse.paho.client.mqttv3.MqttMessage;

// import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

import rwth.ima.seminar.db.SQLiteJDBCDriverConnection;

public class MqttSubscribe {

	public static void main(String[] args) throws MqttException, SQLException {
		
		SQLiteJDBCDriverConnection sqLiteJDBCDriverConnection = new SQLiteJDBCDriverConnection();
		Connection conn = DriverManager.getConnection("jdbc:sqlite:myfactory.db");
		sqLiteJDBCDriverConnection.initialize();
		
		MqttClient mqttClient = new MqttClient("tcp://broker.hivemq.com:1883", 
					MqttClient.generateClientId());
		
		// MqttClient mqttClient = new MqttClient("tcp://192.168.1.16:1883", 
		//			MqttClient.generateClientId());
		
		ObjectMapper objectMapper = new ObjectMapper();
		
		mqttClient.setCallback(new MqttCallback() {
			
			@Override
			public void messageArrived(String topic, MqttMessage message) throws Exception {
				String factoryData = new String(message.getPayload());
				System.out.println(factoryData);
				System.out.println(topic);

				MachineData inMachineData = objectMapper.readValue(factoryData, MachineData.class);
				Date timestamp = new Date();
				System.out.println("Current Time:      " + timestamp);
				System.out.println("Temperature Value: " + inMachineData.getTemp());
				System.out.println("Humidity Value:    " + inMachineData.getHumidity());
				System.out.println("Pressure Value:    " + inMachineData.getPressure());
				
				String[] machineStrings = topic.split("/");
				String machine = machineStrings[machineStrings.length-1];
				System.out.println("Machine: " + machine);
				
		        String sql = "INSERT INTO factoryDataTime(time, machine,temperature,humidity,pressure) "
		        		+ "VALUES(?,?,?,?,?)";
		        
		        try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
		        	pstmt.setString(1, timestamp.toString());
		        	pstmt.setString(2, machine);
		            pstmt.setDouble(3, inMachineData.getTemp());
		            pstmt.setInt(4, inMachineData.getHumidity());
		            pstmt.setDouble(5, inMachineData.getPressure());
		            System.out.println("About to insert");
		            pstmt.executeUpdate();
		            System.out.println("Successfully executed");
		        } catch (SQLException e) {
		            System.out.println(e.getMessage());
		        }
		        System.out.println("-------------------------------------------------------------------------------");
				
			}
			
			@Override
			public void deliveryComplete(IMqttDeliveryToken token) {
				// TODO Auto-generated method stub
				
			}
			
			@Override
			public void connectionLost(Throwable cause) {
				// TODO Auto-generated method stub
				
			}
		});
		
		mqttClient.connect();
		mqttClient.subscribe("factory/machines/#");
		// mqttClient.subscribe("rwth/seminars/#");
		
	}
	
}
