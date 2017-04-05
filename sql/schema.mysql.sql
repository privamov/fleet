CREATE TABLE fleet_device (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `mac` varchar(255) DEFAULT NULL,
  `imei` bigint(20) DEFAULT NULL,
  `imsi` bigint(20) DEFAULT NULL,
  `nsce` bigint(20) DEFAULT NULL,
  `purchased` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `price` double DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `comments` text,
  PRIMARY KEY (`id`)
);

CREATE TABLE fleet_device_type (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturer` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE fleet_lending (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` int(11) DEFAULT NULL,
  `started` datetime DEFAULT NULL,
  `ended` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `segment` varchar(255) DEFAULT NULL,
  `comments` text,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
)
