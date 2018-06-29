create database socguess;

CREATE TABLE `matchs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `matchid` int(11) DEFAULT NULL,
  `teamA` varchar(64) DEFAULT NULL,
  `teamB` varchar(64) DEFAULT NULL,
  `begin_time` timestamp NULL DEFAULT NULL COMMENT '',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '',
  `score` varchar(32) DEFAULT NULL COMMENT '',
  `status` tinyint(4) DEFAULT '1' COMMENT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `competition` varchar(64) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`),
  KEY `m_b_s` (`matchid`,`begin_time`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `matchs_games` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `matchid` int(11) NOT NULL,
  `gameid` int(11) NOT NULL,
  `address_gid` int(11) DEFAULT NULL,
  `handicap` varchar(16) DEFAULT '0' COMMENT '',
  `match_sp` varchar(128) DEFAULT NULL COMMENT '',
  `status` tinyint(4) DEFAULT '0',
  `create_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(256) DEFAULT NULL COMMENT '',
  `group_id` int(11) DEFAULT NULL,
  `private_key` varchar(256) DEFAULT NULL,
  `bet_label` tinyint(4) DEFAULT '0' COMMENT '',
  `status` tinyint(4) DEFAULT '0' COMMENT '',
  `create_time` timestamp NULL DEFAULT '2018-06-28 08:00:00',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `s_g_a` (`status`,`group_id`,`address`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `gamble_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txid` varchar(256) DEFAULT NULL COMMENT '',
  `address` varchar(256) DEFAULT NULL COMMENT '',
  `value` int(11) DEFAULT NULL COMMENT '',
  `result` tinyint(4) DEFAULT '1' COMMENT '',
  `matchid` int(11) DEFAULT NULL COMMENT '',
  `match_result` tinyint(4) DEFAULT '3' COMMENT '',
  `status` tinyint(4) DEFAULT '0' COMMENT '',
  `create_time` timestamp NULL DEFAULT '2018-06-28 08:00:00',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `gameid` int(11) DEFAULT '1',
  `is_cancel` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `STATUS_MATCHID` (`status`,`matchid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `incharge_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txid` varchar(256) DEFAULT NULL,
  `from_address` varchar(256) DEFAULT NULL,
  `to_address` varchar(256) DEFAULT NULL,
  `matchid` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT NULL COMMENT '',
  `create_time` timestamp NULL DEFAULT '2018-06-28 08:00:00',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `gameid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `f_t_s` (`from_address`,`to_address`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `match_prize_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `matchid` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL COMMENT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `prize_result` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(256) DEFAULT NULL COMMENT '',
  `value` int(11) DEFAULT NULL COMMENT '',
  `matchid` int(11) DEFAULT NULL COMMENT '',
  `sp` decimal(16,2) DEFAULT NULL COMMENT '',
  `txid` varchar(256) DEFAULT NULL COMMENT '',
  `status` tinyint(4) DEFAULT '1' COMMENT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `gameid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_s_a` (`matchid`,`status`,`address`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `transfer_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txid` varchar(256) DEFAULT NULL,
  `from_address` varchar(256) DEFAULT NULL,
  `to_address` varchar(256) DEFAULT NULL,
  `matchid` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT NULL COMMENT '',
  `create_time` timestamp NULL DEFAULT '2018-06-28 00:00:00',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `gameid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `games` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL COMMENT '',
  `status` tinyint(4) DEFAULT '0',
  `create_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

