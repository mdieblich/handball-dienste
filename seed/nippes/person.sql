-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Erstellungszeit: 30. Apr 2022 um 21:48
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
-- Tabellenstruktur für Tabelle `person`
--

DROP TABLE IF EXISTS `person`;
CREATE TABLE IF NOT EXISTS `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `email` varchar(256) DEFAULT NULL,
  `hauptmannschaft` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Hauptmannschaft` (`hauptmannschaft`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `person`
--

INSERT INTO `person` (`id`, `name`, `email`, `hauptmannschaft`) VALUES
(1, 'Micky Dottler', 'test_dottler@tknippes.de', 1),
(2, 'Mars Pranke-Schleimers', 'test_pranke@tknippes.de', 2),
(3, 'Christoph Gernweich', 'test_gernweich@tknippes.de', 3),
(5, 'Martini Klaulich', 'test_klaulich@tknippes.de', 1),
(6, 'Valeska Sechseulen', 'test_sechseulen@tknippes.de', 4),
(7, 'Johanna Wildefrau', 'test_wildefrau@tknippes.de', 5),
(8, 'Hilde Bergsteiger', 'test_bergsteiger@tknippes.de', 6);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
