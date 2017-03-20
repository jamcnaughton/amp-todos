<?php
	
	// Get utility classes.
	include ('utils/DatabaseConnection.php');
	
	// Define local constants.
	$EMAIL_TITLE = 'TODO Completed';
	$EMAIL_MESSAGE = 'The following TODO has been marked as completed: ';
	
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
	
	// Get the description for notifications later.
	$description = $resultRow->description;
	
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
	
	// Create statement to get subscription entries with this todo id.
	$sqlGetSubscriptionsStatement = 'SELECT * FROM subs WHERE todo_id = ' . $todoId;
	
	// Execute statement to get subscription entries with this todo id.
	$subscriptionsResult = mysqli_query($databaseConnection, $sqlGetSubscriptionsStatement);
	
	// Provide an error if the SQL statement failed.
	if ( !$subscriptionsResult ) {
		http_response_code(503);
		die($databaseConnection->error);
	}
	
	// Loop through subscriptions.
	for ($i = 0; $i < mysqli_num_rows($subscriptionsResult); $i++) {
		$subscription = mysqli_fetch_object($subscriptionsResult);
		
		// Get user relating to subscription.
		$userId = $subscription->user_id;
		
		// Create statement to get user e-mail with.
		$sqlGetEmailStatement = 'SELECT * FROM users WHERE user_id = ' . $userId;
		
		// Execute statement to get the user e-mail.
		$emailResult = mysqli_query($databaseConnection, $sqlGetEmailStatement);

		// Provide an error if the SQL statement failed.
		if ( !$emailResult ) {
			http_response_code(503);
			die($databaseConnection->error);
		}

		// Check user still exists.
		if (mysqli_num_rows($emailResult) == 0) {
			
			// Delete related subs if this user doesn't exist anymore.
			$sqlDeleteSubsStatement = 'DELETE FROM subs WHERE user_id = ' . $userId;
			mysqli_query($databaseConnection, $sqlDeleteSubsStatement);			
			break;
		}
		
		// Get e-mail for user.
		$emailRecord = mysqli_fetch_object($emailResult);
		$email = $emailRecord->email;
		
		// Send e-mail to user informing them of the update to the todo.
		mail($email, $EMAIL_TITLE, $EMAIL_MESSAGE . $description);
		
	}
	
	// Prepare output array with a flag to show it was successful.
	$resultsArray = array();
	$resultsArray['success'] = true;
	
	// Output the results in JSON.
	echo json_encode($resultsArray);

?>