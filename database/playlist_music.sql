-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 09, 2025 at 02:28 AM
-- Server version: 10.6.21-MariaDB-cll-lve
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sibe5579_cloud_music`
--

-- --------------------------------------------------------

--
-- Table structure for table `playlist_music`
--

CREATE TABLE `playlist_music` (
  `id_playlist_music` int(5) NOT NULL,
  `id_music` int(5) NOT NULL,
  `id_playlist` int(5) NOT NULL,
  `date_add_music_playlist` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `playlist_music`
--

INSERT INTO `playlist_music` (`id_playlist_music`, `id_music`, `id_playlist`, `date_add_music_playlist`) VALUES
(45, 13529, 793, '2025-02-12 21:04:58'),
(46, 10707, 793, '2025-02-12 21:07:47'),
(47, 135, 793, '2025-02-12 21:08:31'),
(48, 218, 793, '2025-02-12 21:08:58'),
(49, 459, 793, '2025-02-12 21:09:18'),
(50, 8, 793, '2025-02-12 21:11:08'),
(56, 13534, 793, '2025-02-14 07:15:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `playlist_music`
--
ALTER TABLE `playlist_music`
  ADD PRIMARY KEY (`id_playlist_music`),
  ADD KEY `Delete Playlist Music` (`id_playlist`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `playlist_music`
--
ALTER TABLE `playlist_music`
  MODIFY `id_playlist_music` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `playlist_music`
--
ALTER TABLE `playlist_music`
  ADD CONSTRAINT `Delete Playlist Music` FOREIGN KEY (`id_playlist`) REFERENCES `playlist` (`uid`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
