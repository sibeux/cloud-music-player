-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 01, 2025 at 02:49 AM
-- Server version: 10.6.20-MariaDB-cll-lve
-- PHP Version: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sibe5579_sihalal`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`sibe5579`@`localhost` FUNCTION `Levenshtein` (`str1` VARCHAR(255), `str2` VARCHAR(255)) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE len1 INT DEFAULT LENGTH(str1);
    DECLARE len2 INT DEFAULT LENGTH(str2);
    DECLARE i INT;
    DECLARE j INT;
    DECLARE cost INT;
    DECLARE prev_row TEXT;
    DECLARE curr_row TEXT;
    DECLARE temp_row TEXT;
    DECLARE res INT;

    -- Initialize the first row (0, 1, 2, ..., len2)
    SET prev_row = '';
    SET i = 0;
    WHILE i <= len2 DO
        SET prev_row = CONCAT(prev_row, ',', i);
        SET i = i + 1;
    END WHILE;

    -- Iterate over each character of str1
    SET i = 1;
    WHILE i <= len1 DO
        SET curr_row = i; -- First column in the current row
        SET j = 1;
        WHILE j <= len2 DO
            SET cost = IF(SUBSTRING(str1, i, 1) = SUBSTRING(str2, j, 1), 0, 1);
            SET curr_row = CONCAT(curr_row, ',', LEAST(
                SUBSTRING_INDEX(prev_row, ',', j) + 1,    -- Deletion
                SUBSTRING_INDEX(curr_row, ',', -1) + 1,  -- Insertion
                SUBSTRING_INDEX(prev_row, ',', j - 1) + cost -- Substitution
            ));
            SET j = j + 1;
        END WHILE;
        SET prev_row = curr_row;
        SET i = i + 1;
    END WHILE;

    -- Get the last value in the last row
    SET res = SUBSTRING_INDEX(curr_row, ',', -1);
    RETURN res;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `alamat`
--

CREATE TABLE `alamat` (
  `id_alamat` int(5) NOT NULL,
  `id_user` mediumint(5) DEFAULT NULL,
  `nama_penerima` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_penerima` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label_alamat` enum('primary','office') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'primary',
  `provinsi` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_provinsi` tinyint(5) NOT NULL,
  `kota` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_kota` tinyint(5) NOT NULL,
  `kode_pos` tinytext NOT NULL,
  `detail_alamat` mediumtext DEFAULT NULL,
  `jalan_alamat` mediumtext NOT NULL,
  `pinpoint_alamat` mediumtext DEFAULT NULL,
  `is_utama` enum('true','false') NOT NULL,
  `is_toko` enum('true','false') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `alamat`
--

INSERT INTO `alamat` (`id_alamat`, `id_user`, `nama_penerima`, `nomor_penerima`, `label_alamat`, `provinsi`, `id_provinsi`, `kota`, `id_kota`, `kode_pos`, `detail_alamat`, `jalan_alamat`, `pinpoint_alamat`, `is_utama`, `is_toko`) VALUES
(39, 23, 'awas', '0812121221', 'office', 'Jawa Timur', 11, 'Kab. Jember', 127, '68113', 'Jawa Timur\nKab. Jember\n68113', '123', 'LatLng(-8.164723199437482, 113.68636585772038)', 'false', 'false'),
(41, 23, 'Bagas', '08965136212366', 'office', 'DKI Jakarta', 6, 'Kota Jakarta Selatan', 127, '12230', 'DKI Jakarta\nKota Jakarta Selatan\n12230', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan', 'LatLng(-8.124360517876784, 113.73067561537027)', 'false', 'false'),
(49, 28, 'Brian Tan William', '0895412225866', 'office', 'Jawa Timur', 11, 'Kab. Blitar', 74, '66171', 'Jawa Timur\nKab. Blitar\n66171', 'Jl. Mawar', 'LatLng(-8.021683661842639, 112.00679272413254)', 'true', 'true'),
(50, 29, 'Chandra Bintang Wijaya', '0895486952366', 'primary', 'Jawa Timur', 11, 'Kota Surabaya', 127, '60119', 'Jawa Timur\nKota Surabaya\n60119', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234', 'LatLng(-8.01230899518247, 112.00445953756571)', 'true', 'true'),
(51, 32, 'jafar', '089509233166', 'primary', 'Jawa Timur', 11, 'Kab. Blitar', 74, '66171', 'Jawa Timur\nKab. Blitar\n66171', 'tunjung', 'LatLng(-8.0216685, 112.006783)', 'true', 'true'),
(52, 23, 'Yosi', '08956325685', 'office', 'Bangka Belitung', 2, 'Kab. Bangka Tengah', 30, '33613', 'Bangka Belitung\nKab. Bangka Tengah\n33613', 'Jl. mawar', 'LatLng(-8.176895624876492, 113.72011844068766)', 'false', 'false'),
(53, 23, 'M Nasrul Wahabi', '08954286889', 'office', 'DKI Jakarta', 6, 'Kota Jakarta Timur', 127, '13330', 'DKI Jakarta\nKota Jakarta Timur\n13330', 'Jl. mawar', 'LatLng(-8.163113257368407, 113.72729703783989)', 'true', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `favorite`
--

CREATE TABLE `favorite` (
  `id_favorite` int(5) NOT NULL,
  `id_produk` int(5) NOT NULL,
  `id_user` mediumint(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `favorite`
--

INSERT INTO `favorite` (`id_favorite`, `id_produk`, `id_user`) VALUES
(34, 53, 28),
(35, 48, 28),
(36, 75, 23),
(40, 47, 28),
(41, 50, 28),
(43, 73, 28),
(51, 90, 28),
(52, 91, 28),
(54, 88, 23),
(57, 90, 23);

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` mediumint(11) NOT NULL,
  `id_produk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_user`, `id_produk`) VALUES
(106, 28, 89),
(107, 28, 88),
(108, 28, 87),
(110, 28, 84),
(111, 28, 86),
(112, 28, 83),
(122, 23, 88),
(125, 23, 91);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(5) NOT NULL,
  `no_pesanan` mediumtext NOT NULL,
  `id_user` mediumint(5) DEFAULT NULL,
  `id_produk` mediumint(5) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `pengiriman` tinytext NOT NULL,
  `nama_no_penerima` text NOT NULL,
  `alamat_penerima` text NOT NULL,
  `subtotal_harga_barang` int(11) NOT NULL,
  `subtotal_pengiriman` int(11) NOT NULL,
  `total_pembayaran` int(11) NOT NULL,
  `tanggal_pesanan` mediumtext DEFAULT NULL,
  `status_pesanan` enum('tunggu','proses','kirim','selesai','batal','batal_toko','ulas') NOT NULL DEFAULT 'tunggu'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `no_pesanan`, `id_user`, `id_produk`, `jumlah`, `pengiriman`, `nama_no_penerima`, `alamat_penerima`, `subtotal_harga_barang`, `subtotal_pengiriman`, `total_pembayaran`, `tanggal_pesanan`, `status_pesanan`) VALUES
