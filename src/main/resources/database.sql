/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

CREATE TABLE `locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `startDate` datetime NOT NULL,
  `endDate` datetime DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `locationId` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locationId` (`locationId`),
  CONSTRAINT `dates_ibfk1` FOREIGN KEY (`locationId`) REFERENCES `locations` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `picturealbums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coverPictureId` int(11) unsigned DEFAULT NULL,
  `year` year NOT NULL,
  `date` date NOT NULL,
  `name` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `text` text DEFAULT NULL,
  `published` boolean NOT NULL DEFAULT FALSE,
  `isPublic` boolean NOT NULL DEFAULT FALSE,
  `isAlbumOfTheYear` boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `coverPictureId` (`coverPictureId`),
  UNIQUE KEY `year_name` (`year`, `name`),
  CONSTRAINT `picturealbums_ibfk1` FOREIGN KEY (`coverPictureId`) REFERENCES `pictures` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET =utf8;

CREATE TABLE `pictures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `albumId` int(11) unsigned NOT NULL,
  `file` varchar(32) NOT NULL,
  `number` int(11) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `albumId` (`albumId`),
  UNIQUE KEY `albumId_number` (`albumId`, `number`),
  CONSTRAINT `pictures_ibfk1` FOREIGN KEY (`albumId`) REFERENCES `picturealbums` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `newEmail` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `totpKey` varchar(20) DEFAULT NULL,
  `lastOnline` datetime DEFAULT NULL,
  `enabled` boolean NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usedtotptokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `token` varchar(6) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_token` (`userId`, `token`),
  CONSTRAINT `usedtotptokens_ibfk1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usercontacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `type` set('phone', 'fax', 'mobile') NOT NULL,
  `category` set('private', 'business') NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `usercontacts_ibfk1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `uploads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `senderUserId` int(11) unsigned NOT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `senderUserId` (`senderUserId`),
  CONSTRAINT `messages_ibfk1` FOREIGN KEY (`senderUserId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messagerecipients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `messageId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `messageId_userId` (`messageId`, `userId`),
  CONSTRAINT `messagerecipients_ibfk1` FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `messagerecipients_ibfk2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messageattachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `messageId` int(11) unsigned NOT NULL,
  `uploadId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `messageId_uploadId` (`messageId`, `uploadId`),
  CONSTRAINT `messageattachments_ibfk1` FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `messageattachments_ibfk2` FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;