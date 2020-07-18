-- --------------------------------------------------------
-- Хост:                         10.10.1.5
-- Версия сервера:               5.1.67 - Source distribution
-- Операционная система:         redhat-linux-gnu
-- HeidiSQL Версия:              10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Дамп структуры базы данных pbx
CREATE DATABASE IF NOT EXISTS `pbx` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `pbx`;

-- Дамп структуры для таблица pbx.bridges
CREATE TABLE IF NOT EXISTS `bridges` (
  `time_begin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uniqueid1` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `uniqueid2` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `channel1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `channel2` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `callerid1` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `callerid2` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `uniqueid` (`uniqueid1`,`uniqueid2`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для таблица pbx.channels
CREATE TABLE IF NOT EXISTS `channels` (
  `channel_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `num` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `uniqueid` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `channel` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `channelstate` tinyint(4) NOT NULL,
  `channelstatedesc` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `calleridnum` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `calleridname` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinenum` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinename` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `exten` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `event` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `time_begin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `uniqueid` (`uniqueid`),
  KEY `num` (`num`),
  KEY `channelstate` (`channelstate`),
  KEY `channel_type` (`channel_type`),
  KEY `channel` (`channel`),
  KEY `connectedlinenum` (`connectedlinenum`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для таблица pbx.dials
CREATE TABLE IF NOT EXISTS `dials` (
  `time_begin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `destination` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `calleridnum` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `calleridname` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinenum` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinename` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `uniqueid` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `destuniqueid` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `dialstring` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `dialstatus` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  KEY `uniqueid` (`uniqueid`),
  KEY `destuniqueid` (`destuniqueid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для таблица pbx.peers
CREATE TABLE IF NOT EXISTS `peers` (
  `num` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `goip_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `did` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `registered` tinyint(4) NOT NULL DEFAULT '0',
  `reachable` tinyint(4) NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dnd` tinyint(4) NOT NULL DEFAULT '0',
  `is_operator` tinyint(4) NOT NULL DEFAULT '0',
  `is_trunk` tinyint(4) NOT NULL DEFAULT '0',
  `is_registry` tinyint(4) NOT NULL,
  `last_update` datetime NOT NULL,
  `last_state` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`num`),
  KEY `is_registry` (`is_registry`),
  KEY `is_trunk` (`is_trunk`),
  KEY `is_operator` (`is_operator`),
  KEY `goip_id` (`goip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для таблица pbx.queues
CREATE TABLE IF NOT EXISTS `queues` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `calleridnum` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `calleridname` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinenum` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `connectedlinename` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `queue` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `position` tinyint(4) NOT NULL,
  `count` tinyint(4) NOT NULL,
  `uniqueid` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  KEY `uniqueid` (`uniqueid`),
  KEY `queue` (`queue`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для таблица pbx.queue_abandon
CREATE TABLE IF NOT EXISTS `queue_abandon` (
  `queue` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `abandon_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uniqueid` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `position` tinyint(4) NOT NULL,
  `originalposition` tinyint(4) NOT NULL,
  `holdtime` int(11) NOT NULL,
  `calleridnum` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `calleridname` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  KEY `abandon_time` (`abandon_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.
-- Дамп структуры для триггер pbx.after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `after_update` BEFORE UPDATE ON `peers` FOR EACH ROW BEGIN
SET NEW.last_update = NOW();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Дамп структуры для триггер pbx.before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `before_update` BEFORE UPDATE ON `channels` FOR EACH ROW BEGIN
CASE
WHEN OLD.calleridnum <> '' THEN 
	BEGIN
		SET NEW.calleridnum = OLD.calleridnum;
	END;
WHEN OLD.calleridname <> '' THEN 
	BEGIN
		SET NEW.calleridname = OLD.calleridname;
	END;
ELSE BEGIN END;
END CASE;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
