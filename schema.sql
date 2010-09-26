CREATE TABLE  `tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(100) NOT NULL,
  `hits` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`tag`),
  KEY `id` (`id`)
);

CREATE TABLE  `uploads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(80) default NULL,
  `description` text NOT NULL,
  `tags` text NOT NULL,
  `public` int(1) NOT NULL,
  `name` varchar(60) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
);

CREATE TABLE  `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pseudo` varchar(20) NOT NULL,
  `session` varchar(40) default NULL,
  `password` varchar(30) default NULL,
  `membre` int(1) NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
);
