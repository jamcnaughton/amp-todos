<?php

	// Get utility classes.
	include ('utils/DatabaseConnection.php');
	
	// Expecting a GET request - verify this.
	$method = $_SERVER['REQUEST_METHOD'];
	if ($method != 'GET') {
		http_response_code(405);
		die('Expecting only GET requests to this URI.');
	}
	
	// Get connection to database.
	$databaseConnection = DatabaseConnection::getConnection();
	
	// Build SQL statement for retrieving uncomplete todos.
	$sqlStatement = 'SELECT todo_id, description FROM todos WHERE status = 0';
	
	// Execute the SQL statement.
	$result = mysqli_query($databaseConnection, $sqlStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$result ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Get query results as an array.
	$resultsArray = array();
	for ($i=0; $i < mysqli_num_rows($result); $i++) {
		$resultsArray[] = mysqli_fetch_object($result);
	}
	
	// Output the results in JSON.
	echo json_encode($resultsArray);


?>