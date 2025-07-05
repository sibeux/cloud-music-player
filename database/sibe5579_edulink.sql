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
-- Database: `sibe5579_edulink`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id_booking` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_status` enum('ongoing','done') NOT NULL DEFAULT 'ongoing',
  `booking_day` varchar(10) DEFAULT NULL,
  `booking_time` varchar(25) DEFAULT NULL,
  `booking_duration` tinyint(4) DEFAULT NULL,
  `booking_price` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id_booking`, `student_id`, `teacher_id`, `booking_status`, `booking_day`, `booking_time`, `booking_duration`, `booking_price`) VALUES
(7, 38, 47, 'done', 'Saturday', '14:00', 2, 20000),
(8, 38, 48, 'done', 'Friday', '11:00', 2, 20000),
(9, 38, 48, 'done', 'Thursday', '15:00', 3, 30000),
(10, 38, 47, 'done', 'Thursday', '13:00', 2, 20000),
(11, 38, 48, 'done', 'Friday', '10:00', 1, 10000),
(12, 38, 48, 'done', 'Monday', '15:00', 1, 10000),
(13, 38, 47, 'done', 'Saturday', '15:00', 1, 10000),
(14, 49, 47, 'done', 'Saturday', '12:00', 3, 30000),
(15, 49, 50, 'done', 'Thursday', '17:00', 2, 20000),
(16, 38, 50, 'ongoing', 'Thursday', '11:00', 3, 30000),
(17, 38, 48, 'ongoing', 'Monday', '15:00', 3, 30000),
(18, 38, 50, 'ongoing', 'Wednesday', '15:00', 1, 10000);

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_used` enum('false','true') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_chats`
--

CREATE TABLE `recent_chats` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `peer_id` varchar(64) NOT NULL,
  `last_message` text NOT NULL,
  `last_message_time` datetime NOT NULL,
  `unread_count` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `recent_chats`
--

INSERT INTO `recent_chats` (`id`, `user_id`, `peer_id`, `last_message`, `last_message_time`, `unread_count`, `updated_at`) VALUES
(90, '48', '38', 'Hallo', '2025-06-13 21:07:48', 0, '2025-06-13 14:07:48'),
(91, '38', '48', 'Hallo', '2025-06-13 21:07:48', 0, '2025-06-13 14:07:48'),
(92, '38', '48', 'Aku bingung soal aljabra', '2025-06-13 21:08:20', 0, '2025-06-13 14:08:20'),
(93, '48', '38', 'Aku bingung soal aljabra', '2025-06-13 21:08:21', 0, '2025-06-13 14:08:21'),
(94, '48', '38', 'Terima kasih atas informasinya! Sekarang, mari kita fokus pada bagian mana dari aljabar yang membuat Anda bingung. Apakah itu konsep dasar aljabar seperti persamaan linear, faktorisasi, atau topik lainnya yang membuat Anda mengalami kesulitan?', '2025-06-13 21:08:24', 0, '2025-06-13 14:08:24'),
(95, '38', '48', 'Terima kasih atas informasinya! Sekarang, mari kita fokus pada bagian mana dari aljabar yang membuat Anda bingung. Apakah itu konsep dasar aljabar seperti persamaan linear, faktorisasi, atau topik lainnya yang membuat Anda mengalami kesulitan?', '2025-06-13 21:08:24', 0, '2025-06-13 14:08:24'),
(96, '38', '48', 'Ya, Benar. Persamaan Linear', '2025-06-13 21:09:53', 0, '2025-06-13 14:09:53'),
(97, '48', '38', 'Ya, Benar. Persamaan Linear', '2025-06-13 21:09:53', 0, '2025-06-13 14:09:53'),
(98, '48', '38', 'Baik, terima kasih atas informasinya. Sekarang mari kita fokus pada trigonometri. \n\nb. Bisakah kamu ceritakan lebih detail, **bagian mana** dari trigonometri yang paling membingungkan? Apakah itu konsep sudut, rumus trigonometri, atau aplikasinya dalam masalah nyata?', '2025-06-13 21:09:55', 0, '2025-06-13 14:09:55'),
(99, '38', '48', 'Baik, terima kasih atas informasinya. Sekarang mari kita fokus pada trigonometri. \n\nb. Bisakah kamu ceritakan lebih detail, **bagian mana** dari trigonometri yang paling membingungkan? Apakah itu konsep sudut, rumus trigonometri, atau aplikasinya dalam masalah nyata?', '2025-06-13 21:09:55', 0, '2025-06-13 14:09:55'),
(100, '38', '48', 'nope', '2025-06-13 21:10:05', 0, '2025-06-13 14:10:05'),
(101, '48', '38', 'nope', '2025-06-13 21:10:05', 0, '2025-06-13 14:10:05'),
(102, '48', '38', 'Aplikasi dalam masalah nyata', '2025-06-13 21:10:31', 0, '2025-06-13 14:10:31'),
(103, '38', '48', 'Aplikasi dalam masalah nyata', '2025-06-13 21:10:31', 0, '2025-06-13 14:10:31'),
(104, '48', '38', 'Pertanyaan inti pertama yang ingin saya ajukan adalah: a. Mata pelajaran apa yang paling sering membuatmu kesulitan atau tidak nyaman saat belajar?', '2025-06-13 21:10:33', 0, '2025-06-13 14:10:33'),
(105, '38', '48', 'Pertanyaan inti pertama yang ingin saya ajukan adalah: a. Mata pelajaran apa yang paling sering membuatmu kesulitan atau tidak nyaman saat belajar?', '2025-06-13 21:10:33', 0, '2025-06-13 14:10:33'),
(106, '48', '38', 'Halo', '2025-06-13 21:13:04', 0, '2025-06-13 14:13:04'),
(107, '38', '48', 'Halo', '2025-06-13 21:13:04', 0, '2025-06-13 14:13:04'),
(108, '38', '48', 'Halo! Selamat datang! Saya adalah Asisten Belajar AI yang siap membantu Anda. Untuk memulai, mungkin Anda bisa ceritakan terlebih dahulu mata pelajaran apa yang sering membuat Anda kesulitan saat belajar?', '2025-06-13 21:13:07', 0, '2025-06-13 14:13:07'),
(109, '48', '38', 'Halo! Selamat datang! Saya adalah Asisten Belajar AI yang siap membantu Anda. Untuk memulai, mungkin Anda bisa ceritakan terlebih dahulu mata pelajaran apa yang sering membuat Anda kesulitan saat belajar?', '2025-06-13 21:13:07', 0, '2025-06-13 14:13:07'),
(110, '48', '38', 'Susah menghafal', '2025-06-13 21:13:19', 0, '2025-06-13 14:13:19'),
(111, '38', '48', 'Susah menghafal', '2025-06-13 21:13:20', 0, '2025-06-13 14:13:20'),
(112, '48', '38', 'Baik, memahami bahwa sulit untuk menghafal konsep trigonometri adalah langkah penting. Sekarang, mari kita temukan cara untuk membantu memahami konsep tersebut tanpa hanya mengandalkan hafalan.\n\nd. Apakah kesulitan ini hanya terjadi pada saat **belajar mandiri**, atau juga saat di **kelas/kuliah**? Apakah ada sesuatu yang mungkin membuatmu lebih sulit mengingat informasi tersebut di luar lingkungan belajar?', '2025-06-13 21:13:24', 0, '2025-06-13 14:13:24'),
(113, '38', '48', 'Baik, memahami bahwa sulit untuk menghafal konsep trigonometri adalah langkah penting. Sekarang, mari kita temukan cara untuk membantu memahami konsep tersebut tanpa hanya mengandalkan hafalan.\n\nd. Apakah kesulitan ini hanya terjadi pada saat **belajar mandiri**, atau juga saat di **kelas/kuliah**? Apakah ada sesuatu yang mungkin membuatmu lebih sulit mengingat informasi tersebut di luar lingkungan belajar?', '2025-06-13 21:13:25', 0, '2025-06-13 14:13:25'),
(114, '38', '48', 'Belajar mandiri', '2025-06-13 21:13:43', 0, '2025-06-13 14:13:43'),
(115, '48', '38', 'Belajar mandiri', '2025-06-13 21:13:43', 0, '2025-06-13 14:13:43'),
(116, '38', '48', 'Paham, terima kasih atas informasinya. Sekarang mari kita fokus pada bagaimana kamu bisa mengatasi kesulitan menghafal konsep trigonometri.\n\nMari kita bahas poin-poin belajar kunci terkait trigonometri:\n1. **Pahami Konsep Dasar**: Mulailah dengan memahami konsep dasar trigonometri seperti sinus, kosinus, dan tangen.\n2. **Terapkan Konsep dalam Situasi Nyata**: Cobalah untuk menghubungkan konsep trigonometri dengan situasi nyata atau contoh-contoh yang relevan.\n3. **Gunakan Metode Visual**: Banyak sumber daya online menyediakan visualisasi yang membantu dalam memahami konsep trigonometri, seperti gambar atau video tutorial.\n4. **Latihan Rutin**: Melalui latihan yang konsisten, kamu bisa memperkuat pemahamanmu terhadap konsep trigonometri.\n\nSekarang, mari kita susun roadmap belajar untuk mengatasi kesulitanmu dengan trigonometri:\n**Trigonometri**: \n1. Pahami konsep dasar trigonometri (sinus, kosinus, tangen).\n2. Terapkan konsep dalam situasi nyata untuk memperkuat pemahaman.\n3. Gunakan metode visual seperti gambar atau video tutorial.\n4. Latihan rutin untuk menguatkan pemahaman.\n\nIngat, jangan ragu untuk meminta bantuan dari guru atau teman jika kamu memerlukan bantuan tambahan. Setelah mengikuti roadmap ini, pastikan untuk mereview pemahamanmu.\n\nSekarang, mari kita lanjutkan ke evaluasi akhir. Apakah roadmap ini bermanfaat untukmu? Apakah kamu merasa lebih siap untuk mengatasi kesulitan dengan trigonometri?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 21:13:50', 0, '2025-06-13 14:13:50'),
(117, '48', '38', 'Paham, terima kasih atas informasinya. Sekarang mari kita fokus pada bagaimana kamu bisa mengatasi kesulitan menghafal konsep trigonometri.\n\nMari kita bahas poin-poin belajar kunci terkait trigonometri:\n1. **Pahami Konsep Dasar**: Mulailah dengan memahami konsep dasar trigonometri seperti sinus, kosinus, dan tangen.\n2. **Terapkan Konsep dalam Situasi Nyata**: Cobalah untuk menghubungkan konsep trigonometri dengan situasi nyata atau contoh-contoh yang relevan.\n3. **Gunakan Metode Visual**: Banyak sumber daya online menyediakan visualisasi yang membantu dalam memahami konsep trigonometri, seperti gambar atau video tutorial.\n4. **Latihan Rutin**: Melalui latihan yang konsisten, kamu bisa memperkuat pemahamanmu terhadap konsep trigonometri.\n\nSekarang, mari kita susun roadmap belajar untuk mengatasi kesulitanmu dengan trigonometri:\n**Trigonometri**: \n1. Pahami konsep dasar trigonometri (sinus, kosinus, tangen).\n2. Terapkan konsep dalam situasi nyata untuk memperkuat pemahaman.\n3. Gunakan metode visual seperti gambar atau video tutorial.\n4. Latihan rutin untuk menguatkan pemahaman.\n\nIngat, jangan ragu untuk meminta bantuan dari guru atau teman jika kamu memerlukan bantuan tambahan. Setelah mengikuti roadmap ini, pastikan untuk mereview pemahamanmu.\n\nSekarang, mari kita lanjutkan ke evaluasi akhir. Apakah roadmap ini bermanfaat untukmu? Apakah kamu merasa lebih siap untuk mengatasi kesulitan dengan trigonometri?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 21:13:50', 0, '2025-06-13 14:13:50'),
(118, '48', '38', 'Ya', '2025-06-13 21:14:10', 0, '2025-06-13 14:14:10'),
(119, '38', '48', 'Ya', '2025-06-13 21:14:10', 0, '2025-06-13 14:14:10'),
(120, '48', '38', 'Baik, jika kamu bisa memberikan informasi lebih detail, bagian mana dari mata pelajaran itu yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-13 21:14:12', 0, '2025-06-13 14:14:12'),
(121, '38', '48', 'Baik, jika kamu bisa memberikan informasi lebih detail, bagian mana dari mata pelajaran itu yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-13 21:14:12', 0, '2025-06-13 14:14:12'),
(122, '38', '48', 'apesal kau ni', '2025-06-13 21:14:35', 0, '2025-06-13 14:14:35'),
(123, '48', '38', 'apesal kau ni', '2025-06-13 21:14:35', 0, '2025-06-13 14:14:35'),
(124, '48', '38', 'Halo', '2025-06-13 21:19:14', 0, '2025-06-13 14:19:14'),
(125, '38', '48', 'Halo', '2025-06-13 21:19:14', 0, '2025-06-13 14:19:14'),
(126, '38', '48', 'Halo! Selamat datang! Untuk memulai, saya ingin tahu, dalam mata pelajaran apa kamu sering merasa kesulitan atau tidak nyaman saat belajar? Apakah ada yang bisa saya bantu?', '2025-06-13 21:19:17', 0, '2025-06-13 14:19:17'),
(127, '48', '38', 'Halo! Selamat datang! Untuk memulai, saya ingin tahu, dalam mata pelajaran apa kamu sering merasa kesulitan atau tidak nyaman saat belajar? Apakah ada yang bisa saya bantu?', '2025-06-13 21:19:17', 0, '2025-06-13 14:19:17'),
(128, '38', '48', 'Halo', '2025-06-13 21:42:47', 0, '2025-06-13 14:42:47'),
(129, '48', '38', 'Halo', '2025-06-13 21:42:47', 0, '2025-06-13 14:42:47'),
(130, '48', '38', 'Halo! Selamat datang! Bagaimana saya bisa membantu Anda hari ini? Apakah ada mata pelajaran atau topik tertentu yang ingin Anda diskusikan?', '2025-06-13 21:42:50', 0, '2025-06-13 14:42:50'),
(131, '38', '48', 'Halo! Selamat datang! Bagaimana saya bisa membantu Anda hari ini? Apakah ada mata pelajaran atau topik tertentu yang ingin Anda diskusikan?', '2025-06-13 21:42:50', 0, '2025-06-13 14:42:50'),
(132, '48', '38', 'testing', '2025-06-13 21:46:27', 0, '2025-06-13 14:46:27'),
(133, '38', '48', 'testing', '2025-06-13 21:46:27', 0, '2025-06-13 14:46:27'),
(134, '47', '49', 'pe\n', '2025-06-13 21:49:27', 0, '2025-06-13 14:49:27'),
(135, '49', '47', 'pe\n', '2025-06-13 21:49:27', 0, '2025-06-13 14:49:27'),
(136, '49', '47', 'oke', '2025-06-13 21:50:08', 0, '2025-06-13 14:50:08'),
(137, '47', '49', 'oke', '2025-06-13 21:50:08', 0, '2025-06-13 14:50:08'),
(138, '47', '49', 'l\n', '2025-06-13 21:50:09', 0, '2025-06-13 14:50:09'),
(139, '49', '47', 'l\n', '2025-06-13 21:50:09', 0, '2025-06-13 14:50:09'),
(140, '49', '47', 'e', '2025-06-13 21:50:12', 0, '2025-06-13 14:50:12'),
(141, '47', '49', 'e', '2025-06-13 21:50:12', 0, '2025-06-13 14:50:12'),
(142, '47', '49', 'test', '2025-06-13 21:50:12', 0, '2025-06-13 14:50:12'),
(143, '49', '47', 'test', '2025-06-13 21:50:13', 0, '2025-06-13 14:50:13'),
(144, '47', '49', 'r', '2025-06-13 21:50:13', 0, '2025-06-13 14:50:13'),
(145, '49', '47', 'r', '2025-06-13 21:50:13', 0, '2025-06-13 14:50:13'),
(146, '49', '47', 'halo', '2025-06-13 21:50:18', 0, '2025-06-13 14:50:18'),
(147, '47', '49', 'halo', '2025-06-13 21:50:18', 0, '2025-06-13 14:50:18'),
(148, '49', '47', 'Halo! Selamat datang! Bagaimana saya bisa membantu Anda hari ini? Apakah ada mata pelajaran atau topik tertentu yang ingin Anda diskusikan?', '2025-06-13 21:50:21', 0, '2025-06-13 14:50:21'),
(149, '47', '49', 'Halo! Selamat datang! Bagaimana saya bisa membantu Anda hari ini? Apakah ada mata pelajaran atau topik tertentu yang ingin Anda diskusikan?', '2025-06-13 21:50:21', 0, '2025-06-13 14:50:21'),
(150, '47', '49', 'aku bingung mengenai quantum physics nih', '2025-06-13 21:50:37', 0, '2025-06-13 14:50:37'),
(151, '49', '47', 'aku bingung mengenai quantum physics nih', '2025-06-13 21:50:37', 0, '2025-06-13 14:50:37'),
(152, '49', '47', 'Baik, mari kita bahas lebih lanjut. Pertama-tama, bisa kamu ceritakan lebih detail, bagian mana dari quantum physics yang paling membingungkan bagimu? Apakah itu konsep dasarnya, persamaan matematikanya, atau aplikasinya dalam fisika modern?', '2025-06-13 21:50:42', 0, '2025-06-13 14:50:42'),
(153, '47', '49', 'Baik, mari kita bahas lebih lanjut. Pertama-tama, bisa kamu ceritakan lebih detail, bagian mana dari quantum physics yang paling membingungkan bagimu? Apakah itu konsep dasarnya, persamaan matematikanya, atau aplikasinya dalam fisika modern?', '2025-06-13 21:50:42', 0, '2025-06-13 14:50:42'),
(154, '49', '47', 'konsep dasarnya\n', '2025-06-13 21:52:02', 0, '2025-06-13 14:52:02'),
(155, '47', '49', 'konsep dasarnya\n', '2025-06-13 21:52:02', 0, '2025-06-13 14:52:02'),
(156, '38', '48', 'halo', '2025-06-13 21:53:20', 0, '2025-06-13 14:53:20'),
(157, '48', '38', 'halo', '2025-06-13 21:53:20', 0, '2025-06-13 14:53:20'),
(158, '38', '48', 'Halo! Selamat datang! Apakah ada mata pelajaran atau topik tertentu yang ingin Anda bahas hari ini?', '2025-06-13 21:53:23', 0, '2025-06-13 14:53:23'),
(159, '48', '38', 'Halo! Selamat datang! Apakah ada mata pelajaran atau topik tertentu yang ingin Anda bahas hari ini?', '2025-06-13 21:53:23', 0, '2025-06-13 14:53:23'),
(160, '48', '38', 'kimia', '2025-06-13 21:54:01', 0, '2025-06-13 14:54:01'),
(161, '38', '48', 'kimia', '2025-06-13 21:54:01', 0, '2025-06-13 14:54:01'),
(162, '48', '38', 'Terima kasih telah memilih topik \"Kimia\". Sekarang saya akan mengajukan beberapa pertanyaan untuk memahami lebih dalam kesulitan Anda dalam belajar kimia.\n\na. Mata pelajaran kimia apa yang paling sering membuat Anda kesulitan atau tidak nyaman saat belajar?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari pelajaran kimia yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?\nSaya tunggu jawaban Anda untuk melanjutkan pembahasan.', '2025-06-13 21:54:06', 0, '2025-06-13 14:54:06'),
(163, '38', '48', 'Terima kasih telah memilih topik \"Kimia\". Sekarang saya akan mengajukan beberapa pertanyaan untuk memahami lebih dalam kesulitan Anda dalam belajar kimia.\n\na. Mata pelajaran kimia apa yang paling sering membuat Anda kesulitan atau tidak nyaman saat belajar?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari pelajaran kimia yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?\nSaya tunggu jawaban Anda untuk melanjutkan pembahasan.', '2025-06-13 21:54:07', 0, '2025-06-13 14:54:07'),
(164, '49', '47', 'konsep dasarnya', '2025-06-13 21:54:22', 0, '2025-06-13 14:54:22'),
(165, '47', '49', 'konsep dasarnya', '2025-06-13 21:54:22', 0, '2025-06-13 14:54:22'),
(166, '49', '47', 'Baik, berikut roadmap belajar untuk memahami konsep dasar dalam quantum physics:\n\nKonsep Dasar Quantum Physics:\n1. **Dualitas Gelombang-partikel**: Pahami bahwa objek sub-atom memiliki sifat gelombang dan partikel.\n2. **Superposisi**: Pelajari bahwa objek sub-atom dapat berada dalam banyak keadaan secara bersamaan.\n3. **Kuantisasi**: Ketahui bahwa nilai energi dan momentum partikel terbatas pada nilai diskrit tertentu.\n4. **Tak Tentu**: Pahami prinsip ketidakpastian Heisenberg, bahwa kita tidak bisa secara simultan mengetahui posisi dan momentum partikel dengan tepat.\n5. **Interaksi**: Pelajari bagaimana objek sub-atom berinteraksi melalui pertukaran partikel sub-atom, seperti foton.\n\nRoadmap Belajar:\nKonsep Dasar Quantum Physics --> Mekanika Kuantum --> Teori Medan Kuantum\n\nSaran:\nMulailah dengan memahami konsep dasar quantum physics terlebih dahulu, kemudian lanjutkan dengan mempelajari mekanika kuantum dan teori medan kuantum. Jika ada kesulitan, jangan ragu untuk bertanya pada guru atau diskusikan dengan teman sekelas. Latihan juga sangat penting dalam memahami konsep-konsep ini. Semangat belajar! \n\nSekarang, apakah roadmap belajar ini membantu memperjelas konsep dasar quantum physics untukmu?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 21:54:28', 0, '2025-06-13 14:54:28'),
(167, '47', '49', 'Baik, berikut roadmap belajar untuk memahami konsep dasar dalam quantum physics:\n\nKonsep Dasar Quantum Physics:\n1. **Dualitas Gelombang-partikel**: Pahami bahwa objek sub-atom memiliki sifat gelombang dan partikel.\n2. **Superposisi**: Pelajari bahwa objek sub-atom dapat berada dalam banyak keadaan secara bersamaan.\n3. **Kuantisasi**: Ketahui bahwa nilai energi dan momentum partikel terbatas pada nilai diskrit tertentu.\n4. **Tak Tentu**: Pahami prinsip ketidakpastian Heisenberg, bahwa kita tidak bisa secara simultan mengetahui posisi dan momentum partikel dengan tepat.\n5. **Interaksi**: Pelajari bagaimana objek sub-atom berinteraksi melalui pertukaran partikel sub-atom, seperti foton.\n\nRoadmap Belajar:\nKonsep Dasar Quantum Physics --> Mekanika Kuantum --> Teori Medan Kuantum\n\nSaran:\nMulailah dengan memahami konsep dasar quantum physics terlebih dahulu, kemudian lanjutkan dengan mempelajari mekanika kuantum dan teori medan kuantum. Jika ada kesulitan, jangan ragu untuk bertanya pada guru atau diskusikan dengan teman sekelas. Latihan juga sangat penting dalam memahami konsep-konsep ini. Semangat belajar! \n\nSekarang, apakah roadmap belajar ini membantu memperjelas konsep dasar quantum physics untukmu?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 21:54:28', 0, '2025-06-13 14:54:28'),
(168, '49', '47', 'tidak', '2025-06-13 21:54:59', 0, '2025-06-13 14:54:59'),
(169, '47', '49', 'tidak', '2025-06-13 21:54:59', 0, '2025-06-13 14:54:59'),
(170, '47', '49', 'Maaf jika saya belum bisa membantumu sepenuhnya. Mari kita coba cari tahu lebih dalam.\n\n**Sesi selesai.** Saya telah merangkum beberapa poin penting untuk tutor Anda.', '2025-06-13 21:55:03', 0, '2025-06-13 14:55:03'),
(171, '49', '47', 'Maaf jika saya belum bisa membantumu sepenuhnya. Mari kita coba cari tahu lebih dalam.\n\n**Sesi selesai.** Saya telah merangkum beberapa poin penting untuk tutor Anda.', '2025-06-13 21:55:03', 0, '2025-06-13 14:55:03'),
(172, '49', '47', 'Awikwok', '2025-06-13 22:02:09', 0, '2025-06-13 15:02:09'),
(173, '47', '49', 'Awikwok', '2025-06-13 22:02:09', 0, '2025-06-13 15:02:09'),
(174, '50', '49', 'peler', '2025-06-13 22:02:42', 0, '2025-06-13 15:02:42'),
(175, '49', '50', 'peler', '2025-06-13 22:02:42', 0, '2025-06-13 15:02:42'),
(176, '49', '50', 'ga paham matematika dasar kak', '2025-06-13 22:02:54', 0, '2025-06-13 15:02:54'),
(177, '50', '49', 'ga paham matematika dasar kak', '2025-06-13 22:02:54', 0, '2025-06-13 15:02:54'),
(178, '49', '50', 'Baik, mari kita bahas lebih lanjut. Terkait kesulitan Anda dalam matematika dasar, saya ingin bertanya:\n\na. Mata pelajaran matematika apa yang paling membuat Anda kesulitan?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari matematika dasar yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-13 22:02:59', 0, '2025-06-13 15:02:59'),
(179, '50', '49', 'Baik, mari kita bahas lebih lanjut. Terkait kesulitan Anda dalam matematika dasar, saya ingin bertanya:\n\na. Mata pelajaran matematika apa yang paling membuat Anda kesulitan?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari matematika dasar yang paling membingungkan? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-13 22:02:59', 0, '2025-06-13 15:02:59'),
(180, '48', '38', 'Selamat pagi, saya ingin bertanya. Apakah tersedia layanan pemotretan ijazah untuk Fasilkom Unej? Jika iya, apakah bisa dicetak menggunakan kertas doff? Dan berapa perkiraan harganya? Terima kasih sebelumnya.', '2025-06-13 22:03:16', 0, '2025-06-13 15:03:16'),
(181, '38', '48', 'Selamat pagi, saya ingin bertanya. Apakah tersedia layanan pemotretan ijazah untuk Fasilkom Unej? Jika iya, apakah bisa dicetak menggunakan kertas doff? Dan berapa perkiraan harganya? Terima kasih sebelumnya.', '2025-06-13 22:03:17', 0, '2025-06-13 15:03:17'),
(182, '38', '48', 'Halo! Maaf sebelumnya, tetapi saya adalah Asisten Belajar AI yang dirancang untuk membantu Anda dalam mengatasi kesulitan belajar terkait pelajaran. Jika Anda memiliki pertanyaan tentang mata pelajaran atau topik tertentu yang ingin saya bantu, silakan beritahu saya!', '2025-06-13 22:03:19', 0, '2025-06-13 15:03:19'),
(183, '48', '38', 'Halo! Maaf sebelumnya, tetapi saya adalah Asisten Belajar AI yang dirancang untuk membantu Anda dalam mengatasi kesulitan belajar terkait pelajaran. Jika Anda memiliki pertanyaan tentang mata pelajaran atau topik tertentu yang ingin saya bantu, silakan beritahu saya!', '2025-06-13 22:03:19', 0, '2025-06-13 15:03:19'),
(184, '50', '49', '1. tambah kurang kali bagi\n2. ga paham cara ngerjainnya', '2025-06-13 22:03:56', 0, '2025-06-13 15:03:56'),
(185, '49', '50', '1. tambah kurang kali bagi\n2. ga paham cara ngerjainnya', '2025-06-13 22:03:56', 0, '2025-06-13 15:03:56'),
(186, '38', '48', 'Selamat pagi, saya ingin bertanya. Apakah tersedia layanan pemotretan ijazah untuk Fasilkom Unej? Jika iya, apakah bisa dicetak menggunakan kertas doff? Dan berapa perkiraan harganya? Terima kasih sebelumnya.', '2025-06-13 22:03:56', 0, '2025-06-13 15:03:56'),
(187, '48', '38', 'Selamat pagi, saya ingin bertanya. Apakah tersedia layanan pemotretan ijazah untuk Fasilkom Unej? Jika iya, apakah bisa dicetak menggunakan kertas doff? Dan berapa perkiraan harganya? Terima kasih sebelumnya.', '2025-06-13 22:03:57', 0, '2025-06-13 15:03:57'),
(188, '50', '49', 'Baik, mari kita telaah permasalahan Anda.\n\na. Mata pelajaran apa yang paling sering membuat Anda kesulitan atau tidak nyaman saat belajar? Apakah itu Matematika?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari Matematika yang paling membingungkan? Apakah itu konsep tambah, kurang, kali, atau bagi?', '2025-06-13 22:04:00', 0, '2025-06-13 15:04:00'),
(189, '49', '50', 'Baik, mari kita telaah permasalahan Anda.\n\na. Mata pelajaran apa yang paling sering membuat Anda kesulitan atau tidak nyaman saat belajar? Apakah itu Matematika?\nb. Bisakah Anda ceritakan lebih detail, bagian mana dari Matematika yang paling membingungkan? Apakah itu konsep tambah, kurang, kali, atau bagi?', '2025-06-13 22:04:00', 0, '2025-06-13 15:04:00'),
(190, '38', '48', 'https://drive.google.com/drive/folders/1Q5L0uwP87Ote__OO2e_4Q8AHxP0Q2Ehr', '2025-06-13 22:04:06', 0, '2025-06-13 15:04:06'),
(191, '48', '38', 'https://drive.google.com/drive/folders/1Q5L0uwP87Ote__OO2e_4Q8AHxP0Q2Ehr', '2025-06-13 22:04:06', 0, '2025-06-13 15:04:06'),
(192, '48', '38', 'Permisi Pak/Bu. Saya izin bertanya kembali. Apabila telah melakukan pembayaran, untuk bukti pembayaran apakah perlu dikirimkan di sini atau', '2025-06-13 22:04:36', 0, '2025-06-13 15:04:36'),
(193, '38', '48', 'Permisi Pak/Bu. Saya izin bertanya kembali. Apabila telah melakukan pembayaran, untuk bukti pembayaran apakah perlu dikirimkan di sini atau', '2025-06-13 22:04:36', 0, '2025-06-13 15:04:36'),
(194, '50', '49', '1. matematika\n2. ga paham cara ngerjainnya', '2025-06-13 22:05:04', 0, '2025-06-13 15:05:04'),
(195, '49', '50', '1. matematika\n2. ga paham cara ngerjainnya', '2025-06-13 22:05:04', 0, '2025-06-13 15:05:04'),
(196, '49', '50', 'Baik, mari kita bahas lebih lanjut. Bagian mana dari matematika yang membuatmu paling bingung? Apakah itu terkait dengan konsep tertentu, rumus, atau cara menyelesaikan soal-soal matematika?', '2025-06-13 22:05:07', 0, '2025-06-13 15:05:07'),
(197, '50', '49', 'Baik, mari kita bahas lebih lanjut. Bagian mana dari matematika yang membuatmu paling bingung? Apakah itu terkait dengan konsep tertentu, rumus, atau cara menyelesaikan soal-soal matematika?', '2025-06-13 22:05:07', 0, '2025-06-13 15:05:07'),
(198, '49', '50', 'rumus tambah kurang kali bagi kak', '2025-06-13 22:05:24', 0, '2025-06-13 15:05:24'),
(199, '50', '49', 'rumus tambah kurang kali bagi kak', '2025-06-13 22:05:24', 0, '2025-06-13 15:05:24'),
(200, '49', '50', 'Baik, berikut adalah poin-poin kunci yang perlu dipahami terkait operasi matematika dasar seperti tambah, kurang, kali, dan bagi:\n\n1. **Penjumlahan (Tambah):**\n   - Penjumlahan adalah operasi untuk menggabungkan dua bilangan.\n   - Contoh: 2 + 3 = 5, artinya 2 ditambah 3 sama dengan 5.\n\n2. **Pengurangan (Kurang):**\n   - Pengurangan adalah operasi untuk mengurangi satu bilangan dari bilangan lain.\n   - Contoh: 5 - 3 = 2, artinya 3 dikurangkan dari 5 sama dengan 2.\n\n3. **Perkalian (Kali):**\n   - Perkalian adalah operasi untuk mengalikan dua bilangan.\n   - Contoh: 2 x 4 = 8, artinya 2 dikalikan dengan 4 sama dengan 8.\n\n4. **Pembagian (Bagi):**\n   - Pembagian adalah operasi untuk membagi satu bilangan dengan bilangan lain.\n   - Contoh: 8 : 2 = 4, artinya 8 dibagi dengan 2 sama dengan 4.\n\nRoadmap Belajar:\nTentunya, penting untuk berlatih soal-soal latihan untuk memahami konsep ini dengan lebih baik. Saya sarankan untuk mulai dari penguasaan penjumlahan dan pengurangan terlebih dahulu, kemudian lanjutkan ke perkalian dan pembagian.\n\nJika Anda merasa kesulitan saat belajar sendiri, jangan ragu untuk meminta bantuan dari guru atau teman. Mereka akan dengan senang hati membantu Anda memahami konsep-konsep tersebut dengan lebih baik.\n\nSekarang, apakah informasi ini membantu Anda memahami konsep dasar tambah, kurang, kali, dan bagi?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 22:05:33', 0, '2025-06-13 15:05:33'),
(201, '50', '49', 'Baik, berikut adalah poin-poin kunci yang perlu dipahami terkait operasi matematika dasar seperti tambah, kurang, kali, dan bagi:\n\n1. **Penjumlahan (Tambah):**\n   - Penjumlahan adalah operasi untuk menggabungkan dua bilangan.\n   - Contoh: 2 + 3 = 5, artinya 2 ditambah 3 sama dengan 5.\n\n2. **Pengurangan (Kurang):**\n   - Pengurangan adalah operasi untuk mengurangi satu bilangan dari bilangan lain.\n   - Contoh: 5 - 3 = 2, artinya 3 dikurangkan dari 5 sama dengan 2.\n\n3. **Perkalian (Kali):**\n   - Perkalian adalah operasi untuk mengalikan dua bilangan.\n   - Contoh: 2 x 4 = 8, artinya 2 dikalikan dengan 4 sama dengan 8.\n\n4. **Pembagian (Bagi):**\n   - Pembagian adalah operasi untuk membagi satu bilangan dengan bilangan lain.\n   - Contoh: 8 : 2 = 4, artinya 8 dibagi dengan 2 sama dengan 4.\n\nRoadmap Belajar:\nTentunya, penting untuk berlatih soal-soal latihan untuk memahami konsep ini dengan lebih baik. Saya sarankan untuk mulai dari penguasaan penjumlahan dan pengurangan terlebih dahulu, kemudian lanjutkan ke perkalian dan pembagian.\n\nJika Anda merasa kesulitan saat belajar sendiri, jangan ragu untuk meminta bantuan dari guru atau teman. Mereka akan dengan senang hati membantu Anda memahami konsep-konsep tersebut dengan lebih baik.\n\nSekarang, apakah informasi ini membantu Anda memahami konsep dasar tambah, kurang, kali, dan bagi?\n\nApakah kamu merasa lebih paham sekarang setelah melihat roadmap ini? (Ya/Tidak)', '2025-06-13 22:05:33', 0, '2025-06-13 15:05:33'),
(202, '49', '50', 'ya', '2025-06-13 22:05:47', 0, '2025-06-13 15:05:47'),
(203, '50', '49', 'ya', '2025-06-13 22:05:47', 0, '2025-06-13 15:05:47'),
(204, '50', '49', 'Baik, mari kita fokus pada bagian mana dari matematika dasar yang membuat Anda kesulitan. Bisakah Anda ceritakan lebih detail tentang hal ini? Apakah ada konsep, rumus, atau cara pengerjaan tertentu yang membuat Anda bingung?', '2025-06-13 22:05:50', 0, '2025-06-13 15:05:50'),
(205, '49', '50', 'Baik, mari kita fokus pada bagian mana dari matematika dasar yang membuat Anda kesulitan. Bisakah Anda ceritakan lebih detail tentang hal ini? Apakah ada konsep, rumus, atau cara pengerjaan tertentu yang membuat Anda bingung?', '2025-06-13 22:05:50', 0, '2025-06-13 15:05:50'),
(206, '50', '49', 'we santai dong\n', '2025-06-13 22:07:05', 0, '2025-06-13 15:07:05'),
(207, '49', '50', 'we santai dong\n', '2025-06-13 22:07:05', 0, '2025-06-13 15:07:05'),
(208, '48', '38', 'Permisi Pak/Bu. Saya izin bertanya kembali. Apabila telah melakukan pembayaran, untuk bukti pembayaran apakah perlu dikirimkan di sini atau', '2025-06-13 22:19:42', 0, '2025-06-13 15:19:42'),
(209, '38', '48', 'Permisi Pak/Bu. Saya izin bertanya kembali. Apabila telah melakukan pembayaran, untuk bukti pembayaran apakah perlu dikirimkan di sini atau', '2025-06-13 22:19:42', 0, '2025-06-13 15:19:42'),
(210, '48', '38', 'Oke, bagaimana cara mencari jarak antara 2 sudut di segitiga?', '2025-06-13 23:01:32', 0, '2025-06-13 16:01:32'),
(211, '38', '48', 'Oke, bagaimana cara mencari jarak antara 2 sudut di segitiga?', '2025-06-13 23:01:32', 0, '2025-06-13 16:01:32'),
(212, '38', '48', 'Baik, untuk mencari jarak antara dua sudut di segitiga, Anda dapat menggunakan rumus trigonometri yang disebut sebagai Hukum Cosinus. Rumus Hukum Cosinus adalah sebagai berikut:\n\n\\[ c^2 = a^2 + b^2 - 2ab \\cdot \\cos(C) \\]\n\nDalam rumus tersebut:\n- \\( c \\) adalah panjang sisi yang berlawanan dengan sudut yang ingin Anda cari jaraknya.\n- \\( a \\) dan \\( b \\) adalah panjang dua sisi lainnya.\n- \\( C \\) adalah besarnya sudut yang berlawanan dengan sisi \\( c \\).\n\nSetelah Anda menghitung nilai dari \\( c \\) menggunakan rumus di atas, itu akan menjadi jarak antara dua sudut di segitiga yang Anda cari. Jika Anda memerlukan bantuan lebih lanjut atau contoh penerapan rumus ini, jangan ragu untuk bertanya!', '2025-06-13 23:01:36', 0, '2025-06-13 16:01:36'),
(213, '48', '38', 'Baik, untuk mencari jarak antara dua sudut di segitiga, Anda dapat menggunakan rumus trigonometri yang disebut sebagai Hukum Cosinus. Rumus Hukum Cosinus adalah sebagai berikut:\n\n\\[ c^2 = a^2 + b^2 - 2ab \\cdot \\cos(C) \\]\n\nDalam rumus tersebut:\n- \\( c \\) adalah panjang sisi yang berlawanan dengan sudut yang ingin Anda cari jaraknya.\n- \\( a \\) dan \\( b \\) adalah panjang dua sisi lainnya.\n- \\( C \\) adalah besarnya sudut yang berlawanan dengan sisi \\( c \\).\n\nSetelah Anda menghitung nilai dari \\( c \\) menggunakan rumus di atas, itu akan menjadi jarak antara dua sudut di segitiga yang Anda cari. Jika Anda memerlukan bantuan lebih lanjut atau contoh penerapan rumus ini, jangan ragu untuk bertanya!', '2025-06-13 23:01:36', 0, '2025-06-13 16:01:36'),
(214, '38', '48', 'apakah aku bisa tanya hal random? ', '2025-06-13 23:03:44', 0, '2025-06-13 16:03:44'),
(215, '48', '38', 'apakah aku bisa tanya hal random? ', '2025-06-13 23:03:44', 0, '2025-06-13 16:03:44'),
(216, '48', '38', 'Tentu, Anda bisa bertanya mengenai topik apa pun yang ingin Anda pelajari atau diskusikan. Namun, dalam sesi ini, kita fokus pada mengidentifikasi kesulitan Anda dalam belajar kimia. Jadi, apakah Anda ingin berbicara tentang hal-hal spesifik yang membuat Anda kesulitan dalam mata pelajaran kimia?', '2025-06-13 23:03:47', 0, '2025-06-13 16:03:47'),
(217, '38', '48', 'Tentu, Anda bisa bertanya mengenai topik apa pun yang ingin Anda pelajari atau diskusikan. Namun, dalam sesi ini, kita fokus pada mengidentifikasi kesulitan Anda dalam belajar kimia. Jadi, apakah Anda ingin berbicara tentang hal-hal spesifik yang membuat Anda kesulitan dalam mata pelajaran kimia?', '2025-06-13 23:03:47', 0, '2025-06-13 16:03:47'),
(218, '38', '48', 'Anjai hahahaha', '2025-06-13 23:04:39', 0, '2025-06-13 16:04:39'),
(219, '48', '38', 'Anjai hahahaha', '2025-06-13 23:04:39', 0, '2025-06-13 16:04:39'),
(220, '50', '38', 'Bang. kok kamu wibu :v', '2025-06-13 23:48:17', 0, '2025-06-13 16:48:17'),
(221, '38', '50', 'Bang. kok kamu wibu :v', '2025-06-13 23:48:17', 0, '2025-06-13 16:48:17'),
(222, '47', '49', 'Cara mencari luas segitiga', '2025-06-13 23:50:51', 0, '2025-06-13 16:50:51'),
(223, '49', '47', 'Cara mencari luas segitiga', '2025-06-13 23:50:51', 0, '2025-06-13 16:50:51'),
(224, '49', '47', 'Tentu! Untuk mencari luas segitiga, Anda dapat menggunakan rumus dasar: \\( \\text{Luas} = \\frac{1}{2} \\times \\text{alas} \\times \\text{tinggi} \\).\n\nApakah Anda sudah familiar dengan rumus tersebut atau ada bagian tertentu yang masih membingungkan?', '2025-06-13 23:50:55', 0, '2025-06-13 16:50:55'),
(225, '47', '49', 'Tentu! Untuk mencari luas segitiga, Anda dapat menggunakan rumus dasar: \\( \\text{Luas} = \\frac{1}{2} \\times \\text{alas} \\times \\text{tinggi} \\).\n\nApakah Anda sudah familiar dengan rumus tersebut atau ada bagian tertentu yang masih membingungkan?', '2025-06-13 23:50:55', 0, '2025-06-13 16:50:55'),
(226, '48', '38', 'Mengapa bumi mengelilingi matahari? ', '2025-06-13 23:52:04', 0, '2025-06-13 16:52:04'),
(227, '38', '48', 'Mengapa bumi mengelilingi matahari? ', '2025-06-13 23:52:04', 0, '2025-06-13 16:52:04'),
(228, '48', '38', 'Pertanyaan yang menarik! Namun, untuk membantu saya memahami lebih baik kesulitan belajar Anda, bisakah Anda memberikan informasi lebih terkait mata pelajaran atau topik apa yang sering membuat Anda kesulitan atau tidak nyaman saat belajar? Apakah ada topik spesifik dalam ilmu pengetahuan atau fisika yang ingin Anda diskusikan?', '2025-06-13 23:52:06', 0, '2025-06-13 16:52:06'),
(229, '38', '48', 'Pertanyaan yang menarik! Namun, untuk membantu saya memahami lebih baik kesulitan belajar Anda, bisakah Anda memberikan informasi lebih terkait mata pelajaran atau topik apa yang sering membuat Anda kesulitan atau tidak nyaman saat belajar? Apakah ada topik spesifik dalam ilmu pengetahuan atau fisika yang ingin Anda diskusikan?', '2025-06-13 23:52:06', 0, '2025-06-13 16:52:06'),
(230, '38', '48', 'Astronomi', '2025-06-13 23:52:23', 0, '2025-06-13 16:52:23'),
(231, '48', '38', 'Astronomi', '2025-06-13 23:52:23', 0, '2025-06-13 16:52:23'),
(232, '38', '48', 'Baik, mari kita fokus pada topik astronomi. Bisakah Anda ceritakan lebih detail, bagian mana dari astronomi yang paling membingungkan bagi Anda? Apakah ada konsep tertentu seperti gerak planet, tata surya, atau fenomena langit lainnya yang sulit dipahami?', '2025-06-13 23:52:25', 0, '2025-06-13 16:52:25'),
(233, '48', '38', 'Baik, mari kita fokus pada topik astronomi. Bisakah Anda ceritakan lebih detail, bagian mana dari astronomi yang paling membingungkan bagi Anda? Apakah ada konsep tertentu seperti gerak planet, tata surya, atau fenomena langit lainnya yang sulit dipahami?', '2025-06-13 23:52:25', 0, '2025-06-13 16:52:25'),
(234, '38', '48', 'aku kesulitan di mapel ips', '2025-06-14 00:06:24', 0, '2025-06-13 17:06:24'),
(235, '48', '38', 'aku kesulitan di mapel ips', '2025-06-14 00:06:24', 0, '2025-06-13 17:06:24'),
(236, '48', '38', 'Baik, terima kasih telah berbagi. Untuk membantu lebih lanjut, bisakah kamu ceritakan lebih detail, bagian mana dari IPS yang paling membingungkan bagimu? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-14 00:06:26', 0, '2025-06-13 17:06:26'),
(237, '38', '48', 'Baik, terima kasih telah berbagi. Untuk membantu lebih lanjut, bisakah kamu ceritakan lebih detail, bagian mana dari IPS yang paling membingungkan bagimu? Apakah itu konsep, rumus, atau cara pengerjaannya?', '2025-06-14 00:06:26', 0, '2025-06-13 17:06:26'),
(238, '48', '38', 'sejarah Indonesia', '2025-06-14 00:06:44', 0, '2025-06-13 17:06:44'),
(239, '38', '48', 'sejarah Indonesia', '2025-06-14 00:06:44', 0, '2025-06-13 17:06:44'),
(240, '38', '48', 'sejarah indonesia', '2025-06-14 00:06:55', 0, '2025-06-13 17:06:55'),
(241, '48', '38', 'sejarah indonesia', '2025-06-14 00:06:55', 0, '2025-06-13 17:06:55'),
(242, '38', '48', 'Baik, mari kita mulai dengan beberapa pertanyaan untuk lebih memahami kesulitan Anda dalam belajar Sejarah Indonesia:\n\na. Mata pelajaran apa yang paling sering membuat Anda kesulitan saat belajar? Apakah itu Sejarah Indonesia secara umum atau topik-topik spesifik di dalamnya?', '2025-06-14 00:06:58', 0, '2025-06-13 17:06:58'),
(243, '48', '38', 'Baik, mari kita mulai dengan beberapa pertanyaan untuk lebih memahami kesulitan Anda dalam belajar Sejarah Indonesia:\n\na. Mata pelajaran apa yang paling sering membuat Anda kesulitan saat belajar? Apakah itu Sejarah Indonesia secara umum atau topik-topik spesifik di dalamnya?', '2025-06-14 00:06:58', 0, '2025-06-13 17:06:58'),
(244, '38', '48', 'iya, benar', '2025-06-14 00:07:15', 0, '2025-06-13 17:07:15'),
(245, '48', '38', 'iya, benar', '2025-06-14 00:07:15', 0, '2025-06-13 17:07:15'),
(246, '38', '48', 'Baik, mari kita fokus pada konsep gerak planet di tata surya. Konsep ini memang bisa menjadi rumit bagi banyak orang. Sebelum kita lanjut, pernahkah Anda mencoba membaca buku atau mencari informasi tambahan mengenai gerak planet di tata surya? Apakah ada hal lain yang sudah Anda coba untuk memahami konsep ini?', '2025-06-14 00:07:19', 0, '2025-06-13 17:07:19'),
(247, '48', '38', 'Baik, mari kita fokus pada konsep gerak planet di tata surya. Konsep ini memang bisa menjadi rumit bagi banyak orang. Sebelum kita lanjut, pernahkah Anda mencoba membaca buku atau mencari informasi tambahan mengenai gerak planet di tata surya? Apakah ada hal lain yang sudah Anda coba untuk memahami konsep ini?', '2025-06-14 00:07:19', 0, '2025-06-13 17:07:19'),
(248, '48', '38', 'belum pernah', '2025-06-14 00:07:55', 0, '2025-06-13 17:07:55'),
(249, '38', '48', 'belum pernah', '2025-06-14 00:07:55', 0, '2025-06-13 17:07:55'),
(250, '38', '48', 'Baik, jika kamu belum pernah mengalami kesulitan dalam mata pelajaran tertentu, mungkin kita bisa fokus pada topik atau konsep yang menarik bagi kamu. Apakah ada topik tertentu yang ingin kamu pelajari lebih dalam atau diskusikan?', '2025-06-14 00:07:58', 0, '2025-06-13 17:07:58'),
(251, '48', '38', 'Baik, jika kamu belum pernah mengalami kesulitan dalam mata pelajaran tertentu, mungkin kita bisa fokus pada topik atau konsep yang menarik bagi kamu. Apakah ada topik tertentu yang ingin kamu pelajari lebih dalam atau diskusikan?', '2025-06-14 00:07:58', 0, '2025-06-13 17:07:58'),
(252, '50', '38', 'mengapa jarang ada buah berwarna biru', '2025-06-14 00:08:52', 0, '2025-06-13 17:08:52'),
(253, '38', '50', 'mengapa jarang ada buah berwarna biru', '2025-06-14 00:08:52', 0, '2025-06-13 17:08:52'),
(254, '50', '38', 'Pertanyaan yang menarik! Sebenarnya, warna biru pada buah merupakan hasil dari pigmen tertentu yang disebut antosianin. Meskipun antosianin hadir dalam beberapa buah seperti blueberry, blackberry, dan plum, namun buah berwarna biru cenderung lebih jarang daripada buah dengan warna lainnya. Hal ini disebabkan oleh kombinasi faktor genetik, lingkungan, dan evolusi.\n\nAntosianin yang memberikan warna biru pada buah dipengaruhi oleh gen yang mengode produksi pigmen tersebut. Beberapa spesies tumbuhan memiliki gen-gen yang memungkinkan mereka menghasilkan antosianin biru, sementara spesies lain mungkin tidak memiliki gen tersebut.\n\nSelain faktor genetik, lingkungan tumbuhan juga memainkan peran penting dalam produksi antosianin. Faktor-faktor seperti kondisi tanah, paparan sinar matahari, dan iklim dapat memengaruhi produksi pigmen dalam buah.\n\nSecara evolusi, warna buah yang cerah seperti merah, kuning, dan oranye mungkin lebih menarik bagi hewan pemakan buah untuk memakan dan menyebarkan bijinya. Oleh karena itu, buah dengan warna-warna cerah ini mungkin lebih umum daripada buah berwarna biru yang dapat kurang menarik bagi hewan-hewan tersebut.\n\nJadi, kombinasi dari faktor genetik, lingkungan, dan evolusi ini menjelaskan mengapa buah berwarna biru cenderung lebih jarang dibandingkan dengan buah berwarna lainnya.', '2025-06-14 00:08:59', 0, '2025-06-13 17:08:59'),
(255, '38', '50', 'Pertanyaan yang menarik! Sebenarnya, warna biru pada buah merupakan hasil dari pigmen tertentu yang disebut antosianin. Meskipun antosianin hadir dalam beberapa buah seperti blueberry, blackberry, dan plum, namun buah berwarna biru cenderung lebih jarang daripada buah dengan warna lainnya. Hal ini disebabkan oleh kombinasi faktor genetik, lingkungan, dan evolusi.\n\nAntosianin yang memberikan warna biru pada buah dipengaruhi oleh gen yang mengode produksi pigmen tersebut. Beberapa spesies tumbuhan memiliki gen-gen yang memungkinkan mereka menghasilkan antosianin biru, sementara spesies lain mungkin tidak memiliki gen tersebut.\n\nSelain faktor genetik, lingkungan tumbuhan juga memainkan peran penting dalam produksi antosianin. Faktor-faktor seperti kondisi tanah, paparan sinar matahari, dan iklim dapat memengaruhi produksi pigmen dalam buah.\n\nSecara evolusi, warna buah yang cerah seperti merah, kuning, dan oranye mungkin lebih menarik bagi hewan pemakan buah untuk memakan dan menyebarkan bijinya. Oleh karena itu, buah dengan warna-warna cerah ini mungkin lebih umum daripada buah berwarna biru yang dapat kurang menarik bagi hewan-hewan tersebut.\n\nJadi, kombinasi dari faktor genetik, lingkungan, dan evolusi ini menjelaskan mengapa buah berwarna biru cenderung lebih jarang dibandingkan dengan buah berwarna lainnya.', '2025-06-14 00:08:59', 0, '2025-06-13 17:08:59'),
(256, '50', '38', 'apa itu teori evolusi? ', '2025-06-14 00:09:26', 0, '2025-06-13 17:09:26'),
(257, '38', '50', 'apa itu teori evolusi? ', '2025-06-14 00:09:26', 0, '2025-06-13 17:09:26'),
(258, '50', '38', 'Pertanyaan yang bagus! Teori evolusi adalah konsep ilmiah yang diusulkan oleh Charles Darwin pada abad ke-19. Teori evolusi menjelaskan bahwa semua makhluk hidup berasal dari nenek moyang bersama dan telah mengalami perubahan dari generasi ke generasi melalui proses seleksi alam. Evolusi terjadi melalui mekanisme seperti seleksi alam, mutasi genetik, dan drift genetik. Teori evolusi menjelaskan keragaman kehidupan yang kita amati hari ini. Apakah penjelasan ini membantu, atau apakah ada yang ingin ditanyakan lebih lanjut tentang teori evolusi?', '2025-06-14 00:09:31', 0, '2025-06-13 17:09:31'),
(259, '38', '50', 'Pertanyaan yang bagus! Teori evolusi adalah konsep ilmiah yang diusulkan oleh Charles Darwin pada abad ke-19. Teori evolusi menjelaskan bahwa semua makhluk hidup berasal dari nenek moyang bersama dan telah mengalami perubahan dari generasi ke generasi melalui proses seleksi alam. Evolusi terjadi melalui mekanisme seperti seleksi alam, mutasi genetik, dan drift genetik. Teori evolusi menjelaskan keragaman kehidupan yang kita amati hari ini. Apakah penjelasan ini membantu, atau apakah ada yang ingin ditanyakan lebih lanjut tentang teori evolusi?', '2025-06-14 00:09:31', 0, '2025-06-13 17:09:31'),
(260, '38', '48', 'apa itu teori evolusi? ', '2025-06-14 00:09:46', 0, '2025-06-13 17:09:46'),
(261, '48', '38', 'apa itu teori evolusi? ', '2025-06-14 00:09:46', 0, '2025-06-13 17:09:46'),
(262, '38', '48', 'Sebelum menjawab pertanyaan Anda, mari kita fokus terlebih dahulu pada topik Sejarah Indonesia yang menjadi sumber kesulitan Anda. \n\na. Apakah ada topik khusus dalam Sejarah Indonesia yang membuat Anda merasa kesulitan atau tidak nyaman saat belajar?\n\nJika Anda ingin berdiskusi lebih lanjut tentang teori evolusi, silakan beri tahu saya.', '2025-06-14 00:09:50', 0, '2025-06-13 17:09:50'),
(263, '48', '38', 'Sebelum menjawab pertanyaan Anda, mari kita fokus terlebih dahulu pada topik Sejarah Indonesia yang menjadi sumber kesulitan Anda. \n\na. Apakah ada topik khusus dalam Sejarah Indonesia yang membuat Anda merasa kesulitan atau tidak nyaman saat belajar?\n\nJika Anda ingin berdiskusi lebih lanjut tentang teori evolusi, silakan beri tahu saya.', '2025-06-14 00:09:50', 0, '2025-06-13 17:09:50'),
(264, '48', '38', 'aku ingin berdiskusi soal teori evolusi', '2025-06-14 00:10:31', 0, '2025-06-13 17:10:31'),
(265, '38', '48', 'aku ingin berdiskusi soal teori evolusi', '2025-06-14 00:10:31', 0, '2025-06-13 17:10:31'),
(266, '48', '38', 'Tentu, topik evolusi adalah topik yang menarik! Namun, untuk membantu saya memahami lebih baik kesulitan belajar Anda, apakah ada bagian spesifik dari teori evolusi yang membuat Anda merasa kesulitan atau tidak nyaman? Apakah ada konsep tertentu seperti seleksi alam, adaptasi, atau teori Darwin yang ingin Anda bahas?', '2025-06-14 00:10:33', 0, '2025-06-13 17:10:33'),
(267, '38', '48', 'Tentu, topik evolusi adalah topik yang menarik! Namun, untuk membantu saya memahami lebih baik kesulitan belajar Anda, apakah ada bagian spesifik dari teori evolusi yang membuat Anda merasa kesulitan atau tidak nyaman? Apakah ada konsep tertentu seperti seleksi alam, adaptasi, atau teori Darwin yang ingin Anda bahas?', '2025-06-14 00:10:33', 0, '2025-06-13 17:10:33'),
(268, '38', '48', 'apa itu teori evolusi? ', '2025-06-14 00:10:52', 0, '2025-06-13 17:10:52'),
(269, '48', '38', 'apa itu teori evolusi? ', '2025-06-14 00:10:52', 0, '2025-06-13 17:10:52'),
(270, '48', '38', 'Pertanyaan yang bagus! Teori evolusi adalah konsep ilmiah yang menjelaskan bagaimana makhluk hidup berubah dari waktu ke waktu melalui proses evolusi. Menurut teori evolusi, semua makhluk hidup berasal dari nenek moyang bersama dan mengalami perubahan genetik secara bertahap untuk menyesuaikan diri dengan lingkungannya. Teori evolusi juga menjelaskan tentang seleksi alam, di mana makhluk hidup yang memiliki sifat-sifat yang menguntungkan akan lebih mungkin bertahan hidup dan berkembang biak. Apakah penjelasan ini sudah membantu memahami konsep teori evolusi? Apakah ada hal lain yang ingin kamu ketahui terkait topik ini?', '2025-06-14 00:10:59', 0, '2025-06-13 17:10:59'),
(271, '38', '48', 'Pertanyaan yang bagus! Teori evolusi adalah konsep ilmiah yang menjelaskan bagaimana makhluk hidup berubah dari waktu ke waktu melalui proses evolusi. Menurut teori evolusi, semua makhluk hidup berasal dari nenek moyang bersama dan mengalami perubahan genetik secara bertahap untuk menyesuaikan diri dengan lingkungannya. Teori evolusi juga menjelaskan tentang seleksi alam, di mana makhluk hidup yang memiliki sifat-sifat yang menguntungkan akan lebih mungkin bertahan hidup dan berkembang biak. Apakah penjelasan ini sudah membantu memahami konsep teori evolusi? Apakah ada hal lain yang ingin kamu ketahui terkait topik ini?', '2025-06-14 00:10:59', 0, '2025-06-13 17:10:59'),
(272, '38', '48', 'Bagsimana proses terjadinya fotosintesis? ', '2025-06-14 00:11:27', 0, '2025-06-13 17:11:27'),
(273, '48', '38', 'Bagsimana proses terjadinya fotosintesis? ', '2025-06-14 00:11:27', 0, '2025-06-13 17:11:27'),
(274, '48', '38', 'Pertanyaan yang bagus! Proses fotosintesis adalah proses di mana tumbuhan dan beberapa organisme lain menggunakan energi cahaya matahari untuk mengubah karbon dioksida dan air menjadi glukosa (gula) dan oksigen. Berikut adalah langkah-langkah utama dalam proses fotosintesis:\n\n1. **Penangkapan Cahaya Matahari**: Klorofil, pigmen hijau yang terdapat dalam kloroplas, menangkap energi cahaya matahari.\n\n2. **Pembelahan Air**: Air dipecah menjadi oksigen dan hidrogen oleh enzim dalam reaksi terang.\n\n3. **Siklus Calvin (Reaksi Gelap)**: Dalam tahap ini, karbon dioksida diubah menjadi glukosa melalui serangkaian reaksi kimia kompleks yang melibatkan enzim dan molekul lainnya.\n\n4. **Penghasilan Glukosa**: Glukosa yang dihasilkan akan disimpan sebagai sumber energi dan bahan bangunan bagi tumbuhan.\n\nSelain itu, fotosintesis juga menghasilkan oksigen sebagai produk sampingan yang dilepaskan ke udara.\n\nApakah penjelasan ini membantu memahami proses fotosintesis? Apakah ada bagian tertentu yang ingin kamu ketahui lebih lanjut?', '2025-06-14 00:11:33', 0, '2025-06-13 17:11:33'),
(275, '38', '48', 'Pertanyaan yang bagus! Proses fotosintesis adalah proses di mana tumbuhan dan beberapa organisme lain menggunakan energi cahaya matahari untuk mengubah karbon dioksida dan air menjadi glukosa (gula) dan oksigen. Berikut adalah langkah-langkah utama dalam proses fotosintesis:\n\n1. **Penangkapan Cahaya Matahari**: Klorofil, pigmen hijau yang terdapat dalam kloroplas, menangkap energi cahaya matahari.\n\n2. **Pembelahan Air**: Air dipecah menjadi oksigen dan hidrogen oleh enzim dalam reaksi terang.\n\n3. **Siklus Calvin (Reaksi Gelap)**: Dalam tahap ini, karbon dioksida diubah menjadi glukosa melalui serangkaian reaksi kimia kompleks yang melibatkan enzim dan molekul lainnya.\n\n4. **Penghasilan Glukosa**: Glukosa yang dihasilkan akan disimpan sebagai sumber energi dan bahan bangunan bagi tumbuhan.\n\nSelain itu, fotosintesis juga menghasilkan oksigen sebagai produk sampingan yang dilepaskan ke udara.\n\nApakah penjelasan ini membantu memahami proses fotosintesis? Apakah ada bagian tertentu yang ingin kamu ketahui lebih lanjut?', '2025-06-14 00:11:33', 0, '2025-06-13 17:11:33'),
(276, '48', '38', 'selamat pagi', '2025-06-14 06:24:42', 0, '2025-06-13 23:24:42'),
(277, '38', '48', 'selamat pagi', '2025-06-14 06:24:42', 0, '2025-06-13 23:24:42'),
(278, '38', '48', 'Selamat pagi! Ada yang bisa saya bantu hari ini? Apakah ada pelajaran atau topik tertentu yang ingin kamu diskusikan atau bahas bersama?', '2025-06-14 06:24:46', 0, '2025-06-13 23:24:46'),
(279, '48', '38', 'Selamat pagi! Ada yang bisa saya bantu hari ini? Apakah ada pelajaran atau topik tertentu yang ingin kamu diskusikan atau bahas bersama?', '2025-06-14 06:24:46', 0, '2025-06-13 23:24:46'),
(280, '48', '38', 'ada. jelaskan pengertian termodinamika', '2025-06-14 06:25:13', 0, '2025-06-13 23:25:13'),
(281, '38', '48', 'ada. jelaskan pengertian termodinamika', '2025-06-14 06:25:13', 0, '2025-06-13 23:25:13'),
(282, '38', '48', 'Tentu, saya akan menjelaskan secara singkat tentang termodinamika.\n\nTermodinamika adalah cabang ilmu fisika yang mempelajari perubahan energi, kerja, dan panas yang terjadi dalam sistem fisik. Ilmu ini membahas tentang bagaimana energi berpindah antara sistem dan lingkungannya saat terjadi interaksi termal.\n\nApakah penjelasan singkat ini sudah memberikan gambaran yang jelas atau apakah ada bagian tertentu yang masih membingungkan?', '2025-06-14 06:25:16', 0, '2025-06-13 23:25:16'),
(283, '48', '38', 'Tentu, saya akan menjelaskan secara singkat tentang termodinamika.\n\nTermodinamika adalah cabang ilmu fisika yang mempelajari perubahan energi, kerja, dan panas yang terjadi dalam sistem fisik. Ilmu ini membahas tentang bagaimana energi berpindah antara sistem dan lingkungannya saat terjadi interaksi termal.\n\nApakah penjelasan singkat ini sudah memberikan gambaran yang jelas atau apakah ada bagian tertentu yang masih membingungkan?', '2025-06-14 06:25:16', 0, '2025-06-13 23:25:16'),
(284, '48', '38', 'yang bener? ', '2025-06-14 06:25:30', 0, '2025-06-13 23:25:30'),
(285, '38', '48', 'yang bener? ', '2025-06-14 06:25:30', 0, '2025-06-13 23:25:30'),
(286, '38', '48', 'sudah jelas', '2025-06-14 06:25:44', 0, '2025-06-13 23:25:44'),
(287, '48', '38', 'sudah jelas', '2025-06-14 06:25:44', 0, '2025-06-13 23:25:44');
INSERT INTO `recent_chats` (`id`, `user_id`, `peer_id`, `last_message`, `last_message_time`, `unread_count`, `updated_at`) VALUES
(288, '48', '38', 'Baik, selanjutnya, apakah ada topik spesifik dalam termodinamika yang ingin kamu pelajari lebih lanjut atau kamu alami kesulitan? Atau apakah ada pertanyaan lain yang ingin kamu ajukan terkait termodinamika?', '2025-06-14 06:25:46', 0, '2025-06-13 23:25:46'),
(289, '38', '48', 'Baik, selanjutnya, apakah ada topik spesifik dalam termodinamika yang ingin kamu pelajari lebih lanjut atau kamu alami kesulitan? Atau apakah ada pertanyaan lain yang ingin kamu ajukan terkait termodinamika?', '2025-06-14 06:25:46', 0, '2025-06-13 23:25:46'),
(290, '38', '48', 'tidak ada', '2025-06-14 06:26:01', 0, '2025-06-13 23:26:01'),
(291, '48', '38', 'tidak ada', '2025-06-14 06:26:02', 0, '2025-06-13 23:26:02'),
(292, '48', '38', 'Baik, jika tidak ada masalah terkait topik astronomi yang telah kita diskusikan, mari kita fokus kembali pada topik astronomi. Menurut Anda, apa yang menjadi penyebab utama kesulitan Anda dalam memahami konsep gerak planet di tata surya? Apakah ini terkait dengan cara penyampaian informasi, kurangnya latihan, atau mungkin terlalu banyak informasi sekaligus?', '2025-06-14 06:26:05', 0, '2025-06-13 23:26:05'),
(293, '38', '48', 'Baik, jika tidak ada masalah terkait topik astronomi yang telah kita diskusikan, mari kita fokus kembali pada topik astronomi. Menurut Anda, apa yang menjadi penyebab utama kesulitan Anda dalam memahami konsep gerak planet di tata surya? Apakah ini terkait dengan cara penyampaian informasi, kurangnya latihan, atau mungkin terlalu banyak informasi sekaligus?', '2025-06-14 06:26:05', 0, '2025-06-13 23:26:05'),
(294, '48', '38', 'The first plant-like organisms probably... In the sea, perhaps 3 billion years ago. \nA. Life\nB. Living\nC. Lived\nD. It was living.', '2025-06-17 10:22:16', 0, '2025-06-17 03:22:16'),
(295, '38', '48', 'The first plant-like organisms probably... In the sea, perhaps 3 billion years ago. \nA. Life\nB. Living\nC. Lived\nD. It was living.', '2025-06-17 10:22:16', 0, '2025-06-17 03:22:16'),
(296, '38', '48', 'The correct option is: C. Lived\n\nThis sentence describes an event that occurred in the past, so the correct form is \"lived.\" The sentence should read: \"The first plant-like organisms probably lived in the sea, perhaps 3 billion years ago.\"', '2025-06-17 10:22:20', 0, '2025-06-17 03:22:20'),
(297, '48', '38', 'The correct option is: C. Lived\n\nThis sentence describes an event that occurred in the past, so the correct form is \"lived.\" The sentence should read: \"The first plant-like organisms probably lived in the sea, perhaps 3 billion years ago.\"', '2025-06-17 10:22:20', 0, '2025-06-17 03:22:20'),
(298, '38', '48', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:22:46', 0, '2025-06-17 03:22:46'),
(299, '48', '38', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:22:46', 0, '2025-06-17 03:22:46'),
(300, '48', '38', '', '2025-06-17 10:22:49', 0, '2025-06-17 03:22:49'),
(301, '38', '48', '', '2025-06-17 10:22:49', 0, '2025-06-17 03:22:49'),
(302, '48', '38', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:23:00', 0, '2025-06-17 03:23:00'),
(303, '38', '48', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:23:00', 0, '2025-06-17 03:23:00'),
(304, '48', '38', 'Pertanyaan inti:\na. Mata pelajaran apa yang paling sering membuatmu kesulitan atau tidak nyaman saat belajar?\n\nApakah Anda sedang belajar tentang tata surya, geologi, atau ilmu bumi?\n\nJawaban Anda akan membantu saya memahami konteks lebih lanjut.', '2025-06-17 10:23:02', 0, '2025-06-17 03:23:02'),
(305, '38', '48', 'Pertanyaan inti:\na. Mata pelajaran apa yang paling sering membuatmu kesulitan atau tidak nyaman saat belajar?\n\nApakah Anda sedang belajar tentang tata surya, geologi, atau ilmu bumi?\n\nJawaban Anda akan membantu saya memahami konteks lebih lanjut.', '2025-06-17 10:23:02', 0, '2025-06-17 03:23:02'),
(306, '38', '48', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:23:12', 0, '2025-06-17 03:23:12'),
(307, '48', '38', 'The earth\'s plates meet each other at cracks in the earth...faults.\nA. Were called\nB. Calls\nC. Called\nD. It was called', '2025-06-17 10:23:12', 0, '2025-06-17 03:23:12'),
(308, '38', '48', 'Pertanyaan terkait materi bahasa Inggris:\na. Apakah kamu mengalami kesulitan dalam memahami konsep tata bahasa bahasa Inggris?\nb. Apakah kamu bisa menjelaskan lebih detail mengenai bagian mana dari tata bahasa bahasa Inggris yang membuatmu bingung?\n\nJawaban yang benar untuk kalimat tersebut adalah:\nC. Called\n\nApakah jawaban ini membantu memperjelas konsep tata bahasa bahasa Inggris untukmu?', '2025-06-17 10:23:15', 0, '2025-06-17 03:23:15'),
(309, '48', '38', 'Pertanyaan terkait materi bahasa Inggris:\na. Apakah kamu mengalami kesulitan dalam memahami konsep tata bahasa bahasa Inggris?\nb. Apakah kamu bisa menjelaskan lebih detail mengenai bagian mana dari tata bahasa bahasa Inggris yang membuatmu bingung?\n\nJawaban yang benar untuk kalimat tersebut adalah:\nC. Called\n\nApakah jawaban ini membantu memperjelas konsep tata bahasa bahasa Inggris untukmu?', '2025-06-17 10:23:15', 0, '2025-06-17 03:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `about` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `price` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `about`, `skills`, `price`) VALUES
(47, 'As an experienced tutor, I am dedicated to helping students achieve their academic potential. I have strong knowledge in the subject of Maths and extensive teaching experience at various levels of education.', 'aljabar,kalkulus,data science,pandas,trigonometry', 10000),
(48, NULL, NULL, 10000),
(50, NULL, NULL, 10000);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_availability`
--

