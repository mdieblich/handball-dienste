-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Erstellungszeit: 30. Apr 2022 um 22:11
-- Server-Version: 5.7.36
-- PHP-Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `dienstedienst`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `spiel`
--

DROP TABLE IF EXISTS `spiel`;
CREATE TABLE IF NOT EXISTS `spiel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nuliga_id` int(11) NOT NULL,
  `mannschaft` int(11) NOT NULL,
  `gegner` text NOT NULL,
  `heimspiel` tinyint(1) NOT NULL,
  `anwurf` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nuliga_id` (`nuliga_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `spiel`
--

INSERT INTO `spiel` (`id`, `nuliga_id`, `mannschaft`, `gegner`, `heimspiel`, `anwurf`) VALUES
(1, 1653, 3, 'Polizei SV Köln V', 1, '2022-03-27 15:00:00'),
(2, 101030, 2, 'HSV Frechen II', 1, '2022-03-05 18:00:00'),
(3, 112003, 1, 'ASV SR Aachen ', 0, '2022-03-05 00:00:00'),
(4, 99, 1, 'TSV Bayer Dormagen II', 1, NULL),
(5, 2059, 5, 'Dünnwalder TV', 0, '2022-02-20 23:30:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