(11, 'SHL/20241120/2850/124809f3f9', 28, 50, 4, 'pos', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 70000, 80000, 150000, '2024-11-20 12:48:09.125326', 'batal'),
(15, 'SHL/20241120/2858/165745fccf', 28, 58, 2, 'jnt', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 10200, 20000, 30200, '2024-11-20 16:57:45.830306', 'batal'),
(17, 'SHL/20241120/2855/1706118878', 28, 55, 5, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 520000, 100000, 620000, '2024-11-20 17:06:11.555786', 'batal'),
(20, 'SHL/20241122/2858/0730014302', 28, 58, 2, 'jnt', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 10200, 20000, 30200, '2024-11-22 07:30:01.018219', 'batal'),
(21, 'SHL/20241122/2851/073652d602', 28, 51, 6, 'jnt', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 119400, 120000, 239400, '2024-11-22 07:36:52.442507', 'batal'),
(22, 'SHL/20241122/2857/0747499338', 28, 57, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 142900, 80000, 222900, '2024-11-22 07:47:49.901017', 'batal'),
(23, 'SHL/20241122/2854/075119a9e1', 28, 54, 3, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 119700, 40000, 159700, '2024-11-22 07:51:19.836843', 'batal'),
(24, 'SHL/20241122/2856/0757506887', 28, 56, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 1489, 20000, 21489, '2024-11-22 07:57:50.493407', 'batal'),
(25, 'SHL/20241122/2849/155143080f', 28, 49, 9, 'pos', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 380655, 60000, 440655, '2024-11-22 15:51:43.175694', 'batal_toko'),
(26, 'SHL/20241123/2368/0758016c85', 23, 68, 4, 'jnt', 'awas | (+62) 0812121221', '123, Kab. Jember, Jawa Timur, 68113', 56000, 80000, 136000, '2024-11-23 07:58:01.798358', 'batal_toko'),
(27, 'SHL/20241123/2852/0838001a82', 28, 52, 2, 'jnt', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 18000, 20000, 38000, '2024-11-23 08:38:00.523846', 'batal_toko'),
(28, 'SHL/20241123/2368/08462201bc', 23, 68, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 14000, 20000, 34000, '2024-11-23 08:46:22.452040', 'batal_toko'),
(29, 'SHL/20241123/2368/0846411295', 23, 68, 2, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 28000, 40000, 68000, '2024-11-23 08:46:41.225031', 'batal_toko'),
(30, 'SHL/20241123/2855/113517341e', 28, 55, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 104000, 20000, 124000, '2024-11-23 11:35:17.017827', 'batal'),
(32, 'SHL/20241123/2858/133037fce8', 28, 58, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 5100, 20000, 25100, '2024-11-23 13:30:37.865785', 'ulas'),
(34, 'SHL/20241124/2372/17085642be', 23, 72, 2, 'pos', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 62000, 20000, 82000, '2024-11-24 17:08:56.514818', 'ulas'),
(35, 'SHL/20241124/2972/204756ae7e', 29, 72, 3, 'jnt', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jalan Gatot Subroto No. Kav. 52-53, Kelurahan Kuningan Timur, Kecamatan Setiabudi, Jakarta Selatan, DKI Jakarta, 12950, Kota Jakarta Selatan, DKI Jakarta, 12230', 93000, 20000, 113000, '2024-11-24 20:47:56.454931', 'ulas'),
(36, 'SHL/20241125/2952/164710a79b', 29, 52, 11, 'jnt', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234, Kota Surabaya, Jawa Timur, 60119', 99000, 100000, 199000, '2024-11-25 16:47:10.319363', 'ulas'),
(37, 'SHL/20241126/2371/0005384aaf', 23, 71, 7, 'jnt', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 735000, 700000, 1435000, '2024-11-26 00:05:38.771179', 'ulas'),
(38, 'SHL/20241127/2372/141502258c', 23, 72, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 31000, 20000, 51000, '2024-11-27 14:15:02.690501', 'batal'),
(39, 'SHL/20241127/2372/1415151d0c', 23, 72, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 31000, 20000, 51000, '2024-11-27 14:15:15.508348', 'batal'),
(40, 'SHL/20241201/2858/135522714f', 28, 58, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 5100, 20000, 25100, '2024-12-01 13:55:22.115831', 'ulas'),
(41, 'SHL/20241204/2858/1826292b30', 28, 58, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 5100, 20000, 25100, '2024-12-04 18:26:29.265505', 'batal'),
(42, 'SHL/20241204/2848/183244eabe', 28, 48, 5, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 60000, 40000, 100000, '2024-12-04 18:32:44.066928', 'ulas'),
(43, 'SHL/20241204/2850/1958488fa0', 28, 50, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 17500, 20000, 37500, '2024-12-04 19:58:48.695738', 'batal'),
(44, 'SHL/20241205/2391/0347413c85', 23, 91, 5, 'jnt', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 51000, 20000, 71000, '2024-12-05 03:47:41.669943', 'selesai'),
(45, 'SHL/20241209/2891/232809586c', 28, 91, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 10200, 20000, 30200, '2024-12-09 23:28:09.957066', 'batal'),
(46, 'SHL/20241210/2891/1853299c4f', 28, 91, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 10200, 20000, 30200, '2024-12-10 18:53:29.935591', 'batal'),
(47, 'SHL/20241210/2891/224956e33b', 28, 91, 1, 'jne', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 10200, 20000, 30200, '2024-12-10 22:49:56.673601', 'ulas'),
(48, 'SHL/20241210/2957/233613f930', 29, 57, 1, 'jne', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234, Kota Surabaya, Jawa Timur, 60119', 142900, 80000, 222900, '2024-12-10 23:36:13.795854', 'ulas'),
(49, 'SHL/20241212/2890/1437239126', 28, 90, 5, 'jnt', 'Akmal Satria Kadhafi | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 230775, 40000, 270775, '2024-12-12 14:37:23.775343', 'selesai'),
(50, 'SHL/20241216/2390/1945415d98', 23, 90, 2, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 92310, 20000, 112310, '2024-12-16 19:45:41.597925', 'batal'),
(51, 'SHL/20241216/2391/2014040a6f', 23, 91, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 10200, 20000, 30200, '2024-12-16 20:14:04.409266', 'batal'),
(52, 'SHL/20241216/2391/201424016b', 23, 91, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 10200, 20000, 30200, '2024-12-16 20:14:24.886488', 'ulas'),
(53, 'SHL/20241216/2950/20155121d4', 29, 50, 1, 'jne', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234, Kota Surabaya, Jawa Timur, 60119', 17500, 20000, 37500, '2024-12-16 20:15:51.265179', 'batal_toko'),
(54, 'SHL/20241216/2971/201558acf7', 29, 71, 1, 'jne', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234, Kota Surabaya, Jawa Timur, 60119', 105000, 100000, 205000, '2024-12-16 20:15:58.866703', 'batal_toko'),
(55, 'SHL/20250111/2884/1503254c7a', 28, 84, 1, 'jne', 'Brian Tan William | (+62) 0895412225866', 'Jl. Mawar, Kab. Blitar, Jawa Timur, 66171', 127000, 60000, 187000, '2025-01-11 15:03:25.919295', 'batal'),
(56, 'SHL/20250112/2391/20093015a6', 23, 91, 1, 'jne', 'Bagas | (+62) 0896513621235', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 10200, 20000, 30200, '2025-01-12 20:09:30.669125', 'batal'),
(57, 'SHL/20250112/2391/222102d56c', 23, 91, 1, 'jne', 'Bagas | (+62) 0896513621236', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 10200, 20000, 30200, '2025-01-12 22:21:02.127805', 'ulas'),
(58, 'SHL/20250113/2391/2027122661', 23, 91, 1, 'jne', 'Bagas | (+62) 0896513621236', 'Jl. Keramat Jati, Dukuh Atas, Jakarta Selatan, Kota Jakarta Selatan, DKI Jakarta, 12230', 10200, 20000, 30200, '2025-01-13 20:27:12.680730', 'batal'),
(59, 'SHL/20250113/2389/232801ea0e', 23, 89, 1, 'jne', 'M Nasrul Wahabi | (+62) 08954286889', 'Jl. mawar, Kota Jakarta Timur, DKI Jakarta, 13330', 8500, 20000, 28500, '2025-01-13 23:28:01.212930', 'kirim'),
(60, 'SHL/20250117/2394/153303f628', 23, 94, 1, 'jne', 'M Nasrul Wahabi | (+62) 08954286889', 'Jl. mawar, Kota Jakarta Timur, DKI Jakarta, 13330', 2500, 20000, 22500, '2025-01-17 15:33:03.499386', 'kirim'),
(61, 'SHL/20250121/2968/2014056db6', 29, 68, 1, 'jne', 'Chandra Bintang Wijaya | (+62) 0895486952366', 'Jl. Ahmad Yani No.123, Surabaya, Jawa Timur 60234, Kota Surabaya, Jawa Timur, 60119', 14000, 20000, 34000, '2025-01-21 20:14:05.780637', 'batal');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(5) NOT NULL,
  `id_user` mediumint(5) DEFAULT NULL,
  `id_shhalal` int(5) DEFAULT NULL,
  `nama_produk` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi_produk` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_produk_1` varchar(200) NOT NULL,
  `foto_produk_2` varchar(200) DEFAULT NULL,
  `foto_produk_3` varchar(200) DEFAULT NULL,
  `harga_produk` int(10) NOT NULL,
  `berat_produk` mediumint(5) NOT NULL,
  `stok_produk` int(10) NOT NULL,
  `is_ditampilkan` enum('true','false') NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_user`, `id_shhalal`, `nama_produk`, `deskripsi_produk`, `foto_produk_1`, `foto_produk_2`, `foto_produk_3`, `harga_produk`, `berat_produk`, `stok_produk`, `is_ditampilkan`) VALUES
(47, 23, 158, 'BOGASARI Tepung Terigu Segitiga Biru Premium 1 kg', 'BOGASARI Tepung Terigu Segitiga Biru Premium 1 kg\nNetto : 1,000 gr\n\nSegitiga Biru adalah terigu serbaguna protein sedang, yang cocok untuk membuat berbagai jenis makanan seperti bolu, brownies, banana cake, martabak manis, kue bulan, dan lain-lain. Segitiga Biru diciptakan untuk menjadi terigu yang fleksibel dan mudah untuk digunakan, terutama untuk penggunaan di dapur rumah tangga.', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110223646_6WD9.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110223646_5T1W.jpg', '', 13500, 1000, 50, 'true'),
(48, 23, 209, 'INDOMILK SUSU KENTAL MANIS 370 GRAM', 'indomilk susu kental manis 370 gram', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110230619_CIGA.jpg', '', '', 12000, 370, 0, 'true'),
(49, 23, 304, 'Tenderloin/Has dalam Daging Sapi', 'Tenderloin adalah daging sapi bagian atas tengah badan. Tenderloin memiliki tekstur daging yang lebih lembut / daerah ini adalah bagian yang paling lunak, karena otot-otot di bagian ini jarang dipakai untuk beraktivitas. \r\nRekomendasi masakan: Steak, Roll, Tumis\r\n- Fresh, kualitas diutamakan\r\n- Dikemas higienis dan sesuai standart kualitas\r\n- Halal dan berkualitas \r\n\r\nPengiriman :\r\nPemesanan sebelum jam 4 sore di kirim di Hari yang sama \r\nStok Selalu ready\r\n \r\n#tenderloin #tenderloinsby #tenderloinsapi #tenderloin500gr', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110231402_71GD.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110231402_SNNZ.jpg', '', 42295, 250, 43, 'true'),
(50, 23, 841, 'MINYAK GORENG SUNCO 1 LITER', 'minyak goreng sunco 1 liter\r\n\r\n', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110231917_AJTO.jpg', '', '', 17500, 1000, 109, 'true'),
(51, 23, 1142, 'Sedani Makaroni Pipa 1 Kg – Elbow Pasta Macaroni 1Kg', 'SEDANI Makaroni adalah makaroni yang diproduksi oleh Bogasari dengan harga yang ekonomis. Terbuat dari gandum semolina dengan aneka bentuk.\r\n\r\nCocok untuk aneka menu pasta, macaroni schotel, salad, sup dan lainnya.\r\n\r\n', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110233112_HGIM.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110233112_AUOW.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241110233112_96AR.jpg', 19900, 1000, 37, 'true'),
(52, 23, 1186, 'Tempe 1 Papan Daun Pisang Makanan Sayuran Jakarta', '*(Berat Tempe : 400 Gram)\r\nTEMPE\r\n~Informasi produk :  Tempe merupakan masak khas Indonesia, berbahan dasar kacang kedelai yang telah difermentasi oleh microorganisme, memiliki tekstur kering, dan kenyal.\r\n~Manfaat dan Nutrisi : Tempe mengandung banyak nutrisi baik yang dibutuhkan tubuh, seperti protein tinggi, dan rendah kandungan lemak.\r\n~Cara penyimpanan : Masukan kedalam plastik dan simpan di kulkas\r\n\r\nJadwal Pemesanan :\r\n- Pukul 05.00 - 19.00 (Dikirim H+1)\r\n-Pukul 19.00 - 24.00 (Dikirim H+2, kecuali jika produk ready akan dikirim H+ 1)\r\nPengiriman : \r\n- Jam pengiriman pukul 07.00 - 17.00\r\n- Jam pengiriman diatas pukul 17.00 silahkan request melalui chat, jika memungkinkan akan dikirim sesuai request.\r\n\r\n*Packing Rapih dan Aman\r\n*Free gift untuk orderan diatas 300.000\r\n\r\nNOTE : \r\n* RESIKO BARANG LAYU, ATAU ADA BUSUK DILUAR TANGGUNG JAWAB KAMI UNTUK LUAR KOTA\r\n* MEMBELI BERARTI MENYETUJUI ATURAN KAMI, JIKA STOCK KOSONG, DANA AKAN DIKEMBALIKAN ATAU DIKIRIM DI HARI SELANJUTNYA\r\n\r\nLARIS SEDOYO \" Pusat Belanja Kebutuhan Dapur Anda\"\r\nHappy Shopphing!\r\n\r\n#sayur #sayuran #Jakarta #Jakartabarat #Jakartatimur #Jakartautara #Jakartaselatan #Jakartapusat #Tangerang', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111001039_OU86.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111001039_PE5C.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111001039_860J.jpg', 9000, 400, 3552, 'true'),
(53, 23, 1269, 'Za\'atar Spice Blend - Mediterania Series - Bumbu Arab', 'A touch of the Middle East and the Mediterranean awaits! Our Zesty Za’atar exhibits woody and floral aromatics, and is perfect topped onto bread, dips, salads and avocado toast. Freely season on any meat, veggies, seafood and many more.\r\n\r\nIngredients: \r\nSumac, Sesame Seed, Cumin, Coriander, Thyme, Sea Salt, Red Pepper\r\n\r\nNatural Ingredients \r\nNo MSG \r\nNo Preservatives \r\nVegan \r\nGluten-Free \r\nHalal', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111003403_7QGW.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111003403_E1K5.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111003403_4TGK.jpg', 55000, 200, 30, 'true'),
(54, 23, 1287, 'Sasa santan cair 65ml × 10pcs\r\n\r\n', 'harga untuk per 10pcs ya exp aman barang di jamin ori', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111011847_CYY0.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111011847_H96H.jpg', '', 39900, 650, 5876, 'true'),
(55, 23, 1600, 'Syrup Pandan Delifru 1 Liter - Sirup Daun Pandan Premium', 'Diekstrak dari daun pandan yang paling harum, Sirup Pandan Delifru memberikan nuansa yang lembut dan menenangkan pada campuran minuman Anda. Dengan cita rasa yang cocok dikreasikan menjadi berbagai jenis minuman, Sirup Pandan Delifru menjadi salah satu sirup favorit bagi banyak orang.\n\nSeluruh produk kami menggunakan 100% gula murni & ekstrak buah asli, serta sudah mendapatkan sertifikat Halal, BPOM & PIRT sehingga terjamin mutunya.', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111012944_48IO.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111012944_LE4U.jpg', '', 104000, 1000, 1014, 'true'),
(56, 23, 1437, 'Garam Dapur Murni Beryodium Cap Katak 200 gr / GARAM CAP KATAK BERYODIUM 200 gr / GARAM KONSUMSI CAP KATAK', 'Barang kami Ready sesuai di etalase ya kak Dan akan kami kirim secepatnya 🙏🏻😊\r\n\r\nInformasi produk: \r\nGaram Konsumsi Beryodium ukuran 200 gram \r\nGARAM KECIL = 150 GRAM\r\n*harga tertera untuk 1 pcs*\r\n\r\nMOHON DIPERHATIKAN!!!\r\n1. Semua barang yg ada di toko ini ready sesuai stok ya kak\r\n2. Kami pastikan semua barang yg kami kirim dalam keadaan baik dan kami proses sesuai pesanan dan variasi:)\r\n3. Packing box kami lipat ya kak mengindari penyok saat dalam perjalanan \r\n4. Mohon Lakukan  Video Unboxing Saat Paket Diterima, jika ada kekurangan barang ,harap foto Label pengiriman dan Melampirkan video unboxing , karena kesalahan bukan kami sengaja kak tapi murni human eror :)\r\n5. Cek dahulu sebelum Checkout karena pihak penjual tidak bisa mengganti Nama, Nomor, Alamat, atau Barang setelah Checkout \r\n6. Mohon cek barang langsung setelah barang sampai. Komplain kami terima apabila ada bukti video dan akan Kami cek berdasarkan tanggal dan jam di resi pengiriman.\r\n7. Kerusakan barang, retur dll. Akan dicek dulu kasusnya. Tentunya kami akan memberikan solusi terbaik buat Customer kami ya kak:)\r\n8.Kalau dropsip tuliskan nama DROPSHIP di fitur dropship pada saat cek out\r\n9. Order yg sudah kami proses tidak bisa dibatalkan\r\n10. Hari minggu tidak ada pengiriman karena toko libur, jadi harap dimaklumi jika slow respon dalam chat ya kak:)\r\n\r\nTERIMA KASIH , SEMOGA SENANG BERBELANJA DI TOKO KAMI 😊🙏🏻', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111013343_3K15.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111013343_VGFY.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111013343_LL0N.jpg', 1489, 200, 9555, 'true'),
(57, 23, 1483, 'ABC Saus Sambal Asli 135 ml - Multipack 24 pcs\r\r', 'ABC Sambal Asli 135 ml isi 24 pcs, paling hemat!\r\n\r\nABC Sambal Asli, Sambal No 1 di Indonesia, adalah pilihan utama bagi pecinta pedas yang menghargai kualitas dan keaslian rasa. Terbuat dari Cabai Hiyung berkualitas yang menjadi kunci rahasia di tiap Sambal ABC favorit keluarga Indonesia dan tanpa tambahan pewarna, sambal ini dihasilkan melalui proses penggilingan tradisional untuk menghadirkan rasa pedas yang maksimal dan alami.\r\n\r\nSambal ABC adalah pelengkap sempurna untuk hidangan favorit Anda. Seperti Jalangkote khas Makasar, Bakso Malang, atau Bakwan goreng. sambal ini akan menambahkan sentuhan pedas yang tak terlupakan pada hidangan Anda.\r\n\r\nKualitas yang terjaga dan citarasa yang otentik membuat Sambal asli ABC menjadi pilihan yang tepat untuk meningkatkan pengalaman kuliner Anda.\r\nSambal ABC, Sambal Asli Indonesia Asli Pedasnya!\r\n\r\nKomposisi:\r\nCabai (36%), Air, Gula, Garam, Bawang putih (3%), Penstabil nabati, Pengatur keasaman, Pengawet (natrium benzoat dan natrium metabisulfit), Penguat rasa (mononatrium glutamat dan inosinat guanilat), Pemanis alami glikosida steviol. \r\n\r\nKeunggulan:\r\n- Terbuat dari Cabai Hiung yang otentik dan berkualitas dari desa Tapin Kalimantan, kemudian dipetik hingga dikemas maksimal 51 jam. \r\n- Lebih pedas dari saus sambal lain.\r\n- Tanpa bahan pewarna makanan buatan. \r\n- Sambal No. 1 Pilihan Keluarga Indonesia.\r\n- Sambal asli dengan rasa yang otentik.\r\n\r\nMau masakan favorit keluarga Anda makin mantap pedasnya? Ayo segera beli dan gunakan ABC Sambal Asli Indonesia!\r\n\r\nJaminan Keamanan Makanan:\r\nHalal No.ID00410000054900720 \r\nBPOM No.MD 222871000500373', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111014112_5FQS.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111014112_8HCQ.jpg', '', 142900, 3240, 43, 'true'),
(58, 23, 1549, 'Kraft Keju Cheddar Regular 30g, kemasan kecil untuk memasak kue dan burger', '� Keju Kraft Cheddar adalah keju cheddar olahan dengan bahan utama keju asli New Zealand\r\n� Mengandung Calcimilk: Kaya akan Kalsium, Sumber Protein dan Vitamin D \r\n� Memiliki rasa gurih keju yang lezat tanpa perisa tambahan\r\n� Mudah di parut dan hasil panggangan kuning keemasan cocok untuk berbagai aplikasi\r\n� Halal', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111014739_8ON2.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111014739_5RFB.jpg', '', 5100, 30, 51, 'true'),
(59, 23, 1564, 'Gulaku Gula Tebu (Putih) Premium 1000G', 'Gulaku premium adalah gula pasir putih produksi nasional\r\nyang berkualitas lebih putih dan lebih jernih, serta diproduksi dari tebu alami langsung dari perkebunan. Gulaku diproduksi dari tebu segar bermutu baik dari perkebunan kami di Lampung.', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111064910_HZL2.jpg', 'https://sibeux.my.id/project/sihalal/uploads/23_IMG_20241111064910_W703.jpg', '', 20900, 1000, 492, 'true'),
(68, 28, 158, 'Tepung Terigu KUNCI BIRU PREMIUM 1Kg [ Protein Rendah ]', 'Cocok untuk aneka cake, bolu dan kue kering', 'https://sibeux.my.id/project/sihalal/uploads/28_IMG_20241123075437_4NHP.jpg', 'https://sibeux.my.id/project/sihalal/uploads/28_IMG_20241123075437_VI1B.jpg', 'https://sibeux.my.id/project/sihalal/uploads/28_IMG_20241123075437_H521.jpg', 14000, 1000, 83, 'true'),
(71, 28, 838, 'Minyak Goreng Sawit 5L | Rose Brand', 'Minyak Goreng Sawit 5L\r\n\r\nRose Brand\r\nTerbuat dari kelapa sawit pilihan yang telah melalui proses pengolahan yang higienis sehingga menghasilkan minyak jernih dan berkualitas. Mengandung BETA karoten, omega 9 , dan vitamin A yang baik bagi asupan minyak sayur dalam tubuh\r\n\r\nMinyak Goreng Tawon cocok untuk memasak, menggoreng, ataupun menumis agar masakan menjadi lebih gurih, renyah, dan lezat\r\n\r\nED 24 Januari 2026\r\nIsi 5L\r\nKemasan Jirigen\r\n\r\nBPOM RI MD 208113008251\r\nHALAL INDONESIA\r\nSNI\r\n\r\nPengiriman Instant maksimal 4 jirigen\r\n\r\n*Silahkan menambahkan BUBBLE WRAP / KARDUS untuk extra keamanan \r\n*Seluruh pesanan sebelum dipacking telah kami cek terlebih dahulu bocor atau tidak\r\n*Pembeli mengetahui bahwa produk ini adalah produk cairan yang dapat bocor saat perjalanan\r\n*Paket yang sudah diserahkan ke pihak ekspedisi dan kurir akan menjadi tanggungjawab pihak bersangkutan\r\n\r\nTerima Kasih telah berbelanja di toko kami\r\n\r\nHappy Shopping!', 'https://sibeux.my.id/project/sihalal/uploads/IMG_295d7608-3d2c-49d3-884e-a752a7708a77.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_e3eb2ac9-ac3c-407a-b956-65be4358543c.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_6ba0b0ee-025f-447a-90d9-063377cd72c7.jpg', 105000, 5000, 148, 'true'),
(72, 28, 1251, 'Bumbu Gado-Gado Boplo 140gr', 'Bumbu Gado-Gado Boplo yang mudah dan cepat untuk dimakan dimanapun Anda berada.\r\n\r\nKelezatan bumbu kami sudah teruji selama 50 tahun dikonsumsi oleh Pelanggan Restoran Gado-Gado Boplo, kini Anda dapat nikmati dimanapun  dan kapanpun. \r\n\r\nPerpaduan kacang tanah dan KACANG MEDE yang berkualitas  menghasilkan rasanya yang Khas, Legit dan Nikmat akan membuat Anda tidak ingin berhenti menikmatinya.\r\n\r\nHARAP TANYAKAN KETERSEDIAAN PRODUK TERLEBIH DAHULU\r\n\r\nPRODUK LAINNYA :\r\n- Ayam Kuning Boplo\r\n- Empal Boplo\r\n- Rendang Daging Boplo\r\n- Hamper Boplo untuk berbagai event (ulang tahun, natal/imlek/idul fitri, one month old/sebulanan, congratulation, surprise dll) isinya lengkap: bumbu gado-gado, ayam kuning, empal, rendang dan sambal\r\n\r\nBatas order jam 14.00, lewat dari jam tersebut diproses hari berikutnya. \r\nJam operasional toko:\r\nSenin-Minggu jam 9.00-16.00\r\nDi luar jam tersebut & tanggal merah,  Mohon Maaf Slow Respon', 'https://sibeux.my.id/project/sihalal/uploads/IMG_b9f1f184-5bfd-4c3b-bc76-57fed8ad32c5.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_6ebcfe94-e9b4-45f7-ac81-5c917e886674.jpg', '', 31000, 140, 52, 'true'),
(73, 29, 1235, 'Tahu Lombok 2 Bungkus isi 10 pcs - Dewi Sri Mart\r\r', 'Selamat Datang di Dewi Sri Mart!\r\nPenuhi kebutuhan dapur Anda dengan berbelanja di sini.\r\nBelanja praktis, pesan hari ini bisa langsung diantar!\r\nHarga murah kualitas Premium!\r\n\r\nDESKRIPSI PRODUK\r\nTahu Lombok 2 Bungkus isi 12 pcs - Dewi Sri Mart\r\n\r\nDESKRIPSI PENGIRIMAN - (PENTING!)\r\nPesanan hari ini 06:00 - 20.00 dikirim hari yg sama .\r\nmenggunakan jasa kirim Gosend Instant & Grabsend Instant\r\n\r\nStart pengiriman setiap harinya pk. 06:00 - 20.00 (mengikuti antrian dan sistem pengantaran)\r\n\r\nKami menjamin kesegaran produk kami terutama Sayur yang kami kirim setiap harinya, sehingga, sayur yang baru dipanen akan dikirim setiap harinya waktu dini hari. Setelah datang, kami bersihkan dan siapkan untuk dikirimkan ke pelanggan. \r\n\r\nSelamat Berbelanja dan Selamat Menikmati.', 'https://sibeux.my.id/project/sihalal/uploads/IMG_3dcf4c6c-0e1a-4bc3-9a4a-41ec33de9353.jpg', '', '', 17500, 128, 500, 'true'),
(74, 29, 1187, 'Tahu Yungfu 500 Gram', 'Ready setiap tahu Pong Yungfu dengan kemasan pack berat 500 gr. Dalam kemasan pack isi 4 psc tahu. Ready tahu putih dan tahu kuning. Setiap hari datang produksi baru dari pabrik. Dimohon konfirmasi terlebih dahulu terkait good stok produksi baru.', 'https://sibeux.my.id/project/sihalal/uploads/IMG_c2f4ed0b-0304-4528-8d97-3756ab29298c.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_f183c695-dac5-48be-8016-1ae4df101209.jpg', '', 18850, 500, 563, 'true'),
(75, 29, 1197, 'Tahu Putih Jombang Ekonomis', '* Mohon diperhatikan *\r\n\r\n1. Keterangan Produk :\r\n\r\n- Tahu Putih Jombang Ekonomis\r\n\r\n2. Info pemesanan dan pengiriman :\r\n\r\n- Pesanan yg masuk 07.00 - 14.00 akan diproses hari itu juga\r\n- pesanan lewat jam 14.00 akan di proses hari berikutnya\r\n- Waktu pengiriman mulai pukul 08.00 - 16.00\r\n- Utk pengiriman diproses berdasarkan antrian order dan itu merupakan hak penjual\r\n- Utk keperluan mendadak silahkan tanya terlebih dahulu, jika stock tersedia dan kondisi memungkinkan akan diproses di hari yg sama\r\n- Jika stock kosong akan diberlakukan sistem refund, ganti produk lain, kembali tunai dlm paket, mundur waktu pengiriman (tergantung kesepakatan bersama)\r\n- Utk memudahkan pencarian produk silahkan klik Etalase\r\n\r\n3. Kurir :\r\n- Gojek dan Grab (area Surabaya, Gresik, Sidoarjo)\r\n- Untuk luar Surabaya harap pilih expedisi dengan bijak ya \r\n- Penggunaan selain instan kurir, kami tdk bertanggung jawab jika terjadi kerusakan produk\r\n\r\n4. Aturan komplain :\r\n- Komplain akan diterima max 3jam stlh produk diterima dgn mengunggah foto atau video sbg bukti, lewat dari waktu tersebut komplain tdk kami respon\r\n- Komplain diberlakukan jika produk busuk atau layu yg terlalu parah\r\n\r\n5. Feel free to ask at chat or discussion', 'https://sibeux.my.id/project/sihalal/uploads/IMG_75dc29ee-31c1-4da9-bed6-d250cd80cb3d.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_80c71e91-ecb1-47ad-be19-d9a9c56172d1.jpg', '', 5000, 100, 5966, 'true'),
(76, 29, 1432, 'Javaland Syrup / Sirup All Varian 620ml - Premium', 'HARAP CO DUS \r\nSirup JavalandPremium,  cocok untuk Campuran berbagai jenis minuman dan kopi.\r\n\r\nKami adalah Supplier resmi produk Javaland yang merupakan produk bubuk minuman dan perlengkapan usaha *terlaris di Indonesia sejak tahun 2013*.\r\nProduk yang kami jual sudah bersertifikasi *BPOM* dan *Halal*\r\n\r\nSpesifikasi Produk:\r\n• Isi 620ml\r\n• Kemasan Botol Plastik', 'https://sibeux.my.id/project/sihalal/uploads/IMG_0edc24b3-a37e-4517-8d2b-37ad4177a10f.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_bcaaae7d-2ab0-423c-b98e-6b7108fef809.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_36b234b9-740c-4912-8205-634be03e4a0a.jpg', 60000, 620, 96, 'true'),
(78, 29, 1564, 'Gulaku Kuning Gula Pasir Tebu 1kg', 'Gulaku Kuning 1kg\r\n\r\nPengiriman setiap hari senin - sabtu dari jam 08.00-16.00\r\n- Dengan memesan, kami anggap pembeli sudah paham, jika terdapat pertanyaan silahkan hubungi kami\r\n- Kerusakan yang diakibatkan proses pengiriman dan kelalaian penyimpanan pembeli tidak dapat kami ganti\r\n\r\nTerima kasih untuk kerjasamanya 🙏', 'https://sibeux.my.id/project/sihalal/uploads/IMG_8969fca5-96a9-4435-80dc-009f8afe179f.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_62c6f62d-5e53-466c-b1ba-77363e6dd9e6.jpg', '', 19900, 1000, 174, 'true'),
(79, 29, 91, 'Gula Pasir Segi Tiga Emas 1000gr x 20pcs', 'Gula Pasir Segi Tiga Emas ukuran 1 Dus (1000gr x 20pcs )\r\n\r\nGula murni yang terbuat dari tebu asli, alami dan tebu pilihan yang berkualitas tinggi. Diproduksi melalui proses yang memenuhi standar mutu untuk menghasilkan produk yang bermutu tinggi.\r\n\r\nKeunggulan Gula Pasir Segi Tiga Emas :\r\n1. Diproduksi dari bahan berkualitas.\r\n2. Bersih, manis, dan alami.\r\n3. Sertifikasi BPOM, HALAL dan SNI.\r\n4. Tersedia dalam berbagai jenis kemasan yang dapat disesuaikan dengan kebutuhan.\r\n\r\nUkuran :\r\n1 Pcs : 1 Kg (1 Box isi : 20 Pcs)\r\nEXPIRED DATE : DESEMBER 2027 (ADA DIPOJOK KANAN BAWAH BAGIAN BELAKANG)\r\nBPOM RI MD 251428029389', 'https://sibeux.my.id/project/sihalal/uploads/IMG_f48244a8-99c2-4444-b6fb-1aa33029c523.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_444f42f6-658a-4190-8dc1-c296d5f48256.jpg', '', 365000, 20000, 971, 'true'),
(80, 29, 1269, 'Bu Lani Bumbu Dasar Tumis / Bumbu Nasi Goreng 250gr', 'Bahan baku utama adalah bawang merah dan putih.\r\n\r\nDapat digunakan untuk nasi goreng, mi goreng, tumis sayur, lodeh, dan sebagainya yang mengandung bawang merah dan bawang putih.', 'https://sibeux.my.id/project/sihalal/uploads/IMG_5fd3a186-a911-4180-b787-f5ee6c22c4ac.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_506eac04-250c-48a0-b19a-80442d2d9ea7.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_957abe5c-7a85-4436-8503-c957ddd90dc3.jpg', 18500, 250, 20, 'true'),
(81, 29, 1625, 'Tepung Tapioka Cap Gunung Agung 500 Gram\r\r', 'Tepung Tapioka Cap Gunung Agung 500 Gram\r\n\r\nMerupakan tepung yang terbuat dari singkong pilihan terbaik yang diolah secara higienis dengan menggunakan teknologi modern sehingga terjaga keaslian dan kebersihannya.\r\n- Berat : 500 Gram', 'https://sibeux.my.id/project/sihalal/uploads/IMG_c3d9fb4f-37a3-4991-aea5-9dded3cdc0bd.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_d62faa94-2f6b-47cc-afdc-6ba61f9e96c6.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_0f06e6ca-bcb9-4855-9014-0212e67f9be9.jpg', 5600, 500, 480, 'true'),
(82, 29, 1287, 'Sasa Santan Cair 200 ml', 'Sasa Santan cair adalah santan perasan pertama yang terbuat dari daging buah kelapa tua segar pilihan. Diproses secara higienis, dengan teknologi modern. Rasa dan aroma santan asli. ', 'https://sibeux.my.id/project/sihalal/uploads/IMG_1d7ad1eb-410d-406b-868b-64ab01dcb97a.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_8829b553-b52f-473e-82e6-404bac76dc58.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_3989d169-6104-4c3b-bcd1-0b28175087c4.jpg', 7500, 200, 57, 'true'),
(83, 29, 240, 'Ternak Syams Susu Kambing Etawa Plus Kolostrum Bubuk 1KG Rasa Original Natural rendah gula mengatasi asam urat nyeri sendi osteoporosis kebas keram kesemutan pengeroposan tulang syaraf kejepit lansia pegel linu diabetes', 'Susu Kambing Etawa Plus Kolostrum Bubuk\r\nNetto : 1000gram / 1KG\r\nProduk tahan 1 tahun setelah kemasan dibuka\r\n\r\nKolostrum susu kambing adalah cairan susu berwarna kekuning-kuningan dan sedikit kental. Cairan susu tersebut keluar pada saat pertama kali setelah induk kambing melahirkan, yang dinamakan kolostrum kambing pada umumnya keluar saat 0-36 jam pertama cairan susu keluar, dan kolostrum kambing terdapat banyak sekali gizi, protein dan nutrisi yang terkandung.\r\n\r\nkolostrum kambing dapat membantu mempertahankan kekebalan tubuh. Kolostrum kambing juga kaya akan zat antibody yang dapat melindungi tubuh dari serangan bakteri dan penyakit. Protein yang terkandung di dalam kolostrum kambing kaya akan protein yang dapat membantu agar pertumbuhan otot, kulit, dan jaringan tubuh bayi bekerja dengan lebih optimal.\r\n\r\nBahkan lebih dari 1.000 studi klinis telah menyatakan bahwa kolostrum telah terbukti aman dan efektif untuk kondisi klinis, seperti virus, alergi dan auto imun / Lupus, penyakit jantung, kanker, program penurunan berat badan, stres atletik, sindrom Usus bocor, penyembuhan luka dan memperbaiki otot.\r\n\r\nManfaat Susu Kambing Etawa Kolostrum Ternak Syams :\r\n1. Mengatasi Infeksi Bakteri\r\n2. Memperkuat Kesehatan Tulang, sendi dan otot\r\n3. Membantu Penyerapan Nutrisi\r\n4. Menjaga Kesehatan Sistem Pencernaan\r\n5. Meningkatkan Kekuatan Otot\r\n6. Meredakan Diare\r\n7. Memperlancara Proses Pernapasan dan Penyebaran Oksigen di Seluruh Tubuh\r\n8. Menajamkan Pandangan Mata\r\n9.Memperkuat Imunitas Tubuh\r\n11. Merawat Sistem Saraf\r\n12. Menurunkan Tekanan Darah\r\n13. Merawat Fungsi Jantung\r\n\r\nKelebihan Susu Kambing Etawa Bubuk Ternak Syams :\r\n1. Tidak ada aroma prengus atau aroma kambing, nyaman di konsumsi.\r\n2. Kualitas susu tidak mengandung bahan pengawet dan juga tidak mengandung bahan kimiawi.\r\n3. Hanya produksi dari susu kambing etawa asli yang berkualitas baik, kami cek selalu kualitasnya.\r\n4. Di olah langsung dari peternakan kambing Ternak Syams, kualitas dan mutu susu kambing etawa terjaga.\r\n5. Di proses melalui teknologi canggih Try Dryer, di olah tanpa mengurangi kandungan gizi dan manfaat susu kambing etawa. Susu Kambing Etawa Ternak Syams akan lebih terasa manfaat dan khasiat nya.\r\n6. Melalui proses pengolahan produksi yang steril dan sesuai dengan ketentuan BPOM.\r\n7. Kualitas dan mutu produk susu kambing etawa bubuk selalu di perhatikan dan di kembangkan, kami berikan yang terbaik agar customer mendapatkan produk berkualitas terbaik.\r\n8. Mengutamakan Kepuasan Pelanggan, karna Kepuasan Anda adalah Tanggung Jawab kami.\r\n\r\nSusu Kambing Etawa Bubuk Ternak Syams dapat di konsumsi oleh :\r\nAnak-anak minimal usia 1 tahun, orang dewasa, ibu hamil dan menyusui, dan juga lansia.\r\n\r\nSusu Kambing Etawa Bubuk Ternak Syams dapat dikonsumsi minimal 2x sehari, pagi hari setelah sarapan dan malam hari sebelum tidur, untuk anak-anak bisa disesuaikan dengan kebutuhan sehari-hari nya.\r\n\r\nNOTE : EXP SESUAI YANG TERTERA PADA KEMASAN', 'https://sibeux.my.id/project/sihalal/uploads/IMG_3d9fb3d9-0068-472a-8369-9b6606d6d1a5.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_e9611c06-0c4d-45ac-a086-d9f0b010ffdb.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_b0fba17c-e6a4-413a-8364-fd93e749654d.jpg', 175000, 1000, 1152, 'true'),
(84, 29, 1630, 'Marjan Boudoin Syrup Cocopandan 460ml x6\r\r', 'Marjan Sirup terbuat dari 100% gula murni, tanpa pengawet dan pemanis buatan. Marjan Sirup hadir dalam berbagai pilihan rasa. Rasa yang terbaik ada di sini. \r\n\r\nBPOM 167210093027\r\nShelf Life: 36 Bulan', 'https://sibeux.my.id/project/sihalal/uploads/IMG_9d8b6953-4cc7-4b2c-b042-6c7dc673a6aa.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_17628bbc-cd1a-44a7-9571-fc639b37d7ad.jpg', '', 127000, 2760, 124, 'true'),
(85, 29, 291, 'AUS Shortplate Beef Slice FRESH CUT! 500gr', 'Shortplate Aus Beef Slice Premium! 500gr \r\nAsal Sapi Dari Australia, Bahan Dasar Shortplate, Berbeda Dengan Us Shortplate yaitu Asal Dan Pakan Sapi nya saja. 🥰 Harga Lebih Terjangkau \r\n\r\nPERBEDAAN UTAMA DENGAN US SHORTPLATE BEEF:\r\n\r\n1. Berasal dari AUSTRALIA, BUKAN USA ya, walaupun sama2 dr bagian shortplate (beef belly).\r\n2. Mayoritas LEMAK 60-70% karena merupakan bagian dari PERUT SAPI.\r\n3. Marblingnya minim, beda dengan US sliced beef yg lebih bermarbling.\r\n4. Level gurih dan juicy di bawah US Shortplate\r\n\r\nASLI AUSTRALIA . Warna Lemaknya Agak Lebih Kuning, Karena Perbedaan Pakan GrassFed. Kalau US lemak Lebih Putih karena Pakan Grainfeed \r\n\r\nWarna Lemaknya Agak Lebih Kuning, Karena Perbedaan Pakan GrassFed. Kalau US lemak Lebih Putih karena Pakan Grainfeed\r\n\r\nS&K Berlaku, mohon dibaca sebelum membeli:\r\n-Daging Halal 100% (Baik jenis daging dan cara pemotongan)\r\n-Disarankan menggunakan pengirimann kurir Instant, apabila menggunakan same day pembeli sudah menerima konsekuensi pengiriman dalam waktu yang lama dan daging sudah cair. \r\n-Tidak menerima complain daging cair dalam perjalanan, karena sudah menjadi tanggung jawab kurir dimana hal ini diluar kapasitas penjual untuk kontrol.\r\n-Complain maksimal 1x24 dari pesanan diterima\r\n-Daging tahan di freezer selama -/+ 1 tahun \r\n-Daging adalah barang ternak dimana warna, dan tekstur tidak selalu sama seperti hasil produksi pabrik maupun foto, namun kami jamin daging yang diterima memiliki kualitas dan grade yang sama sesuai deskripsi toko.\r\n-Semua foto diambil saat daging baru diiris dan belum dibekukan sedangkan daging yang kami kirimkan sudah beku sehingga warna nya akan berbeda dan menjadi lebih gelap.\r\n-Warna Daging yang gelap bukan berarti rusak, warna daging yang rusak bewarna hijau/ bau tidak sedap/ bau asam.\r\n\r\nKetentuan untuk pengiriman melalui Go Sent/ Grab Sent:\r\n-Semua pesanan baik Instant/ Sameday akan kami proses sesuai urutan yang pertama kali pesan.\r\n-Pengiriman Sameday kami terima paling lambat jam 14.00, karena driver membutuhkan waktu pick up lebih lama 1-4 jam sedangkan Kuka Kuka Shop tutup jam 17.00\r\n-Pengiriman Instant akan sampai 1-3 jam, mohon menunggu sesuai waktu tsb karena seller membutuhkan waktu untuk pengemasan dan proses sesuai urutan pembelian.\n', 'https://sibeux.my.id/project/sihalal/uploads/IMG_7b129bd6-d423-4f7e-b675-cd9c37541e01.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_ab08fe0e-161c-4ded-9423-30f437b744f0.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_b9b2ae87-5403-40f7-a5ed-7b0802f75780.jpg', 77500, 500, 85, 'true'),
(86, 29, 1641, 'Garam Himalaya Original 1 kg Asli Organik Himalayan Salt Himsalt Organic Garem Premium MPASI', 'HIMALAYAN SALT - GARAM HIMALAYA ORIGINAL\r\nBy SAIN ORGANIC\r\n\r\nORIGINAL 100% FROM PAKISTAN (BER-GARANSI)\r\nGARANSI UANG KEMBALI\r\n\r\nTersedia ukuran ( pilih di variasi)\r\n- kemasan 250 gram\r\n- kemasan 500 gram\r\n- kemasan 1000 gram (dapat 2 pcs kemasan 500 gram)\r\n\r\nPACKING : GRATIS Bubble Wrap dan Kardus\r\nJika Paket Pecah Garansi Uang Kembali (Syarat : konfirmasi ke seller sebelum memberi penilaian)\r\nExpired : > 4 Tahun\r\n\r\nHimsalt = Garam organik dari pegunungan himalaya tanpa bahan tambahan,\r\ntanpa bahan anti caking (anti penggumpalan).\r\nHimalayan Salt merupakan garam alternatif yang sehat, alami dan bebas bahan kimia.\r\nserta Mengandung mineral alami.\r\n\r\nManfaat Konsumsi :\r\n1. Garam ini mengandung hampir 80 mineral yang berbeda seperti  zat besi magnesium, kalsium, kalium, fosfor, klorida, boron, fluoride, yodium, seng, selenium, tembaga, dan masih banyak lagi.\r\n2. Mengontrol tekanan darah, baik untuk penderita darah tinggi\r\n3. Satu porsi biasa garam pink Himalayan mengandung sodium yang rendah\r\n4. Meningkatkan kemampuan tubuh dalam menyerap nutrisi makanan\r\n5. Menjaga kesehatan pembuluh darah\r\n6. Dapat melegakan fungsi saluran pernapasan\r\n7. Menjaga kesehatan ginjal dan kandung empedu\r\n8. Memperkuat tulang dan gigi\r\n\r\nPenggunaan\r\n1. Dapat dikonsumsi untuk masakan sebagai pengganti garam dapur \r\n2. garam himalaya asli ini juga bisa dicampur ke dalam air mandi untuk mencegah pertumbuhan bakteri dan jamur kulit anak.\r\n3. Dapat digunakan sebagai obat kumur saat sakit gigi\r\n4. Dapat dicampurkan ke dalam minuman (resep JSR)\r\n\r\n#garamhimalaya #himalayansalt #salt #garam #garampink #garamhimalayaasli #garamhimalayaorganik #MPASI #garamMPASI #garemhimalaya #pinksalt #rocksalt #garamsehat #organic #bumbudapur #scrub #himsalt', 'https://sibeux.my.id/project/sihalal/uploads/IMG_7f9479ee-4e61-4b58-86b3-89e8912ad0b0.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_18f90839-af54-41d7-9acc-ec54993e83d9.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_2cc61148-0b59-4b82-bd2d-e4bcf693742e.jpg', 115520, 1000, 652, 'true'),
(87, 29, 829, 'Minyak Goreng Segi Tiga Emas 1000ml x 12pcs\r\r', 'Minyak Goreng Segi Tiga Emas ukuran 1 Dus ( 1000ml x 12pcs )\r\n\r\nMinyak goreng di buat dari kelapa sawit pilihan dan secara PREMIUM MULTI PROSES tanpa bahan pengawet yang menghasilkan minyak dengan warna kuning alami  berasal dari  sawit alam berkualitas. Dengan Proses 2 kali penyaringan sehingga menghasilkan minyak goreng yang jernih dan sehat.\r\n\r\nKeunggulan Minyak Goreng Segi Tiga Emas:\r\n1. Diproduksi dengan bahan pilihan yang berkualitas secara Premium Multi Proses.\r\n2. Diproses melalui 2 kali penyaringan sehingga menghasilkan minyak yang jernih dan sehat\r\n3. Mengandung Omega-9.\r\n4. Sertifikasi BPOM, HALAL, SNI.\r\n5. Tersedia dalam berbagai jenis kemasan yang dapat disesuaikan dengan kebutuhan.\r\n\r\nUkuran : 1 Liter /Pcs\r\nMaterial : Pouch\r\nEXPIRED DATE : DESEMBER 2027\r\nBPOM RI MD 208128015389\r\n\r\nGunakan selalu minyak yang berkualitas. Karena lebih jernih, hemat pemakaiannya, cepat panas, serta tahan terhadap panas.', 'https://sibeux.my.id/project/sihalal/uploads/IMG_ee1f6e61-3bd6-42a9-bbad-7b922e793f07.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_81a30417-d2d7-4cb8-8489-227d4765fb7d.jpg', '', 236000, 12000, 19635, 'true'),
(88, 29, 1518, '1 Dus Saus Sambal Sari Pedas Kemasan Sachet\r\r', 'Selamat datang di Toko kami, kami menjual kebutuhan baksoan yang siap menjadi mitra bisnis anda.\r\n\r\nSaus Sambal Sari Pedas Kemasan Sachet 35g\r\n\r\nHadir memberi kemudahan, jadi gak perlu repot bungkusin lagi,tentunya menghemat waktu, tenaga dan uang anda, Cocok untuk bakso, mie ayam dan makanan lainnya. \r\n\r\nHarga tertera untuk 1 Dus isi 300 sachet\r\n@35 gr\r\n\r\nKomposisi:\r\nUbi, Cabe rawit, Pewarna makanan, orange red No. 2107, Garam, Cuka Makanan, Rempah-rempah, Na Benzoat, Pemanis, Air. \r\n\r\nTerbuat dari bahan pilihan dikerjakan dengan resik dan teliti, rasanya enak, gurih, wangi & halal. \r\n\r\nCocok untuk bakso dan mie ayam. \r\n\r\nMUI: 15060047900420\r\nP-IRT No. : 11129010165-22\r\n\r\nDi produksi oleh:\r\nSumber Sari\r\n\r\nMohon Diperhatikan:\r\n- Kami buka hari Senin sd Sabtu ( Minggu Off )\r\n- Pesanan yang masuk di hari senin - Jumat lewat jam 14.00 di proses di hari berikutnya\r\n- Pesanan yang masuk di hari sabtu lewat jam 14.00 akan di proses di hari senin\r\n- Mohon di pastikan pesanan sesuai varian dan ukuran gramaturnya\r\n- Jika ada pesanan yang tidak sesuai silahkan ajukan melalui chat Admin\r\n- Memberi ulasan tidak baik tanpa alasan yang jelas kami auto blokir\r\n\r\nSelamat Berbelanja dan sukses selalu..\r\n\r\nPENTING!\r\nJangan lupa untuk mengambil video Unboxing sebagai bukti untuk klaim jika ada barang kurang atau tidak sesuai pesanan\r\n\r\n', 'https://sibeux.my.id/project/sihalal/uploads/IMG_3bc7063f-25b4-4fdc-abda-91addde17a34.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_eac68c60-b019-4510-b187-48c633d77957.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_ec5938c3-f4f1-4c8b-a4d1-b68f36ae5eb5.jpg', 95000, 11000, 96, 'true'),
(89, 29, 1142, 'La Fonte Pasta Macaroni 225gr, Halal', '“LABEL INSTANT 2 JAM” yang tertera pada gambar produk di Shopee hanyalah iklan promosi dari pihak shopee (bukan ketentuan dari Toko). INSTANT 2 JAM adalah WAKTU ESTIMASI kedatangan paket di lokasi tujuan, setelah kurir mengambil [PICK UP] paket pesanan yang sudah disiapkan dari Toko. “(Bukan sejak pesanan kami terima)”.\r\n\r\nINFORMASI PRODUK:\r\nLa Fonte Macaroni Pasta berbentuk pendek [Elbow Macaroni & Elbow Macaroni]. Terbuat dari Semolina gandum durum pilihan. Untuk toppingnya, tinggal tambahkan saus krim keju susu, saus tomat dengan daging atau irisan ikan. Karena pasta ini mempunyai  permukaan yang bertekstur sehingga saus mudah meresap dan tahan lama. 225 gr =  40 - 60 gr/porsi. Rebus pasta selama 8 - 12 menit atau sesuai dengan tingkat kekenyalan yang diinginkan. Sajikan dengan saus tomat lalu kombinasikan dengan sayuran atau keju, dan pasta pun siap untuk dinikmati.\r\n- Jenis Produk: Pasta/Macaroni [Spiral & Elbow]\r\n- Merek: La Fonte\r\n- Berat: 225\r\n- Penyimpanan: Suhu Ruangan/Kering\r\n- Sertifikasi : Halal Terverifikasi MUI.\r\n\r\nINFORMASI PENGIRIMAN:\r\n- Pengiriman ke alamat kantor & butuh cepat sampai sangat dianjurkan [WAJIB] menggunakan pengiriman kurir INSTANT.\r\n- FYI bahwa kami sebagai penjual di berikan batas waktu oleh Shopee 1x24 jam (Minimum) untuk melakukan pengiriman ke pembeli.\r\n- Kami memproses pesanan segera SETELAH JAM BUKA TOKO, sesuai antrian pesanan.\r\n\r\nKETENTUAN BATAS PENGIRIMAN:\r\n1.  Pesanan menggunakan Kurir SameDay sebelum jam 14:00 diproses di hari yang sama.\r\n2.  Pesanan menggunakan Kurir INSTANT sebelum jam 16:00 diproses di hari yang sama.\r\n*DILUAR KETENTUAN TERSEBUT AKAN DIPROSES DI HARI BERIKUTNYA.\r\n\r\nSYARAT PENGAJUAN KOMPLAIN:\r\nKami hanya menerima komplain yang disertai dengan video unboxing. “(Video dari awal paket sebelum dibuka hingga selesai)”.\r\n*KOMPLAIN TANPA VIDEO UNBOXING TIDAK DAPAT DIPROSES.\r\n\r\nMEMBELI = MENYETUJUI KETENTUAN YANG DITETAPKAN.', 'https://sibeux.my.id/project/sihalal/uploads/IMG_8fef8ca4-a90b-4bab-96b0-b7707ca14907.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_0fcfd330-9619-4fae-9889-1145ee42749f.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_70a52b2f-8ffc-452b-a064-3b6ecd90ee5a.jpg', 8500, 225, 55, 'true'),
(90, 29, 1551, 'Del\'s - Keju Mozzarella / Mozzarella Cheese Extra Stretchy - 250 Gram', 'DELS Keju Mozzarella / Mozzarella Cheese Stretchy - 250 Gr\r\n\r\nSpesifikasi :\r\nDels Keju Mozzarella / Mozzarella Cheese Stretchy memberikan rasa creamy yang lezat, mudah meleleh dan menyebar saat dipanggang serta ekstra stretchy saat masih hangat.\r\n\r\nInformasi Produk: ​\r\n- Keju Mozzarella ​\r\n- Berat Bersih : 250 Gr\r\n- Extra Stretchy\r\n- Easy Melt\r\n- High Protein & Calcium\r\n- Saran penggunaan: Cocok diaplikasikan pada pizza, sandwich, toast, pasta dll.​\r\n- Diimport dari Irlandia​\r\n- Pengemasan : Vacuum sealed​\r\n- Expired Date : Chat untuk informasi terupdate berkenaan ED\r\n- HALAL\r\n- Saran Penyimpanan: Simpan di Freezer (-18\'C) sebelum digunakan, jangan dibekukan kembali setelah dicairkan. Simpan dalam chiller (-4\'C) dalam wadah tertutup rapat setelah dicairkan.​\r\n___________________________________________________​\r\n\r\nCatatan:​\r\n* Batas waktu order dengan pengiriman instant sampai jam 4 sore akan dikirim di hari yang sama​\r\n* Batas waktu order dengan pengiriman sameday sampai jam 2 siang akan dikirim di hari yang sama​\r\n* Pesanan melewati batas waktu atau hari libur akan diproses keesokan harinya pada hari kerja.​\r\n*Khusus semua produk frozen hanya bisa dikirim melalui GOJEK/GRAB instant untuk menjaga kulaitasnya\r\n* Produk frozen hanya dapat bertahan maksimal 4 jam di luar suhu penyimpanan freezer.​\r\n\r\n', 'https://sibeux.my.id/project/sihalal/uploads/IMG_09ebecea-816c-43e2-b570-9ea205eafc50.jpg', 'https://sibeux.my.id/project/sihalal/uploads/IMG_5d5e6bf0-8db4-4934-bec0-718ed60ed6aa.jpg', '', 46155, 250, 89, 'true'),
(91, 29, 1193, 'Tempe Daun 1 Pcs Freshbox', 'Tempe yang dikemas dengan daun pisang sebagai pembungkusnya yang memiliki aroma harum yang berasal dar daun pisang\r\n\r\n[FRESHBOX INFO]\r\n\r\nInfo Pengiriman:\r\n1. Order yang masuk pukul 00.00 – 20.00 akan dikirimkan H+1\r\n2. Order yang masuk pukul 20.00 – 23.58 akan dikirimkan H+2\r\n\r\nInfo Penukaran Barang/Komplain:\r\n1.  Batas maksimal keluhan adalah 3 jam setelah barang diterima oleh customer \r\n2.  Jika pesanan yang diterima tidak sesuai dengan spesifikasi customer dapat melampirkan foto produk dan shipping label\r\n\r\nJam operational Customer Service 09.30 – 18.30\r\n\r\nKami selalu berusaha memberikan yang terbaik untuk merespon kebutuhan kamu!\r\n\r\nFreshBox, \r\nFrom Our Farm to Your Table', 'https://sibeux.my.id/project/sihalal/uploads/IMG_2cfba39f-65c8-486a-ba6d-7d058dea9746.jpg', '', '', 10200, 126, 662, 'true'),
(94, 29, 1404, 'Telur rebus asin', 'telur rebus asin', 'https://sibeux.my.id/project/sihalal/uploads/IMG_038141e7-5acb-4111-8af8-485a175010ec.jpg', '', '', 2500, 100, 49, 'true');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id_rating` int(5) NOT NULL,
  `id_produk` int(5) DEFAULT NULL,
  `id_user` mediumint(5) DEFAULT NULL,
  `id_pesanan` int(5) DEFAULT NULL,
  `bintang_rating` int(1) NOT NULL,
  `pesan_rating` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_rating` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id_rating`, `id_produk`, `id_user`, `id_pesanan`, `bintang_rating`, `pesan_rating`, `tanggal_rating`) VALUES
(5, 58, 28, 32, 4, '', '2024-11-24 09:22:35'),
(6, 72, 23, 34, 3, 'Suka banget sama bumbunya, enak pollll', '2024-11-24 10:10:56'),
(7, 72, 29, 35, 4, 'Baru coba bumbu gado-gado Boplo, rasanya otentik banget! Praktis tinggal tambahin air, dan aromanya wangi khas. Cocok untuk yang kangen rasa gado-gado asli. Recommended!', '2024-11-24 14:48:56'),
(8, 52, 29, 36, 5, 'Tempenya enak bgt! Rasanya gurih, tekstur lembut tapi tetep ada kriuk dikit di pinggirannya. Bungkus daun pisang bikin aromanya tambah mantap. Cocok banget buat lauk nasi panas. Recommended bgt buat yg suka tempe tradisional. 👍', '2024-11-25 10:01:42'),
(9, 71, 23, 37, 5, '', '2024-11-26 14:02:34'),
(10, 58, 28, 40, 5, 'Josssss', '2024-12-03 08:52:41'),
(11, 48, 28, 42, 4, '', '2024-12-04 12:22:21'),
(12, 91, 28, 47, 5, 'Tempenya fresh, gk bau, top bgt pokoknya dah... ', '2024-12-12 07:38:35'),
(13, 57, 29, 48, 3, 'cukup ok sausnya, tapi tadi ada beberapa kemasa yg sdh rusak.. but overalls oke 👌', '2024-12-12 08:27:25'),
(14, 91, 23, 52, 4, 'sedap bangetttt', '2025-01-12 14:42:29'),
(15, 91, 23, 57, 4, 'good', '2025-01-13 15:10:13');

-- --------------------------------------------------------

--
-- Table structure for table `shhalal`
--

CREATE TABLE `shhalal` (
  `id_shhalal` int(5) NOT NULL,
  `kategori_shhalal` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_shhalal` tinytext NOT NULL,
  `merek_shhalal` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `shhalal`
--

INSERT INTO `shhalal` (`id_shhalal`, `kategori_shhalal`, `nomor_shhalal`, `merek_shhalal`) VALUES
(52, 'gula', 'ID37410982654710923', 'Gula Sehat'),
(53, 'gula', 'ID82910467589234012', 'Manis Gulaku'),
(54, 'gula', 'ID10476239821047654', 'Tropicana Slim'),
(55, 'gula', 'ID90812764538910276', 'Sari Manis'),
(56, 'gula', 'ID57309182467521098', 'Gulasegar'),
(57, 'gula', 'ID20398517490283765', 'Tebu Jaya'),
(58, 'gula', 'ID90182374561982374', 'Sweet Life'),
(59, 'gula', 'ID34712908476351290', 'Madu Manis'),
(60, 'gula', 'ID82936450918274561', 'Arenga Organic'),
(61, 'gula', 'ID17482930475192834', 'Gula Aren'),
(62, 'gula', 'ID64783921056473820', 'Gula Indo'),
(63, 'gula', 'ID12894376502837415', 'Raw Sugar'),
(64, 'gula', 'ID73249812098347124', 'Gula Madani'),
(65, 'gula', 'ID57429386190234876', 'Sweety Sugar'),
(66, 'gula', 'ID83746502918374652', 'Golden Cane'),
(67, 'gula', 'ID91827456023874653', 'Gula Sari'),
(68, 'gula', 'ID28746519827465321', 'Gula Merah'),
(69, 'gula', 'ID47589120394756281', 'Putih Manis'),
(70, 'gula', 'ID23847591023847562', 'Sweet Sugar'),
(71, 'gula', 'ID19823465028374659', 'Lezat Gulaku'),
(72, 'gula', 'ID30294857102938475', 'Alam Sweet'),
(73, 'gula', 'ID10483956210938457', 'Gula Sukses'),
(74, 'gula', 'ID84736201938475629', 'Manis Nusantara'),
(75, 'gula', 'ID29837456198374652', 'Indo Sweet'),
(76, 'gula', 'ID39487561209834765', 'Gula Kristal'),
(77, 'gula', 'ID98456321094837651', 'Tebu Murni'),
(78, 'gula', 'ID48576910328476532', 'Sweet Indo'),
(79, 'gula', 'ID28475619384756321', 'Gula Putih'),
(80, 'gula', 'ID30847561293847560', 'Gula Organik'),
(81, 'gula', 'ID29487561098436512', 'Rasa Nusantara'),
(82, 'gula', 'ID18374659128374659', 'Gula Tropis'),
(83, 'gula', 'ID38475621093847563', 'Sugar Delight'),
(84, 'gula', 'ID28473650129837461', 'Arengas Choice'),
(85, 'gula', 'ID10829374651984756', 'Gula Crystal'),
(86, 'gula', 'ID28374659120394857', 'Sweet Aroma'),
(87, 'gula', 'ID94756201938475612', 'Gula Rasa'),
(88, 'gula', 'ID93847561028374659', 'Serasi Sweet'),
(89, 'gula', 'ID18376452019384756', 'Gula Premium'),
(90, 'gula', 'ID20837465129837465', 'White Gold'),
(91, 'gula', 'ID28374651093847562', 'Crystal Sugar'),
(92, 'gula', 'ID29384756012837465', 'Tebu Putih'),
(93, 'gula', 'ID28374651293847561', 'Sweet Gold'),
(94, 'gula', 'ID38947561092837456', 'Sari Gula'),
(95, 'gula', 'ID28475610938475623', 'Gula Herbal'),
(96, 'gula', 'ID47382916520938476', 'Healthy Sugar'),
(97, 'gula', 'ID38475621093746582', 'Manis Super'),
(98, 'gula', 'ID27384956210837465', 'Gula Lestari'),
(99, 'gula', 'ID20984736102837465', 'Sweet Nature'),
(100, 'gula', 'ID47382956120938476', 'Gula Mulia'),
(101, 'gula', 'ID39487561029384757', 'Natural Sweetener'),
(154, 'tepung', 'ID37410982654710901', 'Tepung Terigu Sehat'),
(155, 'tepung', 'ID82910467589234023', 'Serbaguna ABC'),
(156, 'tepung', 'ID10476239821047657', 'Bogasari Segitiga Biru'),
(157, 'tepung', 'ID90812764538910289', 'Cakra Kembar'),
(158, 'tepung', 'ID57309182467521019', 'Kunci Biru'),
(159, 'tepung', 'ID20398517490283767', 'Pandan Wangi'),
(160, 'tepung', 'ID90182374561982375', 'Rose Brand'),
(161, 'tepung', 'ID34712908476351291', 'Ladang Lima'),
(162, 'tepung', 'ID82936450918274562', 'Tepung Beras Organik'),
(163, 'tepung', 'ID17482930475192835', 'Sedaap Tepung'),
(164, 'tepung', 'ID64783921056473821', 'Tepung Beras Cap Orang Tua'),
(165, 'tepung', 'ID12894376502837416', 'Gandum Nusantara'),
(166, 'tepung', 'ID73249812098347125', 'Kobe Tepung Bumbu'),
(167, 'tepung', 'ID57429386190234877', 'Mekar Sari'),
(168, 'tepung', 'ID83746502918374653', 'Tepung Panir'),
(169, 'tepung', 'ID91827456023874654', 'Panko Crispy'),
(170, 'tepung', 'ID28746519827465322', 'MamaSuka Tepung Bumbu'),
(171, 'tepung', 'ID47589120394756282', 'Crispy Gold'),
(172, 'tepung', 'ID23847591023847563', 'Bola Deli'),
(173, 'tepung', 'ID19823465028374660', 'San Remo'),
(174, 'tepung', 'ID30294857102938476', 'Masaesaku Tepung Bumbu'),
(175, 'tepung', 'ID10483956210938458', 'Mamasuka Tepung Roti'),
(176, 'tepung', 'ID84736201938475630', 'Sarimurni'),
(177, 'tepung', 'ID29837456198374653', 'Cap Cangkir'),
(178, 'tepung', 'ID39487561209834766', 'Tepung Mocaf'),
(179, 'tepung', 'ID98456321094837652', 'Arrowroot Organic'),
(180, 'tepung', 'ID48576910328476533', 'Tepung Kentang'),
(181, 'tepung', 'ID28475619384756322', 'Tepung Jagung'),
(182, 'tepung', 'ID30847561293847561', 'Sasa Tepung Bumbu'),
(183, 'tepung', 'ID29487561098436513', 'Golden Sun'),
(184, 'tepung', 'ID18374659128374660', 'Ayam Goreng Tepung'),
(185, 'tepung', 'ID38475621093847564', 'Masako Tepung'),
(186, 'tepung', 'ID28473650129837462', 'Cap Kepala Tepung'),
(187, 'tepung', 'ID10829374651984757', 'Sakura Roti Tepung'),
(188, 'tepung', 'ID28374659120394858', 'Chilli Coating'),
(189, 'tepung', 'ID94756201938475613', 'Okara Crispy'),
(190, 'tepung', 'ID93847561028374660', 'Pancake Instant'),
(191, 'tepung', 'ID18376452019384757', 'Arang Emas'),
(192, 'tepung', 'ID20837465129837466', 'Kroket Tepung'),
(193, 'tepung', 'ID28374651093847563', 'Lumpia Tepung'),
(194, 'tepung', 'ID29384756012837466', 'Pia Tepung'),
(195, 'tepung', 'ID28374651293847562', 'Kentang Tepung'),
(196, 'tepung', 'ID38947561092837457', 'Manis Kukus'),
(197, 'tepung', 'ID28475610938475624', 'Brownies Tepung'),
(198, 'tepung', 'ID47382916520938477', 'Golden Cake Mix'),
(199, 'tepung', 'ID38475621093746583', 'Banana Flour'),
(200, 'tepung', 'ID27384956210837466', 'Churros Mix'),
(201, 'tepung', 'ID20984736102837466', 'Spring Roll Flour'),
(202, 'tepung', 'ID47382956120938477', 'Tepung Goreng'),
(203, 'tepung', 'ID39487561029384758', 'Bolu Tepung'),
(204, 'tepung', 'ID29384756120938479', 'Matcha Flour'),
(205, 'tepung', 'ID29385761293847562', 'Golden Wheat'),
(206, 'susu', 'ID37419847628374913', 'Ultra Milk'),
(207, 'susu', 'ID29384756198237456', 'Frisian Flag'),
(208, 'susu', 'ID83920485762839475', 'Greenfields'),
(209, 'susu', 'ID74829356109283745', 'Indomilk'),
(210, 'susu', 'ID84920384756182734', 'Milo'),
(211, 'susu', 'ID39485720193847563', 'Dancow'),
(212, 'susu', 'ID19384756192837456', 'Bear Brand'),
(213, 'susu', 'ID37492018475618374', 'Zee'),
(214, 'susu', 'ID84930293847561283', 'Anlene'),
(215, 'susu', 'ID39485720193847564', 'Enfagrow'),
(216, 'susu', 'ID29384756198237457', 'Lactogen'),
(217, 'susu', 'ID83920485762839476', 'Similac'),
(218, 'susu', 'ID74829356109283746', 'Bebelac'),
(219, 'susu', 'ID84920384756182735', 'Pediasure'),
(220, 'susu', 'ID39485720193847565', 'Sustagen'),
(221, 'susu', 'ID19384756192837457', 'Vidoran Xmart'),
(222, 'susu', 'ID37492018475618375', 'Nutrilon'),
(223, 'susu', 'ID84930293847561284', 'Enfamil'),
(224, 'susu', 'ID39485720193847566', 'SGM'),
(225, 'susu', 'ID29384756198237458', 'Morinaga Chil Kid'),
(226, 'susu', 'ID83920485762839477', 'Nutren Junior'),
(227, 'susu', 'ID74829356109283747', 'Ensure'),
(228, 'susu', 'ID84920384756182736', 'Procal'),
(229, 'susu', 'ID39485720193847567', 'Anmum'),
(230, 'susu', 'ID19384756192837458', 'Appeton Weight Gain'),
(231, 'susu', 'ID37492018475618376', 'Viva'),
(232, 'susu', 'ID84930293847561285', 'Lactamil'),
(233, 'susu', 'ID39485720193847568', 'Prenagen'),
(234, 'susu', 'ID29384756198237459', 'Nutrilac'),
(235, 'susu', 'ID83920485762839478', 'Isomil Plus'),
(236, 'susu', 'ID74829356109283748', 'Nestle NAN'),
(237, 'susu', 'ID84920384756182737', 'Meiji'),
(238, 'susu', 'ID39485720193847569', 'Pedialyte'),
(239, 'susu', 'ID19384756192837459', 'Hilo Teen'),
(240, 'susu', 'ID37492018475618377', 'Nestle Lactogen'),
(241, 'susu', 'ID84930293847561286', 'Pediasure Complete'),
(242, 'susu', 'ID39485720193847570', 'Nutramigen'),
(243, 'susu', 'ID29384756198237460', 'Ensure Gold'),
(244, 'susu', 'ID83920485762839479', 'Friso'),
(245, 'susu', 'ID74829356109283749', 'Frisomum Gold'),
(246, 'susu', 'ID84920384756182738', 'SGM LLM+'),
(247, 'susu', 'ID39485720193847571', 'Wyeth'),
(248, 'susu', 'ID19384756192837460', 'Pediasure Triplesure'),
(249, 'susu', 'ID37492018475618378', 'Pediasure Plus'),
(250, 'susu', 'ID84930293847561287', 'SGM Eksplor'),
(251, 'susu', 'ID39485720193847572', 'Dumex Dugro'),
(252, 'susu', 'ID29384756198237461', 'Puregrow Organic'),
(253, 'susu', 'ID83920485762839480', 'Vitalac'),
(254, 'susu', 'ID74829356109283750', 'Nestle Boost'),
(255, 'susu', 'ID84920384756182739', 'Sustagen Junior'),
(256, 'daging', 'ID83746592837409182', 'Bernardi'),
(257, 'daging', 'ID72836459820183746', 'So Good'),
(258, 'daging', 'ID93847562837409283', 'Fiesta'),
(259, 'daging', 'ID20394857619283745', 'Farmhouse'),
(260, 'daging', 'ID83920192837465748', 'Cedea'),
(261, 'daging', 'ID38475619283745012', 'Belfoods'),
(262, 'daging', 'ID18293746582938475', 'Kimbo'),
(263, 'daging', 'ID49583720192837465', 'Chop Buntut Cak Yo'),
(264, 'daging', 'ID58293746582938476', 'Prima Food'),
(265, 'daging', 'ID73849584720192837', 'Sunpride'),
(266, 'daging', 'ID39485719283746583', 'Delicious Farm'),
(267, 'daging', 'ID57483920193847565', 'Golden Farm'),
(268, 'daging', 'ID83920138475619283', 'Sasa'),
(269, 'daging', 'ID18374659283746572', 'Primarasa'),
(270, 'daging', 'ID94758392019283745', 'Beef Master'),
(271, 'daging', 'ID28374659283746582', 'Daging Nusantara'),
(272, 'daging', 'ID48573619283746592', 'Sumber Selera'),
(273, 'daging', 'ID38475619283746501', 'Japfa'),
(274, 'daging', 'ID73948572837401938', 'Kobe'),
(275, 'daging', 'ID82736459837465719', 'Mandiri Farm'),
(276, 'daging', 'ID39485728374659283', 'Mr. Meat'),
(277, 'daging', 'ID84920392019283747', 'Prima'),
(278, 'daging', 'ID73948572837401939', 'Tasty Meat'),
(279, 'daging', 'ID18293746582938477', 'Purefoods'),
(280, 'daging', 'ID28374659283746583', 'Japota'),
(281, 'daging', 'ID47583920193847563', 'Softex'),
(282, 'daging', 'ID39284756192837458', 'Best Meat'),
(283, 'daging', 'ID49584720193847567', 'Farm Fresh'),
(284, 'daging', 'ID83746592837401984', 'Good Daging'),
(285, 'daging', 'ID48573619283746503', 'Sapindo'),
(286, 'daging', 'ID94857392019283748', 'Cikini Meat'),
(287, 'daging', 'ID10293847563746584', 'Hanara'),
(288, 'daging', 'ID28374659384719285', 'Lezatku'),
(289, 'daging', 'ID47583920193847564', 'Sagami'),
(290, 'daging', 'ID49584720193847568', 'Hokido'),
(291, 'daging', 'ID93847563746510294', 'Big Beef'),
(292, 'daging', 'ID38475619283746504', 'Premium Meat'),
(293, 'daging', 'ID29384756193847562', 'Cahaya Daging'),
(294, 'daging', 'ID94857392019283749', 'Segar Nusantara'),
(295, 'daging', 'ID83920192837465749', 'Red Meat'),
(296, 'daging', 'ID28374659283746584', 'Deli Meat'),
(297, 'daging', 'ID38475619283746505', 'Sabana'),
(298, 'daging', 'ID94857392019283750', 'Beef Box'),
(299, 'daging', 'ID29384756193847563', 'Top Daging'),
(300, 'daging', 'ID82736459837465720', 'Bintang Nusantara'),
(301, 'daging', 'ID47583920193847565', 'New Meats'),
(302, 'daging', 'ID18293746582938478', 'Jaya Meat'),
(303, 'daging', 'ID48573619283746506', 'Meat Solutions'),
(304, 'daging', 'ID74839284756192837', 'Daging Jaya'),
(305, 'daging', 'ID29384756193847564', 'Nutri Meat'),
(306, 'daging', 'ID38475619283746507', 'Beefy'),
(824, 'minyak', 'ID38475619281746501', 'Bimoli'),
(825, 'minyak', 'ID29384756192837402', 'Filma'),
(826, 'minyak', 'ID39485728374659203', 'Kunci Biru'),
(827, 'minyak', 'ID83920485762839404', 'Sania'),
(828, 'minyak', 'ID28475619283746505', 'Tania'),
(829, 'minyak', 'ID38475628374659206', 'Soya Rata'),
(830, 'minyak', 'ID84930293847561207', 'Cimory'),
(831, 'minyak', 'ID29384756192837408', 'Happy Call'),
(832, 'minyak', 'ID83920485762839409', 'Palmia'),
(833, 'minyak', 'ID38475619283746510', 'Lifree'),
(834, 'minyak', 'ID29384756192837411', 'Hargo Djaya'),
(835, 'minyak', 'ID39485728374659212', 'Coconut King'),
(836, 'minyak', 'ID83920485762839413', 'Tropicana'),
(837, 'minyak', 'ID38475619283746514', 'Cap Burung Dara'),
(838, 'minyak', 'ID29384756192837415', 'Minyak Wijen Aulia'),
(839, 'minyak', 'ID83920485762839416', 'Gold Palm'),
(840, 'minyak', 'ID38475628374659217', 'Sunsilk'),
(841, 'minyak', 'ID29384756192837418', 'Vasco'),
(842, 'minyak', 'ID39485728374659219', 'Minyak Kenari'),
(843, 'minyak', 'ID83920485762839420', 'Nabati'),
(844, 'minyak', 'ID38475619283746521', 'Minyak Zaitun Al-Dhia'),
(845, 'minyak', 'ID29384756192837422', 'Coconut Queen'),
(846, 'minyak', 'ID83920485762839423', 'Cap Lang'),
(847, 'minyak', 'ID38475619283746524', 'Pure Palm Oil'),
(848, 'minyak', 'ID29384756192837425', 'Minyak Goreng Minanti'),
(849, 'minyak', 'ID83920485762839426', 'Golden Palm Oil'),
(850, 'minyak', 'ID38475628374659227', 'Kenangan Minyak'),
(851, 'minyak', 'ID29384756192837428', 'Cap Top'),
(852, 'minyak', 'ID39485728374659229', 'Berlian'),
(853, 'minyak', 'ID83920485762839430', 'Sunflower Oil'),
(854, 'minyak', 'ID38475619283746531', 'Mega Palm Oil'),
(855, 'minyak', 'ID29384756192837432', 'Minyak Goreng Sehat'),
(856, 'minyak', 'ID83920485762839433', 'Minyak Soya Canggih'),
(857, 'minyak', 'ID38475619283746534', 'Minyak Sumber Alam'),
(858, 'minyak', 'ID29384756192837435', 'Golden Eagle Oil'),
(859, 'minyak', 'ID83920485762839436', 'Alimang Jaya'),
(860, 'minyak', 'ID38475619283746537', 'Sinar Palm'),
(861, 'minyak', 'ID29384756192837438', 'Minyak Ria'),
(862, 'minyak', 'ID83920485762839439', 'Tunggal Minyak'),
(863, 'minyak', 'ID38475628374659240', 'Sunshine Oil'),
(864, 'minyak', 'ID29384756192837441', 'Samsul Minyak'),
(865, 'minyak', 'ID83920485762839442', 'Minyak Wijen Soja'),
(866, 'minyak', 'ID38475619283746543', 'Golden Harvest'),
(867, 'minyak', 'ID29384756192837444', 'Tropi Oil'),
(868, 'minyak', 'ID83920485762839445', 'Minyak Dapur Lestari'),
(869, 'minyak', 'ID38475619283746546', 'Cap Ayam'),
(870, 'minyak', 'ID29384756192837447', 'Kemuning Oil'),
(1141, 'pasta', 'ID98456173284719607', 'Barilla'),
(1142, 'pasta', 'ID58473619284756108', 'La Molisana'),
(1143, 'pasta', 'ID23984756192837409', 'Rummo'),
(1144, 'pasta', 'ID19384756292837410', 'De Cecco'),
(1145, 'pasta', 'ID38475612984765411', 'Bertolli'),
(1146, 'pasta', 'ID98476284937451012', 'Divella'),
(1147, 'pasta', 'ID83720492736452813', 'Granoro'),
(1148, 'pasta', 'ID23874563298475614', 'Colavita'),
(1149, 'pasta', 'ID84930292837461515', 'Sgambaro'),
(1150, 'pasta', 'ID28475618394762516', 'Cucina Viva'),
(1151, 'pasta', 'ID38475628374619117', 'Vero Lucano'),
(1152, 'pasta', 'ID83920485617342818', 'Pasta Zara'),
(1153, 'pasta', 'ID38475619283746519', 'Monograno Felicetti'),
(1154, 'pasta', 'ID29384756192837420', 'Garofalo'),
(1155, 'pasta', 'ID39284756192837421', 'Rana'),
(1156, 'pasta', 'ID49384756192837422', 'Spaghetteria'),
(1157, 'pasta', 'ID38475628374659223', 'Riscossa'),
(1158, 'pasta', 'ID29384756192837424', 'Pasta Lensi'),
(1159, 'pasta', 'ID49384756192837425', 'Antico Pastificio Morelli'),
(1160, 'pasta', 'ID29487651283746526', 'Cipriani'),
(1161, 'pasta', 'ID38475629384756227', 'Molino di Ferro'),
(1162, 'pasta', 'ID29483756192837428', 'Pasta Molisana'),
(1163, 'pasta', 'ID38475619384752829', 'Tortiglioni'),
(1164, 'pasta', 'ID38475628374659230', 'Fagottini'),
(1165, 'pasta', 'ID29475619283746531', 'Penne Rigate'),
(1166, 'pasta', 'ID38475692837465232', 'Spaghetti Giovanni'),
(1167, 'pasta', 'ID29384756192837433', 'Fettuccine Tarallo'),
(1168, 'pasta', 'ID38475619384756234', 'Lasagna Bolognese'),
(1169, 'pasta', 'ID83920485762839435', 'Macaroni Italy'),
(1170, 'pasta', 'ID38475629384756236', 'Ravioli Salami'),
(1171, 'pasta', 'ID29384756192837437', 'Tagliatelle La Bella'),
(1172, 'pasta', 'ID39485728374659238', 'Bucatini Romani'),
(1173, 'pasta', 'ID83920485762139439', 'Capellini Light'),
(1174, 'pasta', 'ID38475629384756240', 'Gnocchi Deliziosa'),
(1175, 'pasta', 'ID29384756192834441', 'Fagottini Verde'),
(1176, 'pasta', 'ID38475692837456242', 'Trofie Pesto'),
(1177, 'pasta', 'ID83920485762839443', 'Cannelloni Mama'),
(1178, 'pasta', 'ID38475629384756244', 'Cavatelli Pappardelle'),
(1179, 'pasta', 'ID29384756192837445', 'Penne Pizzaiola'),
(1180, 'pasta', 'ID38475628374659246', 'Maccheroni Ricci'),
(1181, 'pasta', 'ID83920485762839447', 'Tagliolini Lemon'),
(1182, 'pasta', 'ID38475629384756248', 'Spaghetti alla Puttanesca'),
(1183, 'pasta', 'ID29384756192837449', 'Fusilli Allegri'),
(1184, 'pasta', 'ID38475629384756250', 'Ravioli Fresco'),
(1185, 'pasta', 'ID29384756192837451', 'Pappardelle Wild Boar'),
(1186, 'olahan', 'ID38475629384756001', 'Bumi Tempe'),
(1187, 'olahan', 'ID38475629384756002', 'Nusa Tahu'),
(1188, 'olahan', 'ID38475629384756003', 'Surya Lumpia'),
(1189, 'olahan', 'ID38475629384756004', 'Indo Bakwan'),
(1190, 'olahan', 'ID38475629384756005', 'Green Nugget'),
(1191, 'olahan', 'ID38475629384756006', 'Bonanza Perkedel'),
(1192, 'olahan', 'ID38475629384756007', 'Farm Kentang'),
(1193, 'olahan', 'ID38475629384756008', 'Tani Tempe'),
(1194, 'olahan', 'ID38475629384756009', 'Sari Pangsit'),
(1195, 'olahan', 'ID38475629384756010', 'Prima Otak'),
(1196, 'olahan', 'ID38475629384756011', 'Eco Tempe'),
(1197, 'olahan', 'ID38475629384756012', 'Tahu Indo'),
(1198, 'olahan', 'ID38475629384756013', 'Surya Roti'),
(1199, 'olahan', 'ID38475629384756014', 'Nutri Pangsit'),
(1200, 'olahan', 'ID38475629384756015', 'Makmur Lumpia'),
(1201, 'olahan', 'ID38475629384756016', 'Sedap Perkedel'),
(1202, 'olahan', 'ID38475629384756017', 'Ceria Tahu'),
(1203, 'olahan', 'ID38475629384756018', 'Nugraha Tempe'),
(1204, 'olahan', 'ID38475629384756019', 'Kentang Nusantara'),
(1205, 'olahan', 'ID38475629384756020', 'Sentosa Otak'),
(1206, 'olahan', 'ID38475629384756021', 'Mentari Bakwan'),
(1207, 'olahan', 'ID38475629384756022', 'Sayur Nugget'),
(1208, 'olahan', 'ID38475629384756023', 'Eka Lumpia'),
(1209, 'olahan', 'ID38475629384756024', 'Santai Tahu'),
(1210, 'olahan', 'ID38475629384756025', 'Wira Perkedel'),
(1211, 'olahan', 'ID38475629384756026', 'Bahagia Kentang'),
(1212, 'olahan', 'ID38475629384756027', 'Tugu Pangsit'),
(1213, 'olahan', 'ID38475629384756028', 'Rasa Nugget'),
(1214, 'olahan', 'ID38475629384756029', 'Prima Bakwan'),
(1215, 'olahan', 'ID38475629384756030', 'Santap Tempe'),
(1216, 'olahan', 'ID38475629384756031', 'Mega Lumpia'),
(1217, 'olahan', 'ID38475629384756032', 'Sehat Tahu'),
(1218, 'olahan', 'ID38475629384756033', 'Gemilang Otak'),
(1219, 'olahan', 'ID38475629384756034', 'Makmur Kentang'),
(1220, 'olahan', 'ID38475629384756035', 'Mandiri Tempe'),
(1221, 'olahan', 'ID38475629384756036', 'Sentra Bakwan'),
(1222, 'olahan', 'ID38475629384756037', 'Super Lumpia'),
(1223, 'olahan', 'ID38475629384756038', 'Sukses Tahu'),
(1224, 'olahan', 'ID38475629384756039', 'Gemah Nugget'),
(1225, 'olahan', 'ID38475629384756040', 'Harmoni Otak'),
(1226, 'olahan', 'ID38475629384756041', 'Mega Kentang'),
(1227, 'olahan', 'ID38475629384756042', 'Surya Tempe'),
(1228, 'olahan', 'ID38475629384756043', 'Tirta Pangsit'),
(1229, 'olahan', 'ID38475629384756044', 'Prima Perkedel'),
(1230, 'olahan', 'ID38475629384756045', 'Kencana Nugget'),
(1231, 'olahan', 'ID38475629384756046', 'Mentari Kentang'),
(1232, 'olahan', 'ID38475629384756047', 'Abadi Otak'),
(1233, 'olahan', 'ID38475629384756048', 'Mandala Lumpia'),
(1234, 'olahan', 'ID38475629384756049', 'Rasa Bakwan'),
(1235, 'olahan', 'ID38475629384756050', 'Alam Tahu'),
(1236, 'bumbu', 'ID47385629384756001', 'Royco'),
(1237, 'bumbu', 'ID47385629384756002', 'Masako'),
(1238, 'bumbu', 'ID47385629384756003', 'Knorr'),
(1239, 'bumbu', 'ID47385629384756004', 'Sasa'),
(1240, 'bumbu', 'ID47385629384756005', 'Ajinomoto'),
(1241, 'bumbu', 'ID47385629384756006', 'Indofood'),
(1242, 'bumbu', 'ID47385629384756007', 'Maggi'),
(1243, 'bumbu', 'ID47385629384756008', 'Kokita'),
(1244, 'bumbu', 'ID47385629384756009', 'Bango'),
(1245, 'bumbu', 'ID47385629384756010', 'Desaku'),
(1246, 'bumbu', 'ID47385629384756011', 'ABC'),
(1247, 'bumbu', 'ID47385629384756012', 'Bumbu Munik'),
(1248, 'bumbu', 'ID47385629384756013', 'Mama Suka'),
(1249, 'bumbu', 'ID47385629384756014', 'Saori'),
(1250, 'bumbu', 'ID47385629384756015', 'Lee Kum Kee'),
(1251, 'bumbu', 'ID47385629384756016', 'Ladaku'),
(1252, 'bumbu', 'ID47385629384756017', 'Kecap ABC'),
(1253, 'bumbu', 'ID47385629384756018', 'Sedaap'),
(1254, 'bumbu', 'ID47385629384756019', 'Filma'),
(1255, 'bumbu', 'ID47385629384756020', 'Golden Filma'),
(1256, 'bumbu', 'ID47385629384756021', 'Cap Ibu'),
(1257, 'bumbu', 'ID47385629384756022', 'Bon Cabe'),
(1258, 'bumbu', 'ID47385629384756023', 'Torabika'),
(1259, 'bumbu', 'ID47385629384756024', 'Super Bubur'),
(1260, 'bumbu', 'ID47385629384756025', 'Fortune'),
(1261, 'bumbu', 'ID47385629384756026', 'Mama Lemon'),
(1262, 'bumbu', 'ID47385629384756027', 'Masako Rasa Sapi'),
(1263, 'bumbu', 'ID47385629384756028', 'Royco Rasa Ayam'),
(1264, 'bumbu', 'ID47385629384756029', 'Sasa Tepung'),
(1265, 'bumbu', 'ID47385629384756030', 'ABC Saus Tomat'),
(1266, 'bumbu', 'ID47385629384756031', 'Bango Kecap Manis'),
(1267, 'bumbu', 'ID47385629384756032', 'Ladaku Merica Bubuk'),
(1268, 'bumbu', 'ID47385629384756033', 'Saori Saus Tiram'),
(1269, 'bumbu', 'ID47385629384756034', 'Kokita Bumbu Rendang'),
(1270, 'bumbu', 'ID47385629384756035', 'Sasa Sambal'),
(1271, 'bumbu', 'ID47385629384756036', 'Bumbu Munik Gulai'),
(1272, 'bumbu', 'ID47385629384756037', 'Lee Kum Kee Hoisin'),
(1273, 'bumbu', 'ID47385629384756038', 'Indofood Kaldu'),
(1274, 'bumbu', 'ID47385629384756039', 'Ajinomoto MSG'),
(1275, 'bumbu', 'ID47385629384756040', 'Maggi Saus Tomat'),
(1276, 'bumbu', 'ID47385629384756041', 'Golden Filma Minyak'),
(1277, 'bumbu', 'ID47385629384756042', 'Fortune Minyak'),
(1278, 'bumbu', 'ID47385629384756043', 'Mama Suka Sambal'),
(1279, 'bumbu', 'ID47385629384756044', 'Desaku Ketumbar'),
(1280, 'bumbu', 'ID47385629384756045', 'Bon Cabe Level 10'),
(1281, 'bumbu', 'ID47385629384756046', 'Mama Lemon Jeruk'),
(1282, 'bumbu', 'ID47385629384756047', 'Torabika Gula Aren'),
(1283, 'bumbu', 'ID47385629384756048', 'Super Bubur Ayam'),
(1284, 'bumbu', 'ID47385629384756049', 'Cap Ibu Kunyit'),
(1285, 'bumbu', 'ID47385629384756050', 'ABC Saus Sambal'),
(1286, 'santan', 'ID47385629384757001', 'Kara'),
(1287, 'santan', 'ID47385629384757002', 'Sasa'),
(1288, 'santan', 'ID47385629384757003', 'Cocomas'),
(1289, 'santan', 'ID47385629384757004', 'Ayam Brand'),
(1290, 'santan', 'ID47385629384757005', 'Sun Kara'),
(1291, 'santan', 'ID47385629384757006', 'Indomilk'),
(1292, 'santan', 'ID47385629384757007', 'Tropicana Slim'),
(1293, 'santan', 'ID47385629384757008', 'Prima Santan'),
(1294, 'santan', 'ID47385629384757009', 'Santari'),
(1295, 'santan', 'ID47385629384757010', 'Alamie'),
(1296, 'santan', 'ID47385629384757011', 'Pondan'),
(1297, 'santan', 'ID47385629384757012', 'Bamboe'),
(1298, 'santan', 'ID47385629384757013', 'Raja Rasa'),
(1299, 'santan', 'ID47385629384757014', 'Malio'),
(1300, 'santan', 'ID47385629384757015', 'Alami Santan'),
(1301, 'santan', 'ID47385629384757016', 'Cap Ayam'),
(1302, 'santan', 'ID47385629384757017', 'Segitiga Santan'),
(1303, 'santan', 'ID47385629384757018', 'Kukubima'),
(1304, 'santan', 'ID47385629384757019', 'Serambi'),
(1305, 'santan', 'ID47385629384757020', 'Del Monte'),
(1306, 'santan', 'ID47385629384757021', 'Tropicana'),
(1307, 'santan', 'ID47385629384757022', 'Kaldu Sari'),
(1308, 'santan', 'ID47385629384757023', 'Santan Nona'),
(1309, 'santan', 'ID47385629384757024', 'Segar Santan'),
(1310, 'santan', 'ID47385629384757025', 'Murni Santan'),
(1311, 'santan', 'ID47385629384757026', 'Fresh Santan'),
(1312, 'santan', 'ID47385629384757027', 'Coconut Dream'),
(1313, 'santan', 'ID47385629384757028', 'Santanku'),
(1314, 'santan', 'ID47385629384757029', 'Raja Santan'),
(1315, 'santan', 'ID47385629384757030', 'Super Santan'),
(1316, 'santan', 'ID47385629384757031', 'Organic Santan'),
(1317, 'santan', 'ID47385629384757032', 'Santan Kita'),
(1318, 'santan', 'ID47385629384757033', 'Eka Rasa'),
(1319, 'santan', 'ID47385629384757034', 'Cap Gunung'),
(1320, 'santan', 'ID47385629384757035', 'Mega Santan'),
(1321, 'santan', 'ID47385629384757036', 'Santan Jaya'),
(1322, 'santan', 'ID47385629384757037', 'Rasa Nusa'),
(1323, 'santan', 'ID47385629384757038', 'Tani Santan'),
(1324, 'santan', 'ID47385629384757039', 'Sari Kelapa'),
(1325, 'santan', 'ID47385629384757040', 'Coconut King'),
(1326, 'santan', 'ID47385629384757041', 'Santan Alam'),
(1327, 'santan', 'ID47385629384757042', 'Natur Santan'),
(1328, 'santan', 'ID47385629384757043', 'Go Santan'),
(1329, 'santan', 'ID47385629384757044', 'Kelapa Murni'),
(1330, 'santan', 'ID47385629384757045', 'Santan Asli'),
(1331, 'santan', 'ID47385629384757046', 'Fresh Coconut'),
(1332, 'santan', 'ID47385629384757047', 'Nata de Coco'),
(1333, 'santan', 'ID47385629384757048', 'Serba Santan'),
(1334, 'santan', 'ID47385629384757049', 'Sari Santan'),
(1335, 'santan', 'ID47385629384757050', 'Natur Fresh'),
(1386, 'sirup', 'ID47385629384758214', 'ABC'),
(1387, 'sirup', 'ID47385629384758215', 'Tropicana'),
(1388, 'sirup', 'ID47385629384758216', 'Kapal Api'),
(1389, 'sirup', 'ID47385629384758217', 'Fanta'),
(1390, 'sirup', 'ID47385629384758218', 'Hapi'),
(1391, 'sirup', 'ID47385629384758219', 'Frutang'),
(1392, 'sirup', 'ID47385629384758220', 'Teh Botol Sosro'),
(1393, 'sirup', 'ID47385629384758221', 'Sirop Buah Naga'),
(1394, 'sirup', 'ID47385629384758222', 'Teh Pucuk Harum'),
(1395, 'sirup', 'ID47385629384758223', 'Bintang'),
(1396, 'sirup', 'ID47385629384758224', 'Kraso'),
(1397, 'sirup', 'ID47385629384758225', 'Pocari Sweat'),
(1398, 'sirup', 'ID47385629384758226', 'Alpina'),
(1399, 'sirup', 'ID47385629384758227', 'Herbadrink'),
(1400, 'sirup', 'ID47385629384758228', 'Le Minerale'),
(1401, 'sirup', 'ID47385629384758229', 'Susu Sapi Segar'),
(1402, 'sirup', 'ID47385629384758230', 'Milku'),
(1403, 'sirup', 'ID47385629384758231', 'Mizone'),
(1404, 'sirup', 'ID47385629384758232', 'Botani'),
(1405, 'sirup', 'ID47385629384758233', 'Vita Lemon'),
(1406, 'sirup', 'ID47385629384758234', 'Vitacoco'),
(1407, 'sirup', 'ID47385629384758235', 'Frosty'),
(1408, 'sirup', 'ID47385629384758236', 'Rara'),
(1409, 'sirup', 'ID47385629384758237', 'Sidikalang'),
(1410, 'sirup', 'ID47385629384758238', 'Sodaku'),
(1411, 'sirup', 'ID47385629384758239', 'Sirup ABC'),
(1412, 'sirup', 'ID47385629384758240', 'Ceres'),
(1413, 'sirup', 'ID47385629384758241', 'Hortico'),
(1414, 'sirup', 'ID47385629384758242', 'Fiesta'),
(1415, 'sirup', 'ID47385629384758243', 'Cap Naga Resto'),
(1416, 'sirup', 'ID47385629384758244', 'Ola'),
(1417, 'sirup', 'ID47385629384758245', 'Le Femme'),
(1418, 'sirup', 'ID47385629384758246', 'Sirup Buah Apel'),
(1419, 'sirup', 'ID47385629384758247', 'Nescafe'),
(1420, 'sirup', 'ID47385629384758248', 'Teh Hijau'),
(1421, 'sirup', 'ID47385629384758249', 'Kecap Manis'),
(1422, 'sirup', 'ID47385629384758250', 'Choco Mint'),
(1423, 'sirup', 'ID47385629384758251', 'Ming Gao'),
(1424, 'sirup', 'ID47385629384758252', 'Gogo Drink'),
(1425, 'sirup', 'ID47385629384758253', 'Aqua Sirup'),
(1426, 'sirup', 'ID47385629384758254', 'Minuman Serut'),
(1427, 'sirup', 'ID47385629384758255', 'Vimto'),
(1428, 'sirup', 'ID47385629384758256', 'Tropicana Fruit'),
(1429, 'sirup', 'ID47385629384758257', 'Sehat Fresh'),
(1430, 'sirup', 'ID47385629384758258', 'Pinnacle'),
(1431, 'sirup', 'ID47385629384758259', 'Senzuri'),
(1432, 'sirup', 'ID47385629384758260', 'Aqua Twist'),
(1433, 'sirup', 'ID47385629384758261', 'Fresh Drink'),
(1434, 'sirup', 'ID47385629384758262', 'Taro Sirup'),
(1435, 'sirup', 'ID47385629384758263', 'SodaJeruk'),
(1436, 'garam', 'ID57392628473621955', 'ABC Salt'),
(1437, 'garam', 'ID57392628473621956', 'Himalaya Salt'),
(1438, 'garam', 'ID57392628473621957', 'Cendrawasih Salt'),
(1439, 'garam', 'ID57392628473621958', 'Tata Salt'),
(1440, 'garam', 'ID57392628473621959', 'Sasa Salt'),
(1441, 'garam', 'ID57392628473621960', 'Seasalt'),
(1442, 'garam', 'ID57392628473621961', 'Granny Salt'),
(1443, 'garam', 'ID57392628473621962', 'Super Salt'),
(1444, 'garam', 'ID57392628473621963', 'Bali Salt'),
(1445, 'garam', 'ID57392628473621964', 'Garam Dapur ABC'),
(1446, 'garam', 'ID57392628473621965', 'Garam Purity'),
(1447, 'garam', 'ID57392628473621966', 'Ocean Salt'),
(1448, 'garam', 'ID57392628473621967', 'Kris Salt'),
(1449, 'garam', 'ID57392628473621968', 'Garam Bumbu Sejahtera'),
(1450, 'garam', 'ID57392628473621969', 'Saltica'),
(1451, 'garam', 'ID57392628473621970', 'Garam Ria'),
(1452, 'garam', 'ID57392628473621971', 'Garam Bali Segar'),
(1453, 'garam', 'ID57392628473621972', 'Premium Salt'),
(1454, 'garam', 'ID57392628473621973', 'Garam Laut'),
(1455, 'garam', 'ID57392628473621974', 'Lautan Garam'),
(1456, 'garam', 'ID57392628473621975', 'Sumber Garam'),
(1457, 'garam', 'ID57392628473621976', 'Salt Purifier'),
(1458, 'garam', 'ID57392628473621977', 'Garam Deli'),
(1459, 'garam', 'ID57392628473621978', 'Horizon Salt'),
(1460, 'garam', 'ID57392628473621979', 'Sinar Garam'),
(1461, 'garam', 'ID57392628473621980', 'Saltmax'),
(1462, 'garam', 'ID57392628473621981', 'Garam Merah'),
(1463, 'garam', 'ID57392628473621982', 'Garam Udang'),
(1464, 'garam', 'ID57392628473621983', 'Garam Halus'),
(1465, 'garam', 'ID57392628473621984', 'Garam Laut Segar'),
(1466, 'garam', 'ID57392628473621985', 'Garam Halus Sejahtera'),
(1467, 'garam', 'ID57392628473621986', 'Sumber Laut'),
(1468, 'garam', 'ID57392628473621987', 'Garam Kristal'),
(1469, 'garam', 'ID57392628473621988', 'Garam Fit'),
(1470, 'garam', 'ID57392628473621989', 'Garam Laut Himalaya'),
(1471, 'garam', 'ID57392628473621990', 'Garam Citra'),
(1472, 'garam', 'ID57392628473621991', 'Garam Putih'),
(1473, 'garam', 'ID57392628473621992', 'Saka Salt'),
(1474, 'garam', 'ID57392628473621993', 'Garam Oasis'),
(1475, 'garam', 'ID57392628473621994', 'Dewi Garam'),
(1476, 'garam', 'ID57392628473621995', 'Garam Merah Tepi Laut'),
(1477, 'garam', 'ID57392628473621996', 'Minyak Garam'),
(1478, 'garam', 'ID57392628473621997', 'Garam Cendana'),
(1479, 'garam', 'ID57392628473621998', 'Sun Garam'),
(1480, 'garam', 'ID57392628473621999', 'Toshiba Salt'),
(1481, 'garam', 'ID57392628473622000', 'Sumber Sejahtera'),
(1482, 'garam', 'ID57392628473622001', 'Mega Garam'),
(1483, 'saus', 'ID83654982736415266', 'ABC Sauce'),
(1484, 'saus', 'ID83654982736415267', 'Del Monte Sauce'),
(1485, 'saus', 'ID83654982736415268', 'Sasa Sauce'),
(1486, 'saus', 'ID83654982736415269', 'Kikkoman Sauce'),
(1487, 'saus', 'ID83654982736415270', 'Heinz Ketchup'),
(1488, 'saus', 'ID83654982736415271', 'Mama Suka'),
(1489, 'saus', 'ID83654982736415272', 'Worcestershire Sauce'),
(1490, 'saus', 'ID83654982736415273', 'Prego Tomato Sauce'),
(1491, 'saus', 'ID83654982736415274', 'La Fonte Sauces'),
(1492, 'saus', 'ID83654982736415275', 'Tio Ciu Sauce'),
(1493, 'saus', 'ID83654982736415276', 'Bango Soy Sauce'),
(1494, 'saus', 'ID83654982736415277', 'Sambal ABC'),
(1495, 'saus', 'ID83654982736415278', 'Tata Tomato Sauce'),
(1496, 'saus', 'ID83654982736415279', 'Chili Sauce ABC'),
(1497, 'saus', 'ID83654982736415280', 'Sweet Soy Sauce Kecap Manis'),
(1498, 'saus', 'ID83654982736415281', 'Maggie Chili Sauce'),
(1499, 'saus', 'ID83654982736415282', 'Gochujang Sauce'),
(1500, 'saus', 'ID83654982736415283', 'Lao Gan Ma Sauce'),
(1501, 'saus', 'ID83654982736415284', 'Betty Crocker Sauce'),
(1502, 'saus', 'ID83654982736415285', 'Shoyu Sauce Kikkoman'),
(1503, 'saus', 'ID83654982736415286', 'Sriracha Hot Sauce'),
(1504, 'saus', 'ID83654982736415287', 'Lee Kum Kee Soy Sauce'),
(1505, 'saus', 'ID83654982736415288', 'Sambal Terasi ABC'),
(1506, 'saus', 'ID83654982736415289', 'Soy Sauce ABC'),
(1507, 'saus', 'ID83654982736415290', 'Tapatio Hot Sauce'),
(1508, 'saus', 'ID83654982736415291', 'Garam Masala Sauce'),
(1509, 'saus', 'ID83654982736415292', 'Chili Garlic Sauce'),
(1510, 'saus', 'ID83654982736415293', 'Ranch Dressing Sauce'),
(1511, 'saus', 'ID83654982736415294', 'Lime Sauce'),
(1512, 'saus', 'ID83654982736415295', 'Mustard Sauce Heinz'),
(1513, 'saus', 'ID83654982736415296', 'Barbecue Sauce Kraft'),
(1514, 'saus', 'ID83654982736415297', 'Teriyaki Sauce Kikkoman'),
(1515, 'saus', 'ID83654982736415298', 'Pineapple Sauce'),
(1516, 'saus', 'ID83654982736415299', 'Hoisin Sauce'),
(1517, 'saus', 'ID83654982736415300', 'Maple Syrup Sauce'),
(1518, 'saus', 'ID83654982736415301', 'Tahini Sauce'),
(1519, 'saus', 'ID83654982736415302', 'Saus Caesar'),
(1520, 'saus', 'ID83654982736415303', 'Chili Pepper Sauce'),
(1521, 'saus', 'ID83654982736415304', 'Miso Sauce'),
(1522, 'saus', 'ID83654982736415305', 'Saus Pesto'),
(1523, 'saus', 'ID83654982736415306', 'Tartar Sauce'),
(1524, 'saus', 'ID83654982736415307', 'French Fries Sauce'),
(1525, 'keju', 'ID97364250173922987', 'Indofood Keju'),
(1526, 'keju', 'ID97364250173922988', 'Kraft Cheddar'),
(1527, 'keju', 'ID97364250173922989', 'Bega Cheese'),
(1528, 'keju', 'ID97364250173922990', 'Cheddar Gold'),
(1529, 'keju', 'ID97364250173922991', 'Dairy Farm'),
(1530, 'keju', 'ID97364250173922992', 'Kerrygold Butter'),
(1531, 'keju', 'ID97364250173922993', 'La Vache Qui Rit'),
(1532, 'keju', 'ID97364250173922994', 'Cottage Cheese'),
(1533, 'keju', 'ID97364250173922995', 'Green Valley Creamery'),
(1534, 'keju', 'ID97364250173922996', 'Emmental Cheese'),
(1535, 'keju', 'ID97364250173922997', 'Gorgonzola Cheese'),
(1536, 'keju', 'ID97364250173922998', 'Mozzarella Cheese'),
(1537, 'keju', 'ID97364250173922999', 'Parmesan Cheese'),
(1538, 'keju', 'ID97364250173923000', 'Cream Cheese Philadelphia'),
(1539, 'keju', 'ID97364250173923001', 'Coon Cheese'),
(1540, 'keju', 'ID97364250173923002', 'Edam Cheese'),
(1541, 'keju', 'ID97364250173923003', 'Feta Cheese'),
(1542, 'keju', 'ID97364250173923004', 'Ricotta Cheese'),
(1543, 'keju', 'ID97364250173923005', 'Cheese Cream Cowshed'),
(1544, 'keju', 'ID97364250173923006', 'Swiss Cheese'),
(1545, 'keju', 'ID97364250173923007', 'Tilsit Cheese'),
(1546, 'keju', 'ID97364250173923008', 'Havarti Cheese'),
(1547, 'keju', 'ID97364250173923009', 'Brie Cheese'),
(1548, 'keju', 'ID97364250173923010', 'Gouda Cheese'),
(1549, 'keju', 'ID97364250173923011', 'Blue Cheese'),
(1550, 'keju', 'ID97364250173923012', 'Asiago Cheese'),
(1551, 'keju', 'ID97364250173923013', 'Queso Fresco'),
(1552, 'keju', 'ID97364250173923014', 'Boursin Cheese'),
(1553, 'keju', 'ID97364250173923015', 'Provolone Cheese'),
(1554, 'keju', 'ID97364250173923016', 'String Cheese'),
(1555, 'keju', 'ID97364250173923017', 'Gouda Cheese Smoked'),
(1556, 'keju', 'ID97364250173923018', 'Montasio Cheese'),
(1557, 'keju', 'ID97364250173923019', 'Halloumi Cheese'),
(1558, 'keju', 'ID97364250173923020', 'Cheddar Cheese Slices'),
(1559, 'keju', 'ID97364250173923021', 'Double Gloucester'),
(1560, 'keju', 'ID97364250173923022', 'Muenster Cheese'),
(1561, 'keju', 'ID97364250173923023', 'Manchego Cheese'),
(1562, 'keju', 'ID97364250173923024', 'Lancashire Cheese'),
(1563, 'keju', 'ID97364250173923025', 'Ricotta Salata Cheese'),
(1564, 'gula', 'ID83654982736415308', 'Gulaku'),
(1565, 'tepung', 'ID83654982736415309', 'Sasa Tepung'),
(1566, 'susu', 'ID83654982736415310', 'Ultra Milk'),
(1567, 'daging', 'ID83654982736415311', 'Ayam Goreng Suharti'),
(1568, 'bumbu', 'ID83654982736415312', 'Bumbu Rames'),
(1569, 'santan', 'ID83654982736415313', 'Kara Santan'),
(1570, 'sirup', 'ID83654982736415314', 'ABC Sirup'),
(1571, 'garam', 'ID83654982736415315', 'Garam Kapal Selam'),
(1572, 'saus', 'ID83654982736415316', 'Kecap Bango'),
(1573, 'keju', 'ID83654982736415317', 'Kraft Cheddar'),
(1574, 'gula', 'ID83654982736415318', 'Gulaku Premium'),
(1575, 'tepung', 'ID83654982736415319', 'Tepung Beras Cap Masako'),
(1576, 'susu', 'ID83654982736415320', 'Dancow'),
(1577, 'daging', 'ID83654982736415321', 'So Good'),
(1578, 'bumbu', 'ID83654982736415322', 'Bumbu Bali'),
(1579, 'santan', 'ID83654982736415323', 'Santan Tetra'),
(1580, 'sirup', 'ID83654982736415324', 'Teh Botol Sosro Sirup'),
(1581, 'garam', 'ID83654982736415325', 'Garam Layar'),
(1582, 'saus', 'ID83654982736415326', 'Del Monte'),
(1583, 'keju', 'ID83654982736415327', 'Cheddar Bega'),
(1584, 'gula', 'ID83654982736415328', 'Gulaku Kelapa'),
(1585, 'tepung', 'ID83654982736415329', 'Tepung Maizena'),
(1586, 'susu', 'ID83654982736415330', 'Bear Brand'),
(1587, 'daging', 'ID83654982736415331', 'Indoguna'),
(1588, 'bumbu', 'ID83654982736415332', 'Bumbu Nasi Goreng ABC'),
(1589, 'santan', 'ID83654982736415333', 'Santan Vita Coco'),
(1590, 'sirup', 'ID83654982736415334', 'Sambel Terasi ABC'),
(1591, 'garam', 'ID83654982736415335', 'Garam Purin'),
(1592, 'saus', 'ID83654982736415336', 'Tio Ciu'),
(1593, 'keju', 'ID83654982736415337', 'Mozzarella Queso'),
(1594, 'gula', 'ID83654982736415338', 'Gula Merah Tropis'),
(1595, 'tepung', 'ID83654982736415339', 'Tepung Jagung Cap Masako'),
(1596, 'susu', 'ID83654982736415340', 'Anlene'),
(1597, 'daging', 'ID83654982736415341', 'Sapi Murni'),
(1598, 'bumbu', 'ID83654982736415342', 'Bumbu Gulai'),
(1599, 'santan', 'ID83654982736415343', 'Coconut Milk Tetra'),
(1600, 'sirup', 'ID83654982736415344', 'DHT Sirup'),
(1601, 'garam', 'ID83654982736415345', 'Garam Tabur'),
(1602, 'saus', 'ID83654982736415346', 'Bango Kecap Manis'),
(1603, 'keju', 'ID83654982736415347', 'Havarti Cream Cheese'),
(1604, 'gula', 'ID83654982736415348', 'Gulaku Pasir'),
(1605, 'tepung', 'ID83654982736415349', 'Tepung Beras Cap Tiga Roda'),
(1606, 'susu', 'ID83654982736415350', 'Fresh Milk Ultramilk'),
(1607, 'daging', 'ID83654982736415351', 'Ayam Goreng Lengkuas'),
(1608, 'bumbu', 'ID83654982736415352', 'Bumbu Semur'),
(1609, 'santan', 'ID83654982736415353', 'Kara'),
(1610, 'sirup', 'ID83654982736415354', 'Fanta Sirup'),
(1611, 'garam', 'ID83654982736415355', 'Garam Roti'),
(1612, 'saus', 'ID83654982736415356', 'Kraft Thousand Island'),
(1613, 'keju', 'ID83654982736415357', 'Bega Cheddar Block'),
(1614, 'gula', 'ID83654982736415358', 'Gula Kelapa Pasir'),
(1615, 'tepung', 'ID83654982736415359', 'Tepung Terigu Cakra Kembar'),
(1616, 'susu', 'ID83654982736415360', 'Tetra Milk'),
(1617, 'daging', 'ID83654982736415361', 'Burger Daging'),
(1618, 'bumbu', 'ID83654982736415362', 'Bumbu Padang'),
(1619, 'santan', 'ID83654982736415363', 'Coconut Powder'),
(1620, 'sirup', 'ID83654982736415364', 'Sirup Marjan'),
(1621, 'garam', 'ID83654982736415365', 'Garam Lauk'),
(1622, 'saus', 'ID83654982736415366', 'Barbecue Kraft'),
(1623, 'keju', 'ID83654982736415367', 'Cheddar Keju Selera'),
(1624, 'gula', 'ID83654982736415368', 'Gulaku Crystal'),
(1625, 'tepung', 'ID83654982736415369', 'Tepung Terigu Jakarta'),
(1626, 'susu', 'ID83654982736415370', 'Vermilion'),
(1627, 'daging', 'ID83654982736415371', 'Kacang Daging'),
(1628, 'bumbu', 'ID83654982736415372', 'Bumbu Pecel'),
(1629, 'santan', 'ID83654982736415373', 'Organic Coconut Milk'),
(1630, 'sirup', 'ID83654982736415374', 'Delmonte Syrup'),
(1631, 'garam', 'ID83654982736415375', 'Garam Dapur Murni'),
(1632, 'saus', 'ID83654982736415376', 'Saus Sambal ABC'),
(1633, 'keju', 'ID83654982736415377', 'Cream Cheese Philly'),
(1634, 'gula', 'ID83654982736415378', 'Gulaku Raw'),
(1635, 'tepung', 'ID83654982736415379', 'Tepung Singkong'),
(1636, 'susu', 'ID83654982736415380', 'Moo Milk'),
(1637, 'daging', 'ID83654982736415381', 'Meat Fresh'),
(1638, 'bumbu', 'ID83654982736415382', 'Bumbu Mie Goreng'),
(1639, 'santan', 'ID83654982736415383', 'Santan Cap Masak'),
(1640, 'sirup', 'ID83654982736415384', 'Mango Sirup'),
(1641, 'garam', 'ID83654982736415385', 'Garam Cekat'),
(1642, 'saus', 'ID83654982736415386', 'Saus Rujak ABC'),
(1643, 'keju', 'ID83654982736415387', 'Cheddar Chesse'),
(1644, 'gula', 'ID83654982736415388', 'Sugar Cane'),
(1645, 'tepung', 'ID83654982736415389', 'Tepung Ketan Cap Bunga'),
(1646, 'susu', 'ID83654982736415390', 'Goat Milk Powder'),
(1647, 'daging', 'ID83654982736415391', 'Beef Steaks'),
(1648, 'bumbu', 'ID83654982736415392', 'Bumbu Tongseng'),
(1649, 'santan', 'ID83654982736415393', 'Santan Mentega'),
(1650, 'sirup', 'ID83654982736415394', 'Kerisik Sirup'),
(1651, 'garam', 'ID83654982736415395', 'Garam Kosher'),
(1652, 'saus', 'ID83654982736415396', 'Spicy Barbecue Sauce'),
(1653, 'keju', 'ID83654982736415397', 'Swiss Cheese Gourmet');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` mediumint(5) NOT NULL,
  `email_user` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_user` mediumtext NOT NULL,
  `pass_user` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_toko` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi_toko` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_user` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `email_user`, `nama_user`, `pass_user`, `nama_toko`, `deskripsi_toko`, `foto_user`) VALUES
(1, 'wahabinasrul@gmail.com ', 'M Nasrul Wahabi', 'sibeHBQ342169', 'Habiqi.Shop', 'Gramedia Official Store Menyediakan Buku-buku Asli dan Berkualitas. Toko ini buka setiap hari Senin - Sabtu (Pukul 09.00 -16:30 WIB)', 'https://sibeux.my.id/images/sibe.png'),
(9, 'sibesibe86@gmail.com', 'Sibeux', '$2y$10$tPQwbnID1BsdNcKxv92MPus2zngBmCGPmGFiyzweYlnPsttGkUkvu', NULL, NULL, NULL),
(23, 'a@gmail.com', 'Akmal Satria Kadhafis', '$2y$10$Wj/y.ccjPHUX.DLhk9s5uucSdrTfojPyOvLzJCQAW9fDIVizhCexC', 'IndoFoods', NULL, 'https://sibeux.my.id/project/sihalal/uploads/profile_35dcb8e5-6690-44fb-8bfe-6df760603cfd.jpg'),
(28, 'b@gmail.com', 'Brian Tan William', '$2y$10$/zlN4KYkLA/a.gghmsCoaukHSLoyAbQuuy1FFr8eYnYrYWYSwc4da', NULL, NULL, 'https://sibeux.my.id/project/sihalal/uploads/profile_5fce29c0-9854-403b-b420-ff245ef8f72c.jpg'),
(29, 'c@gmail.com', 'Chandra Bintang Wjiaya', '$2y$10$gQ31E14OdmFOqNWnapbgpOwX2xjBnFmZQDIpPn0ATy.FjkJGHJUde', 'C20 Baker\'s Warehouse', NULL, 'https://sibeux.my.id/project/sihalal/uploads/profile_580f9801-1c51-4e36-9b04-0957332f6e13.jpg'),
(30, 'samasama@yahoo.com', 'Sama Sama', '$2y$10$5kveGMU8MsHzKlyPcRPkDeGyneI3etP3sEeuL4mNn7W.VeC34DfrW', NULL, NULL, NULL),
(31, 'gamermanoj00@gmail.com', 'manoj', '$2y$10$/Rrt8HKUsEIrYQFzNg8iS.GEozSINKEhFFoLfSznuKYZjhKZc5ILC', NULL, NULL, NULL),
(32, 'mjafarbaihaqi@gmail.com', 'M Jafar Baihaqi', '$2y$10$BO75pK2XlHNwpT.Kji7U7.yGoD2BWMHA0RnZo1oC3tHqc1OB.45he', NULL, NULL, 'https://sibeux.my.id/project/sihalal/uploads/profile_1c85651f-e7f5-4052-a3f0-e2c95249152e.jpg'),
(33, 'd@gmail.com', 'd', '$2y$10$SZiky.82iVsb6UBNa6sOh.QAljR7FO9pOuh7RRHeLn66CPndzjoWG', NULL, NULL, NULL),
(34, 'g@gmail.com', 'Gerrald Wijaya', '$2y$10$sSDO4w087HAVcDtESQJTVe75pktTn5pGH/ufKYAcilyoxi6vXCFQ.', NULL, NULL, 'https://sibeux.my.id/project/sihalal/uploads/profile_3bc42504-f23c-45a4-b4f5-78890363c6cb.jpg'),
(35, 'v@gmail.com', 'Vins', '$2y$10$Zokc56IQ.2KlOK8TPW4VvOrIjjrMLavDGSoWoBUZ9szQzLdvrm6Ae', NULL, NULL, NULL),
(36, 'bajabs@hotmail.com', 'hsbsinss', '$2y$10$0f2Qy2GopRAUMg33ArSwKukJIUP3ewy5WqWOtZOA1Vbgidvd8jL7S', NULL, NULL, NULL),
(37, '67yytym@hotmail.com', 'fyhyfyhyf', '$2y$10$8u8QMV4KSXPUOmyfHoXcbuI.dpIREKZvBTwnXTohmYRdlAQzDcGmO', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alamat`
--
ALTER TABLE `alamat`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `Delete Alamat by User` (`id_user`);

--
-- Indexes for table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id_favorite`),
  ADD KEY `Delete Favorite by Produk` (`id_produk`) USING BTREE,
  ADD KEY `Delete Favorite by User` (`id_user`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `Delete Cart by Procut` (`id_produk`),
  ADD KEY `Delete Cart by User` (`id_user`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `no_pesanan` (`no_pesanan`) USING HASH;

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `Delete Produk by User` (`id_user`);
ALTER TABLE `produk` ADD FULLTEXT KEY `nama_produk` (`nama_produk`,`deskripsi_produk`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id_rating`),
  ADD KEY `Delete Rating by Produk` (`id_produk`) USING BTREE,
  ADD KEY `Delete Rating by User` (`id_user`);

--
-- Indexes for table `shhalal`
--
ALTER TABLE `shhalal`
  ADD PRIMARY KEY (`id_shhalal`),
  ADD UNIQUE KEY `Nomor SH Halal` (`nomor_shhalal`) USING HASH;
ALTER TABLE `shhalal` ADD FULLTEXT KEY `kategori_shhalal` (`kategori_shhalal`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email_user` (`email_user`) USING HASH;
ALTER TABLE `user` ADD FULLTEXT KEY `nama_toko` (`nama_toko`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alamat`
--
ALTER TABLE `alamat`
  MODIFY `id_alamat` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id_favorite` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `shhalal`
--
ALTER TABLE `shhalal`
  MODIFY `id_shhalal` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1654;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` mediumint(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alamat`
--
ALTER TABLE `alamat`
  ADD CONSTRAINT `Delete Alamat by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `Delete Favorite by Produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `Delete Favorite by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `Delete Cart by Procut` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `Delete Cart by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `Delete Produk by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `Delete Rating by Produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `Delete Rating by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
