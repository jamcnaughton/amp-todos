# TODO Tool - A RESTful API

## Notes
I've only implemented the features specifically outlined in the requirements. There are more features which could be added that are likely needed by the scenario (ability to unsubscribe from todos, deleting todos, authorisation in request headers, etc) but have been omitted due to time constraints.

## Assumptions
* PHP7 + MySQLi extension.
* Access to the API is controlled outside of the tool.
* The server the tool runs on is configured for sending e-mails through the PHP mail function.
* A MySQL database exists with details that match with the db.ini file.
* In said MySQL database the follow table should exist:
  * *todos* with this schema: [todos.sql](https://raw.githubusercontent.com/jamcnaughton/amp-todos/dev/todos.sql)
* The example request URIs below assume the API is run on the localhost under a **todos** directory.

## Requests

### Get Undone Todos
GET | http://localhost/todos/ListIncompleteTodos.php

### Create Todo
POST | http://localhost/todos/CreateTodo.php
```json
{ 
  "description": "Buy milk"
}
```

### Complete Todo
PUT | http://localhost/todos/CompleteTodo.php 
```PUT
{ 
  "todo_id": 3
}
```
