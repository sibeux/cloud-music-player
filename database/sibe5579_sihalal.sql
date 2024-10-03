-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 23 Sep 2024 pada 01.41
-- Versi server: 10.6.19-MariaDB-cll-lve
-- Versi PHP: 8.3.11

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `alamat`
--

CREATE TABLE `alamat` (
  `id_alamat` int(5) NOT NULL,
  `id_user` int(5) NOT NULL,
  `nama_penerima` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_penerima` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label_alamat` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `provinsi` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kota` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kecamatan` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `kode_pos` tinytext NOT NULL,
  `detail_alamat` mediumtext DEFAULT NULL,
  `pinpoint_alamat` mediumtext DEFAULT NULL,
  `is_utama` enum('true','false') NOT NULL,
  `is_toko` enum('true','false') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `alamat`
--

INSERT INTO `alamat` (`id_alamat`, `id_user`, `nama_penerima`, `nomor_penerima`, `label_alamat`, `provinsi`, `kota`, `kecamatan`, `kode_pos`, `detail_alamat`, `pinpoint_alamat`, `is_utama`, `is_toko`) VALUES
(1, 1, 'M Nasrul Wahabi', '0895413386498', 'Rumah Saya', 'Jawa Timur', 'Kabupaten Blitar', 'Udanawu', '66154', 'Ds. Tunjung Kec. Udanawu Kab. Blitar Jawa Timur, pos 66154', NULL, 'true', 'true');

-- --------------------------------------------------------

--
-- Struktur dari tabel `favorite`
--

CREATE TABLE `favorite` (
  `id_favorite` int(5) NOT NULL,
  `id_produk` int(5) NOT NULL,
  `id_user` int(5) NOT NULL,
  `tanggal_favorite` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(5) NOT NULL,
  `nama_kategori` text NOT NULL,
  `perlu_SH` enum('yes','no') NOT NULL DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `perlu_SH`) VALUES
(1, 'Daging', 'yes'),
(2, 'Buah', 'no'),
(3, 'Telur', 'no'),
(4, 'Minyak', 'yes');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(5) NOT NULL,
  `id_user` tinyint(5) DEFAULT NULL,
  `id_kategori` int(5) DEFAULT NULL,
  `foto_produk_1` varchar(200) NOT NULL,
  `foto_produk_2` varchar(200) DEFAULT NULL,
  `foto_produk_3` varchar(200) DEFAULT NULL,
  `nama_produk` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi_produk` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `harga_produk` int(10) NOT NULL,
  `stok_produk` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_user`, `id_kategori`, `foto_produk_1`, `foto_produk_2`, `foto_produk_3`, `nama_produk`, `deskripsi_produk`, `harga_produk`, `stok_produk`) VALUES
(1, 1, 2, 'https://ik.imagekit.io/dcjlghyytp1/f4645e42e825dc85dcbbce576feaeb31?tr=f-auto,w-1000', NULL, NULL, 'Durian Lokal Nusantara Utuh', '1.2 - 1.7 kg/pcs, hanya bisa dipesan jam 00.00-18.00\r\n\r\nIni adalah durian spesial dari Kampung Durian Runtuh Tok Dalang.\r\n\r\nSedap, sedap, sedap.', 39900, 100),
(3, 1, 1, 'https://img.freepik.com/free-photo/fresh-minced-meat-ready-cooking_1220-4988.jpg?w=996&t=st=1707379550~exp=1707380150~hmac=686e6ec8b6fef0cf7e573ba86f33deb76ba22814498bc41d095a36d0e3dc02e4', NULL, NULL, 'Daging Sapi Giling Halal Savory Beef Selections Dari ribeye yang empuk hingga sirloin yang penuh rasa', 'Savory Beef Selections menghadirkan berbagai potongan daging sapi dengan rasa yang menggugah selera. Dari ribeye yang empuk hingga sirloin yang penuh rasa, setiap potongan dipilih dengan teliti untuk memastikan kelezatan. Daging sapi kami diproses dengan standar kebersihan dan kualitas tinggi, dan siap untuk diolah menjadi hidangan yang menggugah selera. Pilih dari berbagai pilihan kami dan buat setiap makan malam menjadi spesial.', 52900, 50),
(4, 1, 3, 'https://img.freepik.com/premium-photo/fresh-chicken-eggs-basket-grey-wooden-background_106006-1013.jpg?w=996', 'https://images.tokopedia.net/img/cache/900/VqbcmM/2021/7/24/026bb439-abc5-4b59-87fb-c7766d4ead3a.jpg', 'https://images.tokopedia.net/img/cache/900/VqbcmM/2022/6/17/f81c2197-0d76-4e2b-a801-449a195e5d81.jpg', 'Telur Ayam Kampung Petelur', '10 pcs, 10 pcs (Harga Rp12.000 per butir)', 13800, 1000),
(5, 1, 4, 'https://ik.imagekit.io/dcjlghyytp1/5b0338051dc85cb9df799c2cbe6cb018?tr=f-auto,w-1000', NULL, NULL, 'Minyak Goreng', 'Sania Minyak Goreng Sawit Pouch 1 liter', 18600, 100);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id_rating` int(5) NOT NULL,
  `id_produk` int(5) DEFAULT NULL,
  `id_user` tinyint(5) DEFAULT NULL,
  `bintang_rating` int(1) NOT NULL,
  `pesan_rating` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_rating` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id_rating`, `id_produk`, `id_user`, `bintang_rating`, `pesan_rating`, `tanggal_rating`) VALUES
(1, 1, 1, 4, 'enak', '2024-08-26 19:18:06'),
(2, 3, 1, 3, NULL, '2024-08-26 22:03:37'),
(3, 1, 1, 5, 'Suka banget belanja di sini 💜', '2024-09-02 19:18:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` tinyint(5) NOT NULL,
  `email_user` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_user` tinytext NOT NULL,
  `pass_user` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_toko` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi_toko` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_user` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `email_user`, `nama_user`, `pass_user`, `nama_toko`, `deskripsi_toko`, `foto_user`) VALUES
(1, 'wahabinasrul@gmail.com ', 'M Nasrul Wahabi', 'sibeHBQ342169', 'Habiqi.Shop', 'Gramedia Official Store Menyediakan Buku-buku Asli dan Berkualitas. Toko ini buka setiap hari Senin - Sabtu (Pukul 09.00 -16:30 WIB)', 'https://sibeux.my.id/images/sibe.png'),
(5, 'sibe@hotmail.com', 'Siberia', '$2y$10$x0ObrG7fcJML2uLXIMu74OKsKwvgqIofOuv.RGbgcVt5fakuVWkg2', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alamat`
--
ALTER TABLE `alamat`
  ADD PRIMARY KEY (`id_alamat`);

--
-- Indeks untuk tabel `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id_favorite`),
  ADD KEY `Delete Favorite` (`id_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `Delete Produk` (`id_user`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id_rating`),
  ADD KEY `Delete Rating by User` (`id_user`),
  ADD KEY `Delete Rating by Produk` (`id_produk`) USING BTREE;

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email_user` (`email_user`) USING HASH;

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alamat`
--
ALTER TABLE `alamat`
  MODIFY `id_alamat` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id_favorite` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` tinyint(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `Delete Favorite` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `Delete Produk` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Ketidakleluasaan untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `Delete Rating by Produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `Delete Rating by User` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
