<?php

	/**
	 * Class for handling static functions for providing
	 * todo database connections.
	 * 
	 * @author JAMcNaughton
	 *
	 */
	class DatabaseConnection {
		
		/** Constant representing the location and name of the database ini file to load the connection details from. */
		private static $DB_INI_FILE = 'config/db.ini';
		
		/**
		 * Read from db.ini file to get database connection details.
		 * Use the details to connect and return the connection
		 * as an object.
		 * 
		 * @return stdClass The connection as an object.
		 */
		public static function getConnection() {
			
			// Read from ini file.
			$dbConfig = parse_ini_file (DatabaseConnection::$DB_INI_FILE);
			
			// Get relevant values from config.
			$host = $dbConfig['host'];
			$user = $dbConfig['user'];
			$pass = $dbConfig['password'];
			$databaseName = $dbConfig['database_name'];
			
			// Create database connection.
			$databaseConnection = mysqli_connect($host, $user, $pass, $databaseName);
			
			// Check connection was successful.
			if (!$databaseConnection) {
				http_response_code(503);
				die($databaseConnection->connect_error);				
			}
			
			// Set connection to utf8 because utf8 makes life easier down the road.
			mysqli_set_charset($databaseConnection,'utf8');
			
			// Return the connection.
			return $databaseConnection;
		}
		
	}


?>