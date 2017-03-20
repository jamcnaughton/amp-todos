<?php
	
	// Get utility classes.
	include ('utils/DatabaseConnection.php');
	
	// Expecting a POST request - verify this.
	$method = $_SERVER['REQUEST_METHOD'];
	if ($method != 'POST') {
		http_response_code(405);
		die('Expecting only POST requests to this URI.');
	}
	
	// Get the provided json.
	$input = json_decode(file_get_contents('php://input'), true);

	// Check that JSON object has required fields.
	if (!array_key_exists('description', $input)) {
		http_response_code(400);
		die('Expected a "description" field in the supplied JSON object.');
	}
	
	// Get connection to database.
	$databaseConnection = DatabaseConnection::getConnection();
	
	// Escape the description.
	$description = $databaseConnection->real_escape_string($input['description']);
	
	// Build SQL statement for creating a todo.
	$sqlStatement = 'INSERT INTO todos (description, status) VALUES("' . $description . '", 0)';
	
	// Execute the SQL statement.
	$result = mysqli_query($databaseConnection, $sqlStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$result ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Prepare output array with generated id for the todo.
	$resultsArray = array();
	$resultsArray['todo_id'] = $databaseConnection->insert_id;
	$resultsArray['success'] = true;
	
	// Output the results in JSON.
	echo json_encode($resultsArray);	

?>