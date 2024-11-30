<?php
include "./database/db.php";
// load_more_music.php
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql_music = "SELECT * FROM music ORDER BY title ASC LIMIT $limit OFFSET $offset";
$sql_count_music = "SELECT COUNT(*) FROM music";

$result_music = $db->query($sql_music);
$result_count_music = $db->query($sql_count_music);

$count_music = mysqli_fetch_array($result_count_music)['jumlah_music'];

$number_music = 1;

$music_data = [];
while ($array_data_music = mysqli_fetch_array($result_music)) {
    // Proses data seperti yang Anda lakukan sebelumnya
    $link_drive = checkUrlFromDrive($array_data_music['link_gdrive'], $api_key);
    $current_number_music = $number_music;
    $id_music = $array_data_music['id_music'];
    $category = $array_data_music['category'];
    $title = $array_data_music['title'];
    $artist = $array_data_music['artist'];
    $favorite = $array_data_music['favorite'];
    $album = $array_data_music['album'];
    $time = $array_data_music['time'];
    $date_added = $array_data_music['date_added'];
    $cover = checkUrlFromDrive($array_data_music['cover'], $api_key);
    
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

header('Content-Type: application/json');
echo
    json_encode(
        $music_data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE

    );