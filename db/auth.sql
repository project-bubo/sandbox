-- Adminer 3.5.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `cms_roles` (`role`, `acl`, `acl_fingerprint`) VALUES
('admin',	NULL,	'');

INSERT INTO `cms_users` (`user_id`, `login`, `email`, `password`, `role`, `acl`, `acl_fingerprint`) VALUES
(1,	'admin',	'marek.juras@netstars.cz',	'90cbf40ccd7467507c0f2880d656c3f20cc6348a',	'admin',	NULL,	'asd'),
(2,	'member',	'jurasm2@gmail.com',	'90cbf40ccd7467507c0f2880d656c3f20cc6348a',	'member',	NULL,	'asd');

-- 2012-11-27 08:35:25