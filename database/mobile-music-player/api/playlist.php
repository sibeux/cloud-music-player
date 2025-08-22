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
        $sql = "SELECT * FROM music 
                LEFT JOIN metadata_music ON music.id_music = metadata_music.metadata_id_music
                LEFT JOIN cache_music ON music.id_music = cache_music.cache_music_id
                ORDER BY title ASC;";
    }

    // Fetch berdasarkan kategori.
    if ($type == 'category' && $uid != 481) {
        $sql = "SELECT * FROM music
                JOIN playlist ON music.category = playlist.uid
                LEFT JOIN metadata_music ON music.id_music = metadata_music.metadata_id_music
                LEFT JOIN cache_music ON music.id_music = cache_music.cache_music_id
                WHERE music.category = '$uid'
                ORDER BY music.title ASC;
                ";
    }

    if ($type == 'album') {
        $sql = "SELECT * FROM music 
        JOIN playlist ON music.album LIKE CONCAT('%', TRIM(BOTH '\r\n' FROM playlist.name), '%')
        LEFT JOIN metadata_music ON music.id_music = metadata_music.metadata_id_music
        LEFT JOIN cache_music ON music.id_music = cache_music.cache_music_id
        WHERE playlist.uid = '$uid'
        ORDER BY title ASC";
    }

    if ($type == 'playlist') {
        $sql = "SELECT * FROM playlist_music 
        JOIN music on playlist_music.id_music = music.id_music 
        join playlist on playlist_music.id_playlist = playlist.uid 
        LEFT JOIN metadata_music ON music.id_music = metadata_music.metadata_id_music
        LEFT JOIN cache_music ON music.id_music = cache_music.cache_music_id
        WHERE playlist.uid = '$uid' 
        ORDER BY date_add_music_playlist ASC";
    }

    if ($type == 'favorite') {
        $sql = "SELECT * FROM music 
        LEFT JOIN metadata_music ON music.id_music = metadata_music.metadata_id_music
        LEFT JOIN cache_music ON music.id_music = cache_music.cache_music_id
        WHERE favorite = '1' ORDER BY title ASC";
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
    $sql = "SELECT * FROM recents_music join music on music.id_music = recents_music.uid_music ORDER BY played_at DESC";
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