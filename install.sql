-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.5.47-0+deb8u1 - (Debian)
-- Server Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle etherpad.padman_group
CREATE TABLE IF NOT EXISTS `padman_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_alias` varchar(100) NOT NULL DEFAULT '',
  `menu_title` varchar(100) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '1000',
  `group_mapper` varchar(100) NOT NULL DEFAULT '',
  `group_id` varchar(100) NOT NULL DEFAULT '',
  `tags` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Daten Export vom Benutzer nicht ausgewählt


-- Exportiere Struktur von Tabelle etherpad.padman_pad_cache
CREATE TABLE IF NOT EXISTS `padman_pad_cache` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_mapper` varchar(100) NOT NULL DEFAULT '',
  `group_id` varchar(100) NOT NULL DEFAULT '',
  `group_alias` varchar(100) NOT NULL DEFAULT '',
  `pad_name` varchar(100) NOT NULL DEFAULT '',
  `last_edited` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `password` varchar(100) DEFAULT NULL,
  `access_level` int(11) NOT NULL,
  `tags` varchar(255) NOT NULL DEFAULT '',
  `shortlink` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pad_id` (`group_mapper`,`pad_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Daten Export vom Benutzer nicht ausgewählt


-- Exportiere Struktur von Tabelle etherpad.padman_user
CREATE TABLE IF NOT EXISTS `padman_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(25) NOT NULL,
  `alias` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Daten Export vom Benutzer nicht ausgewählt
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

