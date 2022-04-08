/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;

CREATE TABLE `locations` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(200)     NOT NULL,
  `latitude`  FLOAT                     DEFAULT NULL,
  `longitude` FLOAT                     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `dates` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `startDate`   DATETIME         NOT NULL,
  `endDate`     DATETIME                  DEFAULT NULL,
  `title`       VARCHAR(200)     NOT NULL,
  `description` TEXT                      DEFAULT NULL,
  `locationId`  INT(11) UNSIGNED          DEFAULT NULL,
  `highlight`   BOOL             NOT NULL DEFAULT FALSE,
  `isPublic`    BOOL             NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY (`locationId`),
  CONSTRAINT FOREIGN KEY (`locationId`) REFERENCES `locations` (`id`)
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `dategroups` (
  `id`     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateId` INT(11) UNSIGNED NOT NULL,
  `name`   VARCHAR(100)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`dateId`, `name`),
  CONSTRAINT FOREIGN KEY (`dateId`) REFERENCES `dates` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `users` (
  `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`              VARCHAR(100)     NOT NULL,
  `email`                 VARCHAR(100)              DEFAULT NULL,
  `newEmail`              VARCHAR(100)              DEFAULT NULL,
  `newEmailKey`           VARCHAR(32)               DEFAULT NULL,
  `newEmailDate`          DATETIME                  DEFAULT NULL,
  `password`              VARCHAR(255)              DEFAULT NULL,
  `resetPasswordKey`      VARCHAR(32)               DEFAULT NULL,
  `resetPasswordDate`     DATETIME                  DEFAULT NULL,
  `requirePasswordChange` BOOLEAN          NOT NULL DEFAULT FALSE,
  `firstName`             VARCHAR(100)              DEFAULT NULL,
  `lastName`              VARCHAR(100)              DEFAULT NULL,
  `birthDate`             DATE                      DEFAULT NULL,
  `totpKey`               VARCHAR(32)               DEFAULT NULL,
  `datesToken`            VARCHAR(32)               DEFAULT NULL,
  `lastOnline`            DATETIME                  DEFAULT NULL,
  `enabled`               BOOLEAN          NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`username`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `usedtotptokens` (
  `id`     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` INT(11) UNSIGNED NOT NULL,
  `token`  VARCHAR(6)       NOT NULL,
  `date`   DATETIME         NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userId`, `token`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `usercontacts` (
  `id`       INT(11) UNSIGNED            NOT NULL AUTO_INCREMENT,
  `userId`   INT(11) UNSIGNED            NOT NULL,
  `type`     SET ('phone', 'mobile')     NOT NULL,
  `category` SET ('private', 'business') NOT NULL,
  `value`    VARCHAR(200)                NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`userId`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `uploads` (
  `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`      VARCHAR(8)       NOT NULL,
  `filename` VARCHAR(200)     NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`key`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `messages` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date`            DATETIME         NOT NULL,
  `senderUserId`    INT(11) UNSIGNED NOT NULL,
  `text`            LONGTEXT         NOT NULL,
  `visibleToSender` BOOLEAN                   DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY (`senderUserId`),
  CONSTRAINT FOREIGN KEY (`senderUserId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `messagerecipients` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `messageId` INT(11) UNSIGNED NOT NULL,
  `userId`    INT(11) UNSIGNED NOT NULL,
  `visible`   BOOLEAN                   DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`messageId`, `userId`),
  CONSTRAINT FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `messageattachments` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `messageId` INT(11) UNSIGNED NOT NULL,
  `uploadId`  INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`messageId`, `uploadId`),
  CONSTRAINT FOREIGN KEY (`messageId`) REFERENCES `messages` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `forms` (
  `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(100)     NOT NULL,
  `name`     VARCHAR(100)     NOT NULL,
  `title`    VARCHAR(200)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `notedirectoryprograms` (
  `id`    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `year`  YEAR             NOT NULL,
  `name`  VARCHAR(200)     NOT NULL,
  `title` VARCHAR(200)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`year`, `name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `notedirectorytitles` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`     VARCHAR(200)     NOT NULL,
  `composer`  VARCHAR(200)              DEFAULT NULL,
  `arranger`  VARCHAR(200)              DEFAULT NULL,
  `publisher` VARCHAR(200)              DEFAULT NULL,
  `details`   TEXT                      DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `notedirectoryprogramtitles` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `programId` INT(11) UNSIGNED NOT NULL,
  `titleId`   INT(11) UNSIGNED NOT NULL,
  `number`    INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`programId`, `titleId`),
  CONSTRAINT FOREIGN KEY (`programId`) REFERENCES `notedirectoryprograms` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`titleId`) REFERENCES `notedirectorytitles` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `protocols` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uploadId`       INT(11) UNSIGNED NOT NULL,
  `uploaderUserId` INT(11) UNSIGNED NOT NULL,
  `title`          VARCHAR(200)     NOT NULL,
  `date`           DATE             NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uploadId`),
  CONSTRAINT FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT FOREIGN KEY (`uploaderUserId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `protocolgroups` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `protocolId` INT(11) UNSIGNED NOT NULL,
  `name`       VARCHAR(100)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`protocolId`, `name`),
  CONSTRAINT FOREIGN KEY (`protocolId`) REFERENCES `protocols` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `visits` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`         VARCHAR(50),
  `date`       DATE             NOT NULL,
  `firstVisit` TIME             NOT NULL,
  `lastVisit`  TIME             NOT NULL,
  `userId`     INT(11) UNSIGNED          DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`ip`, `date`, `userId`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sessions` (
  `id`     VARCHAR(100) NOT NULL,
  `date`   DATETIME    NOT NULL,
  `data`   TEXT        NOT NULL,
  `userId` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;