START TRANSACTION;
CREATE SCHEMA IF NOT EXISTS piBoard;
USE piBoard;

CREATE TABLE IF NOT EXISTS `Users`
(
    `Id`           int          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `Nickname`     VARCHAR(255) NOT NULL UNIQUE,
    `Hash`         VARCHAR(255) NOT NULL,
    `CreationDate` timestamp    NOT NULL          DEFAULT CURRENT_TIMESTAMP,
    `Role`         enum ('Founder','Admin','Mod') DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `Bans`
(
    `Id`        INT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `UserId`    int                DEFAULT NULL,
    `IpAddress` VARCHAR(255)       DEFAULT NULL,
    `StartDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `EndDate`   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`)
);

CREATE TABLE IF NOT EXISTS `Boards`
(
    `Id`        VARCHAR(5)   NOT NULL PRIMARY KEY,
    `CreatorId` INT,
    `FullName`  VARCHAR(255) NOT NULL,
    FOREIGN KEY (`CreatorId`) REFERENCES `Users`(`Id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `Threads`
(
    `Id`        int          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `CreatorId` INT          DEFAULT NULL,
    `IpAddress` VARCHAR(255) NOT NULL,
    `BoardId`   VARCHAR(5)   DEFAULT NULL,
    `Title`     VARCHAR(255) NOT NULL,
    `Content`   TEXT         NOT NULL,
    `MediaPath` VARCHAR(510) DEFAULT NULL,
    `MediaType` ENUM('image', 'video', 'url') DEFAULT NULL,
    FOREIGN KEY (`CreatorId`) REFERENCES `Users`(`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`BoardId`) REFERENCES `Boards`(`Id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `Comments`
(
    `Id`        int          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `CreatorId` int          DEFAULT NULL,
    `IpAddress` VARCHAR(255) NOT NULL,
    `ThreadId`  int          NOT NULL,
    `Content`   text         NOT NULL,
    `MediaPath` VARCHAR(510) DEFAULT NULL,
    `MediaType` ENUM('image', 'video', 'url') DEFAULT NULL,
    FOREIGN KEY (`CreatorId`) REFERENCES `Users`(`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`ThreadId`) REFERENCES `Threads`(`Id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Database starts empty. Create founder user using:
-- php scripts/create_founder.php <username> <password>

COMMIT