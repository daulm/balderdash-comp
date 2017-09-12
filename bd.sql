-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.19-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for balderdash
CREATE DATABASE IF NOT EXISTS `balderdash` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `balderdash`;

-- Dumping structure for table balderdash.answers
CREATE TABLE IF NOT EXISTS `answers` (
  `AnswerID` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `GameID` int(32) unsigned NOT NULL DEFAULT '0',
  `PlayerID` int(32) unsigned NOT NULL DEFAULT '0',
  `BindAnswerID` int(32) unsigned DEFAULT '0',
  `AnswerText` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`AnswerID`),
  UNIQUE KEY `GameID_PlayerID` (`GameID`,`PlayerID`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table balderdash.games
CREATE TABLE IF NOT EXISTS `games` (
  `GameID` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `LobbyID` int(32) unsigned NOT NULL DEFAULT '0',
  `LaunchAnsTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `LaunchVoteTime` datetime DEFAULT NULL,
  `Clue` varchar(255) DEFAULT NULL,
  `DasherID` int(32) unsigned DEFAULT NULL,
  PRIMARY KEY (`GameID`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table balderdash.lobby
CREATE TABLE IF NOT EXISTS `lobby` (
  `LobbyID` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `Code` char(50) DEFAULT '0',
  `CreationTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `HostID` int(32) NOT NULL DEFAULT '0',
  `GameID` int(32) DEFAULT '0',
  `GameState` char(32) DEFAULT NULL,
  `DasherID` int(32) DEFAULT NULL,
  `Clue` varchar(255) DEFAULT NULL,
  `VoteTime` int(32) DEFAULT NULL,
  `AnswerTime` int(32) DEFAULT NULL,
  PRIMARY KEY (`LobbyID`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table balderdash.players
CREATE TABLE IF NOT EXISTS `players` (
  `PlayerID` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `PlayerName` char(25) DEFAULT '0',
  `LobbyID` int(32) unsigned DEFAULT '0',
  `LastCheck` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Score` int(11) unsigned DEFAULT '0',
  `OrderVal` float unsigned DEFAULT NULL,
  PRIMARY KEY (`PlayerID`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table balderdash.votes
CREATE TABLE IF NOT EXISTS `votes` (
  `VoteID` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `PlayerID` int(32) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of player that voted',
  `AnswerID` int(32) unsigned DEFAULT '0',
  `GameID` int(32) unsigned DEFAULT '0',
  PRIMARY KEY (`VoteID`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
