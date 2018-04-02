CREATE TABLE `messages` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pseudo` text NOT NULL,
  `message` text NOT NULL,
  `couleur` text NOT NULL,
  `pieceJointe` text NOT NULL,
  PRIMARY KEY (`time`,`pseudo`(5))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `pseudo` varchar(10) NOT NULL,
  `password` text NOT NULL,
  PRIMARY KEY (`pseudo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


