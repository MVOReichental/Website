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
  `date` date NOT NULL,
  `title` varchar(200) NOT NULL,
  `text` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coverPictureId` (`coverPictureId`),
  CONSTRAINT `picturealbums_ibfk1` FOREIGN KEY (`coverPictureId`) REFERENCES `pictures` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET =utf8;

CREATE TABLE `pictures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `albumId` int(11) unsigned NOT NULL,
  `file` varchar(32) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `albumId` (`albumId`),
  CONSTRAINT `pictures_ibfk1` FOREIGN KEY (`albumId`) REFERENCES `picturealbums` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;