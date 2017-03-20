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
	if (!array_key_exists('user_id', $input) || !array_key_exists('todo_id', $input)) {
		http_response_code(400);
		die('Expected a "user_id" and "todo_id" field in the supplied JSON object.');
	}
	
	// Get connection to database.
	$databaseConnection = DatabaseConnection::getConnection();
	
	// Escape the description.
	$todoId = $databaseConnection->real_escape_string($input['todo_id']);
	$userId = $databaseConnection->real_escape_string($input['user_id']);
	
	
	// Prepare statement to check this combination isn't already present.
	$sqlExistingSubCheckStatement = 'SELECT * FROM subs WHERE todo_id = ' . $todoId . ' AND user_id = ' . $userId;

	// Execute statement to check this combination isn't already present.
	$existingSubCheckResult = mysqli_query($databaseConnection, $sqlExistingSubCheckStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$existingSubCheckResult ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Check the todo exists.
	if (mysqli_num_rows($existingSubCheckResult) != 0) {
		http_response_code(409);
		die('This user is already subscribed to this Todo.');
	}
	
	
	// Prepare statement to check this is a valid user id.
	$sqlExistingUserCheckStatement = 'SELECT * FROM users WHERE user_id = ' . $userId;
	
	// Execute statement to check this user exists.
	$existingUserCheckResult = mysqli_query($databaseConnection, $sqlExistingUserCheckStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$existingUserCheckResult ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Check the todo exists.
	if (mysqli_num_rows($existingUserCheckResult) == 0) {
		http_response_code(404);
		die('No user exists with the ID supplied.');
	}
	
	
	// Prepare statement to check this is a valid todo id.
	$sqlExistingTodoCheckStatement = 'SELECT * FROM todos WHERE todo_id = ' . $todoId;
	
	// Execute statement to check this todo exists.
	$existingTodoCheckResult = mysqli_query($databaseConnection, $sqlExistingTodoCheckStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$existingTodoCheckResult ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Check the todo exists.
	if (mysqli_num_rows($existingTodoCheckResult) == 0) {
		http_response_code(404);
		die('No Todo exists with the ID supplied.');
	}	
	
	// Get the returned row.
	$existingTodoCheckResultRow = mysqli_fetch_object($existingTodoCheckResult);
	
	// Check the todo is undone.
	if ($existingTodoCheckResultRow->status != 0) {
		http_response_code(400);
		die('The Todo with the ID supplied has already been marked completed - so subscribing to it is not possible.');
	}
	
	
	// Build SQL statement for creating a subscription.
	$sqlStatement = 'INSERT INTO subs (user_id, todo_id) VALUES("' . $userId . '", "' . $todoId . '")';
	
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