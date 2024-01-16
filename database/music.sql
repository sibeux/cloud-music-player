-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 17 Jan 2024 pada 00.19
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
(8, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/%E3%81%9D%E3%82%93%E3%81%AA%E5%90%9B%E3%80%81%E3%81%93%E3%82%93%E3%81%AA%E5%83%95%20-%20Thinking%20Dogs.mp3', 'https://open.spotify.com/intl-id/track/6UNWa5YS4G0rJkisF4quAW?si=e26927dfb28e48f3', 'Sonna kimi, konna boku', NULL, NULL, '', NULL, 1),
(10, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Flow%20-%20Sign.mp3', 'https://open.spotify.com/intl-id/track/0xmWQKzc5m9rLv2ucDWxwD?si=b6b73276d79348f2', NULL, NULL, NULL, '', NULL, 0),
(11, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Uru%20-%20%E7%B4%99%E4%B8%80%E9%87%8D.mp3', 'https://open.spotify.com/intl-id/track/4WqWAyxI9uf6CVxUBwglrb?si=8defc974e6f842bc', 'Kamihitoe', NULL, NULL, '', NULL, 0),
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
(51, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196729979866730517/RECORD.mp3?ex=65b8b0a5&is=65a63ba5&hm=9a4f3db41d544753ab9b0601a38b33b751118a45930e28995346ec5c1275b74c&', 'https://open.spotify.com/intl-id/track/3J92JdMFD9Ps7s4GBhXytU?si=20b5753229d54f74', NULL, NULL, NULL, '', NULL, 0),
(52, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196783947435216946/MERMAID.mp3?ex=65b8e2e8&is=65a66de8&hm=4c174b0e40f55e9b7ea0b3bdcda8adab27ce9e651293100128cdcc4d8c3fecba&', 'https://open.spotify.com/intl-id/track/1VmKspfbJ4egubIwzZePdn?si=7f8c28d6cc8846f2', NULL, NULL, NULL, '', NULL, 0),
(53, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196790244931014656/1000000_TIMES_feat._chelly_EGOIST.mp3?ex=65b8e8c5&is=65a673c5&hm=542c6e4cec5e3cc603fd589dced6e948998f1a276e633122f7c6a6610dc8c58c&', 'https://open.spotify.com/intl-id/track/3dVMeI08Of33bjSELmtSqq?si=46218f50a4e6401f', NULL, NULL, NULL, '', NULL, 0),
(54, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196790245249798215/Special_Grade_Vengeful_Cursed_Spirit_-_RIKA.mp3?ex=65b8e8c5&is=65a673c5&hm=32eceafbc7b792f5e76b0c3bcdc503b091179ae5111c5c3c474b6af8ee43c549&', 'https://open.spotify.com/intl-id/track/03B5wUC7bcDRcyF14FBVNK?si=244bcab0aded4c11', NULL, NULL, NULL, '', NULL, 0),
(55, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794033574510663/Liar_-_.mp3?ex=65b8ec4c&is=65a6774c&hm=922eb6177876e470a59ac8e308fd33ecd9e6eff408c3687b810083200cf5759f&', 'https://open.spotify.com/intl-id/track/4L4BcvZZGCLwua4fT3pZsj?si=6f633103deab4a91', NULL, NULL, NULL, '', NULL, 0),
(56, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794033918464020/Call_of_Silence.mp3?ex=65b8ec4d&is=65a6774d&hm=316754182501ff0bc952eae9e114c236f91a3451a9aa75f5d9fd8f3c2404a7f2&', 'https://open.spotify.com/intl-id/track/7k1HoUdskuBhyWvm7hPctM?si=8b43727ba4e9443d', NULL, NULL, NULL, '', NULL, 0),
(57, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794034279166083/Shippuden_-_.mp3?ex=65b8ec4d&is=65a6774d&hm=0d7468a1ba0d1fb69000cc317fdb351869377862ed612cc9caaeb2f47e241b11&', 'https://open.spotify.com/intl-id/track/0y2OZcxtwVvzk4vHsSJXwu?si=6a109a285018441f', NULL, NULL, NULL, '', NULL, 1),
(58, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794034585358437/Nine.mp3?ex=65b8ec4d&is=65a6774d&hm=c150664ad986673217a88dce6d552bf70638a98858f998b80951d69e18f05aa4&', 'https://open.spotify.com/intl-id/track/2q2TVQWn0Jorp9g0Uo24Tw?si=3f316e46739645d7', NULL, NULL, NULL, '', NULL, 1),
(59, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794034857980034/Loser_-_.mp3?ex=65b8ec4d&is=65a6774d&hm=01bb5a489b2dc21b357ae496b06910ea56516d64d732114e2d5d3a579e586ca8&', 'https://open.spotify.com/intl-id/track/3MKlI3y3MQjcAzmgG3VJoe?si=9aa63f19db1c46d5', 'Loser', NULL, NULL, '', NULL, 1),
(60, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196794035201900554/Put_It_in_Your_Fist_-_.mp3?ex=65b8ec4d&is=65a6774d&hm=3f3e38cf5b4c9b44c7ba3a12c3104b0d5f0101a6e15f1f030acca9e2f42a8699&', 'https://open.spotify.com/intl-id/track/4fmLqJjyhj1nqnmIT77ESi?si=e88b8b579d4e4c1e', 'Put it in your fist', NULL, NULL, '', NULL, 1),
(61, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196812053285965834/Where_we_re_going_Hans_Zimmer_Interstellar_SlowedReverb.mp3?ex=65b8fd15&is=65a68815&hm=0583309be8e1edcbd868688d1e567f6148a380898374e34946b436165f25cbbc&', NULL, 'Where We\'re Going (Slowed & Reverb)', 'Hans Zimmer', 'Interstellar Original Soundtrack', '', 'https://i.scdn.co/image/ab67616d0000b273ac29a65e7ffcfa6f9cb0d342', 0),
(62, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816607213801482/Delirious.mp3?ex=65b90152&is=65a68c52&hm=a3b1bb3930a3a3787e3a7ce83dfa2b2199d8fec259f4a15e086d81ac106d68f5&', 'https://open.spotify.com/intl-id/track/1UoumTWoYJfZaiTUoT0Wbn?si=a43d8f211b1546be', NULL, NULL, NULL, '', NULL, 0),
(63, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816607658381382/Schmetterling.mp3?ex=65b90153&is=65a68c53&hm=23be08154a8f2c281079eed3adb5abaa4e057ecf17f001d66963d0bcde6f7b7e&', 'https://open.spotify.com/intl-id/track/4pDlbpkpQQS4ef9aONOZbl?si=718fb44704d44260', NULL, NULL, NULL, '', NULL, 0),
(64, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816608132354139/Rittaikidou.mp3?ex=65b90153&is=65a68c53&hm=1d216533b5dbd9a364eadaff583eab52997b7520c94f2aedbc2ddf98cbef6759&', 'https://open.spotify.com/intl-id/track/7u32XGioKOcqZYrEZphaF5?si=6a274814c7b14287', NULL, NULL, NULL, '', NULL, 0),
(65, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816608522416218/We_Are_Stop_The_Attack.mp3?ex=65b90153&is=65a68c53&hm=01e3f28c3e934235e003ecd4ac5823099997ea6f40d026c20f33dd17c3bba3ce&', 'https://open.spotify.com/intl-id/track/5hUqVz9JExxoKPReVyT9Zs?si=9dc86c8f07c842bb', NULL, NULL, NULL, '', NULL, 0),
(66, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816608820203530/BLUE.mp3?ex=65b90153&is=65a68c53&hm=de489956f86539bb26a43476f15de3cd44d46a605a6cf6e88682bfb8365c199d&', 'https://open.spotify.com/intl-id/track/7F1qu0e6hEdQGvUPB3zxeD?si=c53e9e5f16dd4952', NULL, NULL, NULL, '', NULL, 0),
(67, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196816609147375666/Jujutsu-shi_Fushiguro_Megumi_-_.mp3?ex=65b90153&is=65a68c53&hm=7fb47e54697c0ac1df18fb7f5f562db35eefa1dd3c0e6c2515f20220ff1105f6&', 'https://open.spotify.com/intl-id/track/2OjsjNXWHp8gywp569pWpF?si=38aaafff29674d61', 'Jujutsu-shi Fushiguro Megumi', NULL, NULL, '', NULL, 0),
(68, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196819391912874044/Masaru_Yokoyama_A.mp3?ex=65b903ea&is=65a68eea&hm=71c1b3f1904370e6ca782e56c40562cb6144fe63c294a06007f6a46bb4de4dee&', 'https://open.spotify.com/intl-id/track/3XixsqKoeCsO8PATo6wAKU?si=1075971214ff42e2', 'Yujin A kimi o watashi no banso-sha ni ninmei shimasu', 'Masaru Yokoyama', 'Your Lie in April ORIGINAL SONG & SOUNDTRACK', '', 'https://i.scdn.co/image/ab67616d0000b2738a76d26762faf3834de9674c', 1),
(69, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196835975557558322/Masaru_Yokoyama_-_MASHLEVERSE.mp3?ex=65b9135c&is=65a69e5c&hm=759e1be505453cd486a25b0b6855c69573167224131f9bbbb33b2748bd8b83be&', 'https://open.spotify.com/intl-id/track/020icJkkGALF1F5QjHGuWP?si=987bc036cef14a69', NULL, NULL, NULL, '', NULL, 0),
(70, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196835975851163658/Masaru_Yokoyama_-_THE_IRON_FIST.mp3?ex=65b9135c&is=65a69e5c&hm=2053dbdbe1c8dee0f5319c562e8bcaafc2d0a14904d567f403a8f224b935afc1&', 'https://open.spotify.com/intl-id/track/5GVcXfPDlxG0rjTG6VWHst?si=b97e893f470f41d5', NULL, NULL, NULL, '', NULL, 0),
(71, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196837591010513057/amazarashi_-_.mp3?ex=65b914dd&is=65a69fdd&hm=a5e3eccae6f7f71ebe328f7e4472097784574285bbb5871efa40c9b19b3d6ee7&', 'https://open.spotify.com/intl-id/track/6dQ3lcQG2MuIQ0P7GSYQeJ?si=aec608e5095d4079', 'Kisetsu wa tsugitsugi shinde iku', NULL, NULL, '', NULL, 0),
(72, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196837591350267934/GRANRODEO_-_Can_Do.mp3?ex=65b914dd&is=65a69fdd&hm=e786305ec97140e0ce6eb5b7f256f44d2588371ecfd1bf93e9d607a70f5cdfc0&', 'https://open.spotify.com/intl-id/track/0mJfQhcSwE5caboPNv9JL1?si=9c43b7955cc844cf', NULL, NULL, NULL, '', NULL, 0),
(73, 'https://cdn.discordapp.com/attachments/1196559163623493742/1196837591660625963/Miho_Fukuhara_-_LET_IT_OUT.mp3?ex=65b914de&is=65a69fde&hm=035b870a14c0111ff2e0180ff2a6212a96edba70077037e4b352b1f1d2da3ccc&', 'https://open.spotify.com/intl-id/track/3O6uWEmwDlkT7EmucuRdvB?si=b5eb19b63b2943d2', NULL, NULL, NULL, '', NULL, 0),
(74, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196845902233616384/SPYAIR_-_.mp3?ex=65b91c9b&is=65a6a79b&hm=9be692555681b48643dfe6974de8f5e8913bdb34fa6133ce2bd9d28a7ee783c3&', 'https://open.spotify.com/intl-id/track/1xc9r9pgQ3CG7iViKPf0cQ?si=54cc310facb04a0c', 'imagination', NULL, NULL, '', NULL, 0),
(75, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196845902535602176/SID_-_.mp3?ex=65b91c9b&is=65a6a79b&hm=cb43469ed87aca0a19c386c9851b194ef4d90bb06a2c055218664e4ca47bc42a&', 'https://open.spotify.com/intl-id/track/5hyuqT3PwqSKtH1EUSST20?si=bbaf389211174051', 'Uso', NULL, NULL, '', NULL, 0),
(76, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196845902858571816/Yamazaru_-_.mp3?ex=65b91c9b&is=65a6a79b&hm=bf6a6b52d736d257c4c14484d56c8f5051468fc3214d46093a09af9712e0740e&', 'https://open.spotify.com/intl-id/track/19yMWtwmIY2IzThaHG4IEV?si=bdae49c85be94633', 'Kaze', NULL, NULL, '', NULL, 0),
(77, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196845903173140540/DISH_-_FLAME.mp3?ex=65b91c9b&is=65a6a79b&hm=d22635ec8aeaffe2d71693db16defeb6d87829797682cf11dff3c3d7c84cf92a&', 'https://open.spotify.com/intl-id/track/68W6SPPczmrZvOL1aiKYCM?si=786302c9f0944b5c', '', NULL, NULL, '', NULL, 0),
(78, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196845903500292166/UNLIMITS_-_.mp3?ex=65b91c9b&is=65a6a79b&hm=2cf2126e4ab6d1524d96b2520cfab3a8cd028919241178cd24a3d36a58897239&', 'https://open.spotify.com/intl-id/track/6imL0aJnaP49YVRT9NOFx8?si=d2c3d5842dbe4bf2', 'cascade', NULL, NULL, '', NULL, 0),
(79, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196848318022684782/Casey_Edwards_Victor_Borba_-_Bury_the_Light.mp3?ex=65b91edb&is=65a6a9db&hm=e09e0500d70b82515c15d9968fe2e37c7b59259bc07e8d1da7865d965cb8a6ba&', 'https://open.spotify.com/intl-id/track/6tUcFEXos6TGhESFlkAyCm?si=8d5f69473d6b4686', NULL, NULL, NULL, '', NULL, 0),
(80, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196848318366613605/Casey_Edwards_-_Bury_the_Light_-_Game_Edit.mp3?ex=65b91edb&is=65a6a9db&hm=2a3c5e5ad4247b1f916643966c99fe8ce8d5699ae4a7a26e9c9af04b42f55dc9&', 'https://open.spotify.com/intl-id/track/3dXSq4GiQcd3HLKe4fi6iz?si=73bc694b61084146', NULL, NULL, NULL, '', NULL, 0),
(81, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196848318760882280/Ludovico_Einaudi_-_Una_Mattina.mp3?ex=65b91edb&is=65a6a9db&hm=8312a0737b8f75a09dfac7cb469cdff7062270e52de9c9ef22e4037d0d3aa676&', 'https://open.spotify.com/intl-id/track/0Dkibk70FDp6t7eOZNemNQ?si=db7dcc48402c479c', NULL, NULL, NULL, '', NULL, 0),
(82, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850757534765136/The_ROOTLESS_-_One_day.mp3?ex=65b92121&is=65a6ac21&hm=144b56e653b1ad326a4b332f0239404cd859083e43d4bec03eaae20cbaca06e5&', 'https://open.spotify.com/intl-id/track/4H4mvjFyIqE5OMYf5zJLBP?si=955c6204ac164f80', NULL, NULL, NULL, '', NULL, 0),
(83, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850757811580938/Sayuri_-_.mp3?ex=65b92121&is=65a6ac21&hm=dbc5f27697a43e5b91a00fd89ef1439ff544d6bf213231612722d0097fd184f0&', 'https://open.spotify.com/intl-id/track/7FOeR1X65DEG4KjpVhNC4Z?si=5ddf841f1c3d403d', 'Sore wa chiisana hikari no youna', NULL, NULL, '', NULL, 0),
(84, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850758289735691/ASIAN_KUNG-FU_GENERATION_-_Re_Re_.mp3?ex=65b92121&is=65a6ac21&hm=5d72af9c35e9789b3920462d458f2111cebecc42b9146011b50137509661a7c1&', 'https://open.spotify.com/intl-id/track/0WaaPFt4Qy8sVfxKz43bCD?si=8cfed39259464691', '', NULL, NULL, '', NULL, 0),
(85, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850758604312747/Daisuke_-_Moshimo.mp3?ex=65b92121&is=65a6ac21&hm=9610fbbcc5d83b7382d8bcab76aa2d2167966de5006c351ab77881c1808a07aa&', 'https://open.spotify.com/intl-id/track/6qcj0oCHsCJSexlsVV6Ghr?si=9272bedd590547cc', '', NULL, NULL, '', NULL, 0),
(86, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850759086649435/Hitsujibungaku_-_more_than_words.mp3?ex=65b92121&is=65a6ac21&hm=8565e6c5723b40fbbdf89ea01631e3371cd17c7f59f5baa89e04b2465514722c&', 'https://open.spotify.com/intl-id/track/2ZT6eELxeETGamaiXu6vmk?si=9351a6109d934e2a', '', NULL, NULL, '', NULL, 0),
(87, 'https://cdn.discordapp.com/attachments/1196840715330781265/1196850759598358579/tacica_-_newsong.mp3?ex=65b92121&is=65a6ac21&hm=43d471df56ee251ec89c29eec32896669f4c175f0f7e8ed76c4749197286f1b3&', 'https://open.spotify.com/intl-id/track/1hGay3CQSuDeE8X95IMV3C?si=70548c6c463c4882', '', NULL, NULL, '', NULL, 0),
(88, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/MAYUKO%20-%20%E5%B7%B1%E3%81%AE%E9%81%B8%E6%8A%9E/Hiroyuki%20Sawano%20-%20Shingeki%20St%20-%20Hrn%20-%20Gt%2020130629%20Kyojin.mp3', 'https://open.spotify.com/intl-id/track/57z8nLE6nNrh9n7rpD9hHn?si=f37525ed872041fd', NULL, NULL, NULL, '', NULL, 0),
(89, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/MAYUKO%20-%20%E5%B7%B1%E3%81%AE%E9%81%B8%E6%8A%9E/MAYUKO%20-%20%E5%B7%B1%E3%81%AE%E9%81%B8%E6%8A%9E.mp3', 'https://open.spotify.com/intl-id/track/2gzHzsT8vbGMC1ejrirI2D?si=476e3ac9b1574496', 'Onore no sentaku', NULL, NULL, '', NULL, 0),
(90, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/MAYUKO%20-%20%E5%B7%B1%E3%81%AE%E9%81%B8%E6%8A%9E/Yuki%20Hayashi%20-%20%E8%84%85%E5%A8%81%E3%81%A8%E3%81%AE%E6%94%BB%E9%98%B2.mp3', 'https://open.spotify.com/intl-id/track/5zuq9eD0nEh0Bq6dR1zGkt?si=254d7312a187447d', 'Fighting against threats', NULL, NULL, '', NULL, 0),
(91, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Hans%20Zimmer%20-%20Where%20We\'re%20Going.mp3', 'https://open.spotify.com/intl-id/track/7e41gTSV9fKdsDqOgS47Wg?si=885392a7538948d6', NULL, NULL, NULL, '', NULL, 0),
(92, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Jeremy_Zuckerman_Avatar_The_Last_Airbender_Premiere_Main_Title.mp3', 'https://open.spotify.com/intl-id/track/6K7gcKToCb3eWshnhTt857?si=ee700df6d2ef4a01', NULL, NULL, NULL, '', NULL, 0),
(93, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Yutaka%20Yamada%20-%20Auferstehung.mp3', 'https://open.spotify.com/intl-id/track/4ThikKBTqg4Ksy9YFocmeN?si=00cb4cd10d1c4bd4', NULL, NULL, NULL, '', NULL, 0),
(94, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Yutaka%20Yamada%20-%20Departure-%20VINLAND%20SAGA%20-.mp3', 'https://open.spotify.com/intl-id/track/0r0qPkPUbYxl3bHC97oebv?si=fe6fbdeb254a42c0', NULL, NULL, NULL, '', NULL, 0),
(95, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Yutaka%20Yamada%20-%20Fire.mp3', 'https://open.spotify.com/intl-id/track/0ZE73JcGD6CGN0gayH4dhM?si=17e7092f0eaa4b66', NULL, NULL, NULL, '', NULL, 0),
(96, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yutaka%20Yamada%20-%20Licht%20und%20Schatten/Yutaka%20Yamada%20-%20Licht%20und%20Schatten.mp3', 'https://open.spotify.com/intl-id/track/0BQNM4jGoWsJ4qN5GQpgmh?si=46e770251ad44ff8', NULL, NULL, NULL, '', NULL, 0),
(97, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/Hiroyuki%20Sawano%20-%20T-Kt.mp3', 'https://open.spotify.com/intl-id/track/4EZytiJfGIKolH85Nc6G8Q?si=6b442fe257484f70', NULL, NULL, NULL, '', NULL, 0),
(98, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/MAYUKO%20-%20%E7%8E%8B%E6%A7%98%E3%83%A9%E3%83%B3%E3%82%AD%E3%83%B3%E3%82%B0%20-%E3%83%A1%E3%82%A4%E3%83%B3%E3%83%86%E3%83%BC%E3%83%9E-.mp3', 'https://open.spotify.com/intl-id/track/1FrE0MDUawjQGqV4rTeOUj?si=ff2934f1f50b4b98', 'King Ranking - Main Theme', NULL, NULL, '', NULL, 0),
(99, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/Shayne%20Orok%20-%20Hadaka%20no%20Yuusha%20(Ousama%20Ranking).mp3', 'https://open.spotify.com/intl-id/track/6dE9yK8wjMZIdiUNtftemo?si=7d6b450344544a14', '', NULL, NULL, '', NULL, 0),
(100, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/Yuki%20Hayashi%20-%20%E3%82%A2%E3%83%B3%E3%83%81%E5%A5%87%E8%B9%9F.mp3', 'https://open.spotify.com/intl-id/track/53B4wsYMDcRI3gX93CiKnB?si=ca02d3ae2fd44830', 'anti miracle', NULL, NULL, '', NULL, 0),
(101, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4.mp3', 'https://open.spotify.com/intl-id/track/6UgPcdNdyLK96zqKs7nBCT?si=157aa74dcd104386', 'Banquet of monsters', NULL, NULL, '', NULL, 0),
(102, 'https://github.com/sibeux/license-sibeux/raw/MyProgram/%E6%97%A5%E6%9C%AC%E3%81%AE%E6%AD%8C/Yuki%20Hayashi%20-%20%E3%83%90%E3%82%B1%E3%83%A2%E3%83%B3%E3%81%9F%E3%81%A1%E3%81%AE%E5%AE%B4/Yuki%20Hayashi,%20Asami%20Tachibana%20-%20%E7%A5%9E%E6%A5%AD%E9%80%9F%E6%94%BB.mp3', 'https://open.spotify.com/intl-id/track/4baWrKxnYjfhJbPoLSOS2K?si=cebda38d895241e5', 'divine haste', NULL, NULL, '', NULL, 0);

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
  MODIFY `id_music` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
