<?php
	
	// Get utility classes.
	include ('utils/DatabaseConnection.php');
	
	// Expecting a PUT request - verify this.
	$method = $_SERVER['REQUEST_METHOD'];
	if ($method != 'PUT') {
		http_response_code(405);
		die('Expecting only PUT requests to this URI.');
	}
	
	// Get the provided json.
	$input = json_decode(file_get_contents('php://input'), true);
	
	// Check that JSON object has required fields.
	if (!array_key_exists('todo_id', $input)) {
		http_response_code(400);
		die('Expected a "todo_id" field in the supplied JSON object.');
	}
	
	// Get connection to database.
	$databaseConnection = DatabaseConnection::getConnection();
	
	// Escape the todo id.
	$todoId = $databaseConnection->real_escape_string($input['todo_id']);
	
	// Build SQL statement for checking the todo exists.
	$sqlCheckStatement = 'SELECT * FROM todos WHERE todo_id = ' . $todoId;
	
	// Execute the SQL statement.
	$resultCheck = mysqli_query($databaseConnection, $sqlCheckStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$resultCheck ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Check the todo exists.
	if (mysqli_num_rows($resultCheck) == 0) {
		http_response_code(404);
		die('No Todo exists with the ID supplied.');
	}
	
	// Get the returned row.
	$resultRow = mysqli_fetch_object($resultCheck);
	
	// Check the todo is undone.
	if ($resultRow->status != 0) {
		http_response_code(400);
		die('The Todo with the ID supplied has already been marked completed.');
	}
	
	// Build SQL statement for updating the record.
	$sqlStatement = 'UPDATE todos SET status = 1 WHERE todo_id = ' . $todoId;
	
	// Execute the SQL statement.
	$result = mysqli_query($databaseConnection, $sqlStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$result ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Prepare output array with a flag to show it was successful.
	$resultsArray = array();
	$resultsArray['success'] = true;
	
	// Output the results in JSON.
	echo json_encode($resultsArray);

?>