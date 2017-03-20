SELECT * FROM todos.subs;CREATE TABLE `subs` (
  `user_id` int(11) NOT NULL,
  `todo_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`todo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
