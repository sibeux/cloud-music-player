<?php
global $db;
ob_start('ob_gzhandler'); // aktifkan gzip (opsional)
header('Content-Type: application/json; charset=utf-8');
// Sementara
ini_set('memory_limit', '256M'); // atau '512M' kalau perlu
include './connection.php';

$sql = "SELECT * FROM playlist ORDER BY pin";

if (isset($_GET['sort']) && isset($_GET['filter'])) {
    $sort = $_GET['sort'];
    $filter = $_GET['filter'] == '' ? null : $_GET['filter'];

    if ($sort == 'uid') {
        $sql = "SELECT * FROM playlist WHERE LENGTH('$filter') = 0 OR type = '$filter' ORDER BY pin, date_pin asc, date desc";
    } else if ($sort == 'title') {
        $sql = "SELECT * FROM playlist WHERE LENGTH('$filter') = 0 OR type = '$filter' ORDER BY pin, date_pin asc, name asc";
    }
}

if (isset($_GET['type']) && isset($_GET['uid'])) {

    $type = $_GET['type'];
    $uid = $_GET['uid'];

    // Fetch semua lagu.
    if ($type == 'category' && $uid == 481) {
        $sql = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.favorite, m.uploader, m.is_suspicious,
        p.name as album,
        mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color
        FROM music m
        LEFT JOIN metadata_music mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_music ON m.id_music = cache_music.cache_music_id
        LEFT JOIN dominant_color dc on m.cover = dc.image_url
        ORDER BY m.title ASC;";
    }

    // Fetch berdasarkan kategori.
    if ($type == 'category' && $uid != 481) {
        $sql = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.favorite, m.uploader, m.is_suspicious,
        p.name as album,
        mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color
        FROM music m
        JOIN album_music on album_music.id_music = m.id_music
        JOIN playlist p ON album_music.id_playlist = p.uid
        LEFT JOIN metadata_music mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_music ON m.id_music = cache_music.cache_music_id
        LEFT JOIN dominant_color dc on m.cover = dc.image_url
        WHERE m.category = '$uid'
        ORDER BY m.title ASC;";
    }

    if ($type == 'album') {
        $sql = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.favorite, m.uploader, m.is_suspicious,
        p.name as album,
        mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color
        FROM music m
        -- JOIN playlist ON music.album LIKE CONCAT('%', TRIM(BOTH '\r\n' FROM playlist.name), '%')
        JOIN album_music on album_music.id_music = m.id_music
        JOIN playlist p on album_music.id_playlist = p.uid
        LEFT JOIN metadata_music mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_music ON m.id_music = cache_music.cache_music_id
        LEFT JOIN dominant_color dc on m.cover = dc.image_url
        WHERE p.uid = '$uid'
        ORDER BY m.title ASC";
    }

    if ($type == 'playlist') {
        $sql = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.favorite, m.uploader, m.is_suspicious,
        p.name as album,
        mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        playlist_music.date_add_music_playlist
        FROM playlist_music 
        JOIN music m on playlist_music.id_music = m.id_music 
        JOIN playlist p on playlist_music.id_playlist = p.uid 
        LEFT JOIN metadata_music mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_music ON m.id_music = cache_music.cache_music_id
        LEFT JOIN dominant_color dc on m.cover = dc.image_url
        WHERE p.uid = '$uid' 
        ORDER BY date_add_music_playlist ASC";
    }

    if ($type == 'favorite') {
        $sql = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.favorite, m.uploader, m.is_suspicious,
        p.name as album,
        mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color
        FROM music 
        LEFT JOIN metadata_music mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_music ON m.id_music = cache_music.cache_music_id
        LEFT JOIN dominant_color dc on m.cover = dc.image_url
        WHERE m.favorite = '1' ORDER BY m.title ASC";
    }

}

if (isset($_GET['count_favorite'])) {
    $sql = "SELECT COUNT(*) as count_favorite FROM music where favorite = '1'";
}

if (isset($_GET['count_category'])) {
    $sql = "SELECT playlist.uid, COALESCE(COUNT(music.category), 0) AS type_count
    FROM playlist
    LEFT JOIN music ON music.category = playlist.uid
    WHERE playlist.type = 'category'
    GROUP BY playlist.uid
    ORDER BY playlist.uid ASC";
}

if (isset($_GET['play_playlist'])) {
    $uid = $_GET['play_playlist'];

    $sql = "UPDATE playlist SET date = NOW() WHERE uid = '$uid'";
}

if (isset($_GET['recents_music'])) {
    $sql = "SELECT * 
    FROM recents_music 
    join music on music.id_music = recents_music.uid_music 
    ORDER BY played_at DESC";
}

// Response json
// ... $sql Anda didefinisikan di sini ...
$result = $db->query($sql);

// Langkah 1: Tangani kegagalan query dengan benar
if (!$result) {
    http_response_code(500);
    // Matikan skrip dan kirim pesan error yang jelas dari database
    die(json_encode(["error" => "Query failed", "detail" => $db->error]));
}

// Langkah 2: Kumpulkan semua baris hasil ke dalam sebuah array
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Langkah 3: Ubah seluruh array menjadi JSON dalam satu kali proses
header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Tutup koneksi setelah semua selesai
$db->close();
exit;