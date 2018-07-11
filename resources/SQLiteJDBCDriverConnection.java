package rwth.ima.seminar.db;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;

public class SQLiteJDBCDriverConnection {
	
	public static void initialize() {
		
		String url = "jdbc:sqlite:myfactory.db";
        
        // SQL statement for creating a new table
        String sql = "CREATE TABLE IF NOT EXISTS factoryData (\n"
                + "	id integer PRIMARY KEY,\n"
                + "	machine text,\n"
                + "	temperature real,\n"
                + "	humidity integer,\n"
                + "	pressure real\n"
                + ");";
        
        String sql_time = "CREATE TABLE IF NOT EXISTS factoryDataTime (\n"
                + "	id integer PRIMARY KEY,\n"
                + "	time text,\n"
                + "	machine text,\n"
                + "	temperature real,\n"
                + "	humidity integer,\n"
                + "	pressure real\n"
                + ");";
        
        try (Connection conn = DriverManager.getConnection(url);
                Statement stmt = conn.createStatement()) {
            // create a new table
            stmt.execute(sql);
            stmt.execute(sql_time);
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
        
	}
	
    /**
    * Connect to a sample database
    */
   public static Connection connect() {
       Connection conn = null;
       try {
           // db parameters
           String url = "jdbc:sqlite:myfactory.db";
           // create a connection to the database
           conn = DriverManager.getConnection(url);
           
           System.out.println("Connection to SQLite has been established.");
           
       } catch (SQLException e) {
           System.out.println(e.getMessage());
       } finally {
           try {
               if (conn != null) {
                   conn.close();
               }
           } catch (SQLException ex) {
               System.out.println(ex.getMessage());
           }
       }
       return conn;
   }
   
}