-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 26, 2025 at 05:11 PM
-- Server version: 10.6.22-MariaDB-cll-lve
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sibe5579_myplant`
--

-- --------------------------------------------------------

--
-- Table structure for table `sensor_daily_avg`
--

CREATE TABLE `sensor_daily_avg` (
  `date` date NOT NULL,
  `avg_tds` float DEFAULT NULL,
  `avg_kelembaban` float DEFAULT NULL,
  `avg_suhu` float DEFAULT NULL,
  `avg_ph` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sensor_daily_avg`
--

INSERT INTO `sensor_daily_avg` (`date`, `avg_tds`, `avg_kelembaban`, `avg_suhu`, `avg_ph`) VALUES
('2025-05-31', 1245.42, 71.4542, 42.647, 13.6499),
('2025-06-01', 195.422, 21.4542, 12.647, 4.6499),
('2025-06-02', 295.422, 41.4542, 29.647, 7.6499),
('2025-06-03', 245.422, 31.4542, 19.647, 3.6499),
('2025-06-04', 495.422, 21.4542, 39.647, 12.6499),
('2025-06-05', 995.422, 51.4542, 29.647, 6.64993),
('2025-06-06', 914.727, 49.9654, 29.1462, 7.74615),
('2025-06-08', 914.727, 49.9654, 29.1462, 7.74615),
('2025-06-09', 914.727, 49.9654, 29.1462, 7.74615),
('2025-06-10', 914.727, 49.9654, 29.1462, 7.74615),
('2025-06-11', 1095.19, 44.1592, 29.534, 7.2696),
('2025-06-15', 115.784, 30.0911, 53.6766, 4.14785),
('2025-06-16', 0, 28.0729, 63.5256, 3.78571),
('2025-06-17', 0, 27.9746, 67.1618, 4.23342);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `timestamp` datetime NOT NULL,
  `tds` float DEFAULT NULL,
  `kelembaban` float DEFAULT NULL,
  `suhu` float DEFAULT NULL,
  `ph` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_hourly_avg`
--

CREATE TABLE `sensor_hourly_avg` (
  `hour` datetime NOT NULL,
  `avg_tds` float DEFAULT NULL,
  `avg_kelembaban` float DEFAULT NULL,
  `avg_suhu` float DEFAULT NULL,
  `avg_ph` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sensor_daily_avg`
--
ALTER TABLE `sensor_daily_avg`
  ADD PRIMARY KEY (`date`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sensor_hourly_avg`
--
ALTER TABLE `sensor_hourly_avg`
  ADD PRIMARY KEY (`hour`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2055;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