CREATE TABLE `teacher_availability` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `available_day` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` enum('true','false') DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher_availability`
--

INSERT INTO `teacher_availability` (`id`, `teacher_id`, `available_day`, `start_time`, `end_time`, `is_available`) VALUES
(109, 48, 'Monday', '09:00:00', '17:00:00', 'true'),
(110, 48, 'Tuesday', '09:00:00', '17:00:00', ''),
(111, 48, 'Wednesday', '09:00:00', '17:00:00', ''),
(112, 48, 'Thursday', '09:00:00', '17:00:00', 'true'),
(113, 48, 'Friday', '09:00:00', '17:00:00', 'true'),
(114, 48, 'Saturday', '09:00:00', '17:00:00', ''),
(115, 48, 'Sunday', '09:00:00', '17:00:00', ''),
(116, 50, 'Monday', '09:00:00', '17:00:00', ''),
(117, 50, 'Tuesday', '09:00:00', '17:00:00', ''),
(118, 50, 'Wednesday', '09:00:00', '17:00:00', 'true'),
(119, 50, 'Thursday', '09:00:00', '17:00:00', 'true'),
(120, 50, 'Friday', '09:00:00', '17:00:00', ''),
(121, 50, 'Saturday', '09:00:00', '17:00:00', ''),
(122, 50, 'Sunday', '09:00:00', '17:00:00', ''),
(123, 47, 'Monday', '09:00:00', '17:00:00', ''),
(124, 47, 'Tuesday', '09:00:00', '17:00:00', 'true'),
(125, 47, 'Wednesday', '09:00:00', '17:00:00', ''),
(126, 47, 'Thursday', '09:00:00', '17:00:00', 'true'),
(127, 47, 'Friday', '09:00:00', '17:00:00', ''),
(128, 47, 'Saturday', '09:00:00', '17:00:00', 'true'),
(129, 47, 'Sunday', '09:00:00', '17:00:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `user_actor` enum('student','tutor') NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `birth_date` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `courses` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone_number`, `user_photo`, `user_actor`, `gender`, `birth_date`, `city`, `country`, `address`, `education_level`, `courses`) VALUES
(38, 'Akbargobar Ardi', 'a@gmail.com', '$2y$10$somQo5ZnGtVDGpWTWaG8wexN8/az7/rXcB.2n8mU8JQqnGe9nuxgO', '089657575757', 'https://edulink.blob.core.windows.net/images/profile_f7e4da52-0c94-4d55-b662-8af5209d1911.jpg', 'student', 'male', '03/04/2002', 'Surabaya', '', '', 'MA', 'sports,quran hadith'),
(47, 'Brian Andri', 'b@gmail.com', '$2y$10$mPGutOwI.VySOXT12taf5uqgvoV1YplEHlONwZnUi3cT2.Rq/TOs2', '087456956256', '', 'tutor', 'male', '25/11/1992', 'Tangerang', '', '', '', 'math'),
(48, 'Cahya Adi Chandra', 'c@gmail.com', '$2y$10$/QyqHspvYY0lWHjWdD2m6OB7ycev078p5o3tPF.HOmtSFX.ghnOdm', '08655541455', 'https://edulink.blob.core.windows.net/images/profile_459042f9-e9e8-4201-b810-f0505797849c.jpg', 'tutor', 'male', '04/11/1996', 'Depok', '', '', '', 'business and marketing,entrepreneurship,agriculture'),
(49, 'test', 'asldjhiq@gmail.com', '$2y$10$TEqi9Q4aoVafxW3TMR.9J.QKcLwTr0bf.hCnVh37JMRGm0UfYxO9a', '0819731781234', 'https://edulink.blob.core.windows.net/images/profile_91fe9a79-0987-48da-8519-3e63d3f03cf7.jpg', 'student', 'male', '01/01/2000', 'semarang', '', '', 'SMK', 'mathematics,indonesian language,traditional language,tourism,english language'),
(50, 'test tutor', 'awikwok@gmail.com', '$2y$10$Irl5IBNjTEQTaPESsVoL4ecP7vmYW6jFcOXtlGdLYyq5AGuLgxWFG', '081231412', 'https://edulink.blob.core.windows.net/images/profile_5549abc3-70e7-4fcc-a641-6816f4f2e5fc.jpg', 'tutor', 'other', '01/01/2000', 'jember', '', '', '', 'math,english,science,information technology and computer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id_booking`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `recent_chats`
--
ALTER TABLE `recent_chats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teacher_id` (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id_booking` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `recent_chats`
--
ALTER TABLE `recent_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;

--
-- AUTO_INCREMENT for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_user` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD CONSTRAINT `fk_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
