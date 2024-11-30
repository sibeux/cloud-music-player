<?php
include "./database/db.php";
// load_more_music.php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql_music = "SELECT * FROM music ORDER BY title ASC LIMIT $limit OFFSET $offset";
$result_music = $db->query($sql_music);

$music_data = [];
while ($array_data_music = mysqli_fetch_array($result_music)) {
// Proses data seperti yang Anda lakukan sebelumnya
$data = [
'artist' => addslashes($array_data_music['artist']),
'category' => $array_data_music['category'],
'cover' => $array_data_music['cover'],
'date_added' => $array_data_music['date_added'],
'favorite' => $array_data_music['favorite'],
'id_music' => $array_data_music['id_music'],
'link_gdrive' => $array_data_music['link_gdrive'],
'time' => $array_data_music['time'],
'title' => addslashes($array_data_music['title']),
];

$music_data[] = $data;
}

echo htmlspecialchars(
    json_encode(
        $music_data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ),
    ENT_QUOTES,
    'UTF-8'
);