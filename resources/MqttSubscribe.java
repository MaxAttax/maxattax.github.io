package rwth.ima.seminar.mqtt;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;

import rwth.ima.seminar.db.SQLiteJDBCDriverConnection;

public class MqttSubscribe {

	public static void main(String[] args) throws MqttException, SQLException {
		
		SQLiteJDBCDriverConnection sqLiteJDBCDriverConnection = new SQLiteJDBCDriverConnection();
		Connection conn = DriverManager.getConnection("jdbc:sqlite:myfactory.db");
		sqLiteJDBCDriverConnection.initialize();
		
	}
	
}
