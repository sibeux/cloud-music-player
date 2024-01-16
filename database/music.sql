-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 16 Jan 2024 pada 15.30
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
(30, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196559636518670508/nano.RIPE_-_Last_Chapter_.mp3?ex=65b81200&is=65a59d00&hm=dcd460925d5f0b1a06d206ee92edfaa959e3bc8a7b0ca7c85521fa0f62977d87&', 'https://open.spotify.com/intl-id/track/5g6GlkNDtJLnRo17t8gDIx?si=76061fb572a6425f', NULL, NULL, NULL, '', NULL, 0),
(31, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196563761977360667/Tokyo_Ghoul_EP12_OST_-_SorrowRizes_Melody_Kanekis_Transformation_HD_1080p.mp3?ex=65b815d7&is=65a5a0d7&hm=fd26382bfb3af04fb263917c131b36ed5898e92cc60967a30d3e647314d8db37&', NULL, 'Rize\'s Melody', 'Yutaka Yamada', 'TV ANIME [TOKYO GHOUL] ORIGINAL SOUNDTRACK', '', 'https://i.scdn.co/image/ab67616d0000b2730809f5b6616747f5abbe8824', 1),
(33, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196564887166533773/Ousama_Ranking_OST_-_Haha_no_Gisei_Ai.mp3?ex=65b816e4&is=65a5a1e4&hm=a57ec10d9442a22f799bebea1f4ad20363104959d6aecd209c00188581127c54&', '', 'Haha no Gisei Ai', 'MAYUKO', 'Ousama Ranking - Main Theme -', '', 'https://i.scdn.co/image/ab67616d0000b273505c189a4d351a249a9c55a2', 1),
(34, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196564887497871440/milet_MAN_WITH_A_MISSION_-_Koi_Kogare_.mp3?ex=65b816e4&is=65a5a1e4&hm=77a5a83472b758a30b0f0bfbef1188eafd5388917aa11f729b9b8a1e6d24b59d&', 'https://open.spotify.com/intl-id/track/1RtxMS6dcQuxK5y8TKh4Md?si=eff2edabcc2b4700', NULL, NULL, NULL, '', NULL, 0),
(35, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196568358007615559/MAN_WITH_A_MISSION_milet_-_Kizuna_No_Kiseki_.mp3?ex=65b81a1f&is=65a5a51f&hm=a60e8de0e3e83a669c4102af2c6ad3dad20d1ba8f1a66b499e0ceafdfafa37d5&', 'https://open.spotify.com/intl-id/track/2VBLFxCUyFp5BfmsZpxcis?si=c363e0c385d8437c', 'Kizuna no Kiseki', NULL, NULL, '', NULL, 0),
(36, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196568358309609533/Yasuharu_Takanashi_-_Shippuden_.mp3?ex=65b81a1f&is=65a5a51f&hm=d1f01212b030019304788095c6c4a1ef3d77a37b521e378b89e4dfd73648138b&', 'https://open.spotify.com/intl-id/track/0y2OZcxtwVvzk4vHsSJXwu?si=a229a64973da43de', 'Shippuden', NULL, NULL, '', NULL, 0),
(37, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196568358607409183/Mafumafu_-_Nisengohyakumanbunnoichi.mp3?ex=65b81a1f&is=65a5a51f&hm=47051f71080b367f30a45b948a75d948f300c22879954021e188d3a0bd1ad546&', 'https://open.spotify.com/intl-id/track/3OC3tOu95hsAykFUk4fHTL?si=1df1d07965444bae', 'mafumafu', NULL, NULL, '', NULL, 0),
(38, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196568358896807976/TOMORROW_X_TOGETHER_-_Everlasting_Shine.mp3?ex=65b81a1f&is=65a5a51f&hm=e5ccd7197254cd3407faed9dcd06b373df4aa17334096e0fff78560d3d9bb76e&', 'https://open.spotify.com/intl-id/track/3zLCX1TGMpsA67cW2pq6ut?si=1dbe72f6d2ca4828', '', NULL, NULL, '', NULL, 0),
(39, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196722551079063602/Jujutsu_Kaisen_0_OST_-_Peace_Out.mp3?ex=65b8a9ba&is=65a634ba&hm=efbf8ef4e9311b0f331ed1bd44919a73403578ae7cc6e77a6a14022b119128f7&', 'https://open.spotify.com/intl-id/track/3b2xRGuyB3YMsHNUq8oyIN?si=498fa710d8514136', NULL, NULL, NULL, '', NULL, 0),
(40, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196722551364263966/Ao_no_sumika_-_.mp3?ex=65b8a9ba&is=65a634ba&hm=66449de32ac59b210831540510b621e2d15bed7152d8bda64e9c62316b1b19a7&', 'https://open.spotify.com/intl-id/track/12usPU2WnqgCHAW1EK2dfd?si=986c0ef7778e4fea', NULL, NULL, NULL, '', NULL, 0),
(41, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196722551636897873/Kaikai_Kitan.mp3?ex=65b8a9ba&is=65a634ba&hm=5a41024b97d6904b825a2d789c3da50ab3caf12323a42c38201675f1d20c1e9f&', 'https://open.spotify.com/intl-id/track/6y4GYuZszeXNOXuBFsJlos?si=8179a5e493174169', NULL, NULL, NULL, '', NULL, 0),
(42, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196723795340308480/crossing_field.mp3?ex=65b8aae2&is=65a635e2&hm=21496fbcf91c6a59d18140ad9002253d0d58e8986b3835998a108c077515d6ab&', 'https://open.spotify.com/intl-id/track/4BvuZVf9KyBN3QiPfeI9hw?si=b272110e74a64e15', NULL, NULL, NULL, '', NULL, 1),
(43, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196723795646484551/Fumetsu_no_Anata_e_OST_-_To_Your_Eternity_Theme.mp3?ex=65b8aae2&is=65a635e2&hm=3f63429ae20c1314ed6c944771c4192c3a5273ece75be52f109c2c3456b39e04&', 'https://open.spotify.com/intl-id/track/4WBvcP0V6u3Yf79MHh3agv?si=4d7d725898d14ff7', NULL, NULL, NULL, '', NULL, 1),
(44, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196723795906535444/Jujutsu_Kaisen_0_OST_-_Together_Forever_And_Ever.mp3?ex=65b8aae2&is=65a635e2&hm=77b4286e053ffa3a273d87d1c8045c1f448f3709efbbd4d12889b817aa3e21dc&', 'https://open.spotify.com/intl-id/track/0WzERsH085f3zP8u0Z9rGV?si=08c4ba71aaf142ae', NULL, NULL, NULL, '', NULL, 1),
(45, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196723796187557988/Sakuramitsutsuki_-_.mp3?ex=65b8aae3&is=65a635e3&hm=b72fa92f65d8c8e988949616b615adba8186c0cfdbc8f06f31b3026a4d9bcb26&', 'https://open.spotify.com/intl-id/track/4Q8Xekfd9ihkVPCBxGK0ec?si=20eba060f4854246', NULL, NULL, NULL, '', NULL, 1),
(46, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196728354854932541/This_is_pure_love.mp3?ex=65b8af21&is=65a63a21&hm=161c0458e00cbe6ccdb2d2c96d113404c6d25caff99d3778fbe1d469533391e6&', 'https://open.spotify.com/intl-id/track/57oMhJtMXpjhhTnVrfu8xr?si=898e1d6c514c45c7', NULL, NULL, NULL, '', NULL, 0),
(47, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729978725863434/About_Maki.mp3?ex=65b8b0a5&is=65a63ba5&hm=88afb631165bee2b996170644de2fb0f64d0754837b73f0554476941183db1e6&', 'https://open.spotify.com/intl-id/track/0UQerQnx5c6N3v7y8t6qUJ?si=0a32805ef9eb482a', NULL, NULL, NULL, '', NULL, 0),
(48, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729979002703952/As_Usual_-_.mp3?ex=65b8b0a5&is=65a63ba5&hm=a58fb4488ba4357235e7d8e407c927a8868d9ebec0f832c009070841dc2585fa&', 'https://open.spotify.com/intl-id/track/6u5xDJLEvJWR9F3LyOynDo?si=8335ba81538a43cf', NULL, NULL, NULL, '', NULL, 0),
(49, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729979287896115/Brother_-_.mp3?ex=65b8b0a5&is=65a63ba5&hm=931e25c0682e7b5081e4e11c428e1b33ffc020e616d6f6006cda99a868131bbc&', 'https://open.spotify.com/intl-id/track/3sS6E0F1lWizsBN4s03isB?si=fec704679509417d', NULL, NULL, NULL, '', NULL, 0),
(50, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729979560529950/Pain_-_.mp3?ex=65b8b0a5&is=65a63ba5&hm=44fe06721f68295d7d05140a3ec28b3bb2fc9afebc775783d8d7097357f05df9&', 'https://open.spotify.com/intl-id/track/56GXPrJxdXQBbxRm3GBgLu?si=956bcd9b299548b0', NULL, NULL, NULL, '', NULL, 0),
(51, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729979866730517/RECORD.mp3?ex=65b8b0a5&is=65a63ba5&hm=9a4f3db41d544753ab9b0601a38b33b751118a45930e28995346ec5c1275b74c&', 'https://open.spotify.com/intl-id/track/3J92JdMFD9Ps7s4GBhXytU?si=20b5753229d54f74', NULL, NULL, NULL, '', NULL, 0);

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
  MODIFY `id_music` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
