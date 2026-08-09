-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Aug 09. 17:13
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `vizsgaremek_db`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `testtable`
--

CREATE TABLE `testtable` (
  `id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `testtable`
--

INSERT INTO `testtable` (`id`, `first_name`, `last_name`, `gender`) VALUES
(1, 'Nathalie', 'Manser', 'Female'),
(2, 'Eileen', 'Abels', 'Female'),
(3, 'Tomlin', 'Seedhouse', 'Male'),
(4, 'Yves', 'Illesley', 'Male'),
(5, 'Goldie', 'Birkby', 'Female'),
(6, 'Codee', 'Birks', 'Female'),
(7, 'Sybil', 'Wilmington', 'Female'),
(8, 'Minnie', 'Heatherington', 'Female'),
(9, 'Katharyn', 'Blazdell', 'Female'),
(10, 'Tiff', 'Vivyan', 'Agender');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
