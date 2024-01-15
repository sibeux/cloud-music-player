-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 16 Jan 2024 pada 04.07
-- Versi server: 10.3.39-MariaDB-cll-lve
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sibk1922_cloud_music`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `music`
--

CREATE TABLE `music` (
  `id_music` int(5) NOT NULL,
  `link_gdrive` text NOT NULL,
  `link_spotify` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `artist` varchar(200) DEFAULT NULL,
  `album` text DEFAULT NULL,
  `time` varchar(5) NOT NULL,
  `cover` text DEFAULT NULL,
  `favorite` smallint(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `music`
--

INSERT INTO `music` (`id_music`, `link_gdrive`, `link_spotify`, `title`, `artist`, `album`, `time`, `cover`, `favorite`) VALUES
(1, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Greatest%20Strength.mp3', 'https://open.spotify.com/intl-id/track/2d0MD0qqYEdGSvqgLHNiDR?si=aed2e29bf1594973', 'Greatest Strength', 'Hiroaki Tsutsumi', 'Jujutsu Kaisen 0 Original Sountrack', '', 'https://i.scdn.co/image/ab67616d0000b273283da7197a2e5d610adfe4e9', 1),
(8, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/%E3%81%9D%E3%82%93%E3%81%AA%E5%90%9B%E3%80%81%E3%81%93%E3%82%93%E3%81%AA%E5%83%95%20-%20Thinking%20Dogs.mp3', 'https://open.spotify.com/intl-id/track/6UNWa5YS4G0rJkisF4quAW?si=e26927dfb28e48f3', NULL, NULL, NULL, '', NULL, 1),
(10, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Flow%20-%20Sign.mp3', 'https://open.spotify.com/intl-id/track/0xmWQKzc5m9rLv2ucDWxwD?si=b6b73276d79348f2', NULL, NULL, NULL, '', NULL, 0),
(11, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Uru%20-%20%E7%B4%99%E4%B8%80%E9%87%8D.mp3', 'https://open.spotify.com/intl-id/track/4WqWAyxI9uf6CVxUBwglrb?si=8defc974e6f842bc', NULL, NULL, NULL, '', NULL, 0),
(12, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Swimy%20-%20%E7%B5%B6%E7%B5%B6.mp3', 'https://open.spotify.com/intl-id/track/1oifj4ZzYNNDvkM9mByhsM?si=a0a6d79f1bfe4d7c', NULL, NULL, NULL, '', NULL, 0),
(13, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Akihisa%20Kondo%20-%20%E3%83%96%E3%83%A9%E3%83%83%E3%82%AF%E3%83%8A%E3%82%A4%E3%83%88%E3%82%BF%E3%82%A6%E3%83%B3.mp3', 'https://open.spotify.com/intl-id/track/61xQj6eNjY8PnOVyueG4y5?si=57b865f886124bc0', NULL, NULL, NULL, '', NULL, 0),
(14, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/FLOW%20-%20%E8%99%B9%E3%81%AE%E7%A9%BA.mp3', 'https://open.spotify.com/intl-id/track/1ugw9zRE5ufC6AA2YriY52?si=dcab2c0a22f24277', NULL, NULL, NULL, '', NULL, 1),
(15, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Shun,%20Lyu%20Lyu%20-%20Never%20Change.mp3', 'https://open.spotify.com/intl-id/track/5NGWulc0W5ctrG8RSDcnoW?si=819e5a429a3247d2', NULL, NULL, NULL, '', NULL, 1),
(16, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/ASIAN%20KUNG-FU%20GENERATION%20-%20%E9%81%A5%E3%81%8B%E5%BD%BC%E6%96%B9.mp3', 'https://open.spotify.com/intl-id/track/5ORPYXJKlpHWIdceavSGrL?si=4cded48143664a51', NULL, NULL, NULL, '', NULL, 0),
(17, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/FLOW%20-%20Re_member.mp3', 'https://open.spotify.com/intl-id/track/1hoQxGi3ujVYUzQDhXfvkN?si=81cdddf294de4010', NULL, NULL, NULL, '', NULL, 1),
(18, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/FLOW,%20GRANRODEO%20-%20Howling.mp3', 'https://open.spotify.com/intl-id/track/2LdW2wz7EBI7dM54KWyri9?si=ca269664a7c84c3c', NULL, NULL, NULL, '', NULL, 0),
(19, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/FLOW%20-%20COLORS.mp3', 'https://open.spotify.com/intl-id/track/6bPPyigCphBBQ9781j6eKM?si=42c831156770495f', NULL, NULL, NULL, '', NULL, 0),
(20, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Boku%20Dake%20ga%20Inai%20Machi%20OST%20-%20For%20Your%20Pain.mp3', NULL, 'For Your Pain', 'Yuki Kajiura', 'ERASED OST DICS 2', '', 'https://raw.githubusercontent.com/sibeux/license-sibeux/6490a201304882286d3b3c9610a9cbc7ad619740/artworks-000170281236-tu74m6-original.jpg', 1),
(21, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/FLOW%20-%20GO!!!.mp3', 'https://open.spotify.com/intl-id/track/30WNOfFRiqgebO4eRkCii8?si=3c9246766c46455d', NULL, NULL, NULL, '', NULL, 1),
(22, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Motohiro%20Hata%20-%20%E3%81%B2%E3%81%BE%E3%82%8F%E3%82%8A%E3%81%AE%E7%B4%84%E6%9D%9F.mp3', 'https://open.spotify.com/intl-id/track/45jGOHwYKgsRYbAJ8DR61d?si=619b879a35044ee5', NULL, NULL, NULL, '', NULL, 1),
(23, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Survive%20Said%20The%20Prophet%20-%20Paradox.mp3', 'https://open.spotify.com/intl-id/track/6A7sTvj68RJAVgTduJEQnA?si=1a96433899ae4056', NULL, NULL, NULL, '', NULL, 1),
(24, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Ikimonogakari%20-%20BLUEBIRD%20%E3%83%96%E3%83%AB%E3%83%BC%E3%83%90%E3%83%BC%E3%83%89.mp3', 'https://open.spotify.com/intl-id/track/2XpV9sHBexcNrz0Gyf3l18?si=eb9a2359151141ea', NULL, NULL, NULL, '', NULL, 0),
(25, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Joe%20Inoue%20-%20CLOSER.mp3', 'https://open.spotify.com/intl-id/track/2Kld61w2NR7zPPXtaHeIii?si=2e70f7143bb64ef1', NULL, NULL, NULL, '', NULL, 0),
(26, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/KANA-BOON%20-%20Silhouette%20%E3%82%B7%E3%83%AB%E3%82%A8%E3%83%83%E3%83%88.mp3', 'https://open.spotify.com/intl-id/track/1di1C0QI6Y92yZPYn6XYAZ?si=ab5f64d678754bbe', NULL, NULL, NULL, '', NULL, 0),
(27, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/No%20Regret%20Life%20-%20NAKUSHITA%20KOTOBA%20%E5%A4%B1%E3%81%8F%E3%81%97%E3%81%9F%E8%A8%80%E8%91%89.mp3', 'https://open.spotify.com/intl-id/track/4oi24PD8rHopSEXgCwJvlJ?si=f21ecd61c3c94e43', NULL, NULL, NULL, '', NULL, 0),
(28, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Sambomaster%20-%20(Seishun%20Kyousoukyoku)%20%E9%9D%92%E6%98%A5%E7%8B%82%E9%A8%92%E6%9B%B2.mp3', 'https://open.spotify.com/intl-id/track/3zE6YLb2IBb8NriBuuXWQd?si=3aa5213ee5704038', NULL, NULL, NULL, '', NULL, 0),
(29, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196559636233470123/yama_-_Oz..mp3?ex=65b81200&is=65a59d00&hm=dcbe66eaa18d42b13883e78ade9ee15b03664670317fdcb5eea526ed7edde9aa&', 'https://open.spotify.com/intl-id/track/2VRcLEvQCMByWBuvM9gaJ2?si=988e14b1d42e4b78', NULL, NULL, NULL, '', NULL, 0),
(30, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196559636518670508/nano.RIPE_-_Last_Chapter_.mp3?ex=65b81200&is=65a59d00&hm=dcd460925d5f0b1a06d206ee92edfaa959e3bc8a7b0ca7c85521fa0f62977d87&', 'https://open.spotify.com/intl-id/track/5g6GlkNDtJLnRo17t8gDIx?si=76061fb572a6425f', NULL, NULL, NULL, '', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`id_music`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `music`
--
ALTER TABLE `music`
  MODIFY `id_music` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
