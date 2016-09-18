SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `wpdemo` (
  `id` varchar(32) NOT NULL,
  `assigned` datetime DEFAULT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `wpdemo` (`id`, `assigned`, `used`) VALUES
('wpdemo_25a280a1b8a04bef', NULL, 0),
('wpdemo_ec0bd3aa92cc1400', NULL, 0);

CREATE TABLE `wp_options` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `wpdemo`
  ADD UNIQUE KEY `id` (`id`);
