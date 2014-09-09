SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- База данных: `payment`
--

-- --------------------------------------------------------

--
-- Структура таблицы `variables`
--

DROP TABLE IF EXISTS `variables`;
CREATE TABLE IF NOT EXISTS `variables` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `value` varchar(255) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `neotech_requests`
--

DROP TABLE IF EXISTS `neotech_requests`;
CREATE TABLE IF NOT EXISTS `neotech_requests` (
  `id` bigint(20) NOT NULL auto_increment,
  `reqid` varchar(50) NOT NULL,
  `terminal_id` int(11) NOT NULL,
  `qid` varchar(32) NOT NULL,
  `body` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `qid` (`qid`,`terminal_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `neotech_responses`
--

DROP TABLE IF EXISTS `neotech_responses`;
CREATE TABLE IF NOT EXISTS `neotech_responses` (
  `id` bigint(20) NOT NULL auto_increment,
  `reqid` varchar(50) NOT NULL,
  `terminal_id` int(11) NOT NULL,
  `qid` varchar(32) NOT NULL,
  `body` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `qid` (`qid`,`terminal_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) NOT NULL auto_increment,
  `terminal_id` int(11) NOT NULL,
  `receipt` varchar(50) NOT NULL,
  `account` varchar(50) NOT NULL,
  `amount` double NOT NULL,
  `provider_date` datetime NOT NULL,
  `status` varchar(20) default NULL,
  `service_id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `reqid` varchar(50) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `terminals`
--

DROP TABLE IF EXISTS `terminals`;
CREATE TABLE IF NOT EXISTS `terminals` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `active` int(11) NOT NULL default '1',
  `deposit` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `terminal_log`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint(20) NOT NULL auto_increment,
  `account` varchar(50) NOT NULL,
  `terminal_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `events_idx` (`created`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Структура таблицы `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `active` int(11) NOT NULL default '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
