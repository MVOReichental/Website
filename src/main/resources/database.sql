/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

CREATE TABLE `locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `startDate` datetime NOT NULL,
  `endDate` datetime DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `locationId` int(11) unsigned DEFAULT NULL,
  `highlight` bool NOT NULL DEFAULT FALSE,
  `isPublic` bool NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY (`locationId`),
  CONSTRAINT FOREIGN KEY (`locationId`) REFERENCES `locations` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dategroups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dateId` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`dateId`, `name`),
  CONSTRAINT FOREIGN KEY (`dateId`) REFERENCES `dates` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `newEmail` varchar(100) DEFAULT NULL,
  `newEmailKey` varchar(32) DEFAULT NULL,
  `newEmailDate` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `resetPasswordKey` varchar(32) DEFAULT NULL,
  `resetPasswordDate` datetime DEFAULT NULL,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `totpKey` varchar(20) DEFAULT NULL,
  `lastOnline` datetime DEFAULT NULL,
  `enabled` boolean NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usedtotptokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `token` varchar(6) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userId`, `token`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usercontacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `type` set('phone', 'fax', 'mobile') NOT NULL,
  `category` set('private', 'business') NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`userId`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `uploads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(8) NOT NULL,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `senderUserId` int(11) unsigned NOT NULL,
  `text` longtext NOT NULL,
  `visibleToSender` boolean DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY (`senderUserId`),
  CONSTRAINT FOREIGN KEY (`senderUserId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messagerecipients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `messageId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `visible` boolean DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`messageId`, `userId`),
  CONSTRAINT FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messageattachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `messageId` int(11) unsigned NOT NULL,
  `uploadId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`messageId`, `uploadId`),
  CONSTRAINT FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `notedirectorycategories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `order` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectoryprograms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` year NOT NULL,
  `name` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `isDefault` boolean NOT NULL DEFAULT FALSE,
  `showCategories` boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`year`, `name`)
) Engine=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectorytitles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `composer` varchar(200) DEFAULT NULL,
  `arranger` varchar(200) DEFAULT NULL,
  `publisher` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`categoryId`),
  CONSTRAINT FOREIGN KEY (`categoryId`) REFERENCES `notedirectorycategories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectoryprogramtitles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `programId` int(11) unsigned NOT NULL,
  `titleId` int(11) unsigned NOT NULL,
  `number` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`programId`, `titleId`),
  CONSTRAINT FOREIGN KEY (`programId`) REFERENCES `notedirectoryprograms` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`titleId`) REFERENCES `notedirectorytitles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `protocols` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uploadId` int(11) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uploadId`),
  CONSTRAINT FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `protocolgroups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `protocolId` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`protocolId`, `name`),
  CONSTRAINT FOREIGN KEY (`protocolId`) REFERENCES `protocols` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `roomoccupancyplan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `date` date NOT NULL,
  `repeatTillDate` date DEFAULT NULL,
  `repeatWeekly` boolean NOT NULL DEFAULT TRUE,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `visits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `firstVisit` time NOT NULL,
  `lastVisit` time NOT NULL,
  `userId` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`ip`, `date`, `userId`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;