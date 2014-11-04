CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL auto_increment,
  twitter_id BIGINT( 20 ) UNSIGNED NOT NULL,
  name varchar(255) default NULL,
  image varchar(255) default NULL,
  created DATETIME DEFAULT NULL,
  modified DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (twitter_id),
  UNIQUE KEY (name)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS messages (
  id int(11) NOT NULL auto_increment,
  user_id int(11) UNSIGNED NOT NULL,
  message varchar(255) default NULL,
  created DATETIME DEFAULT NULL,
  modified DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

