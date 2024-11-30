<?php
include "./database/db.php";
// URL API
$url = "https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/gdrive_api.php";

// Ambil data API
$response = file_get_contents($url);

// Cek apakah response berhasil diambil
if ($response === FALSE) {
    die('Error occurred while accessing the API.');
}
// Ubah JSON menjadi array PHP
$data = json_decode($response, true);
$api_key = $data[0]['gdrive_api'];

function checkUrlFromDrive(string $url_db, string $gdrive_api_key)
{
    if (strpos($url_db, "drive.google.com") !== false) {
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url_db, $matches);
        return "https://www.googleapis.com/drive/v3/files/{$matches[1]}?alt=media&key={$gdrive_api_key}";
    } else {
        return $url_db;
    }

}
// load_more_music.php
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 100;
$offset = ($page - 1) * $limit;

$sql_music = "SELECT * FROM music ORDER BY title ASC LIMIT $limit OFFSET $offset";
$sql_count_music = "SELECT COUNT(*) FROM music";

$result_music = $db->query($sql_music);
$result_count_music = $db->query($sql_count_music);

$count_music = mysqli_fetch_array($result_count_music)['jumlah_music'];

$number_music = $offset + 1;

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

    $music_data = htmlspecialchars(
        json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ),
        ENT_QUOTES,
        'UTF-8'
    );

    echo '<ul class="album_inner_list_padding">
    <li style="cursor: pointer;">
        <a><span class="play_no">' . $number_music . '</span>
        <span class="play_hover" onclick="animatedPlayMusic(' . ($number_music - 1) . ',\'' . $link_drive . '\',\'' . $count_music . '\', \'' . $id_music . '\', \'' . $music_data . '\')">
            <i class="flaticon-play-button"></i>
        </span></a>
    </li>
    <li class="song_title_width">
        <div class="top_song_artist_wrapper">
            <img src="' . $cover . '" alt="img" class="cover_music">
            <div class="top_song_artist_contnt">
                <h1><a style="cursor: pointer;" class="title_music">' . $title . '</a></h1>
                <p class="various_artist_text"><a class="artist_music">' . $artist . '</a></p>
            </div>
        </div>
    </li>
    <li class="song_title_width"><a class="album_music">' . $album . '</a></li>
    <li class="text-center"><a class="time_music">' . $time . '</a></li>
    <li class="text-center favorite-text-center">';

    if ($favorite == 1) {
        echo '<i class="fas fa-heart" onclick="changeFavoriteButton(' . ($current_number_music - 1) . ')" style="color: #1fd660;"></i>';
    } else {
        echo '<i class="far fa-heart" onclick="changeFavoriteButton(' . ($current_number_music - 1) . ')" style="color: #fff;"></i>';
    }

    echo '</li>
    <li class="text-center top_song_artist_playlist">
        <div class="ms_tranding_more_icon">
            <i class="flaticon-menu" style="color: white;"></i>
        </div>
        <ul class="tranding_more_option">
            <li><a href="#"><span class="opt_icon"><i class="flaticon-playlist"></i></span>Add To playlist</a></li>
            <li><a href="#"><span class="opt_icon"><i class="flaticon-star"></i></span>favourite</a></li>
            <li><a href="#"><span class="opt_icon"><i class="flaticon-share"></i></span>share</a></li>
            <li><a href="#"><span class="opt_icon"><i class="flaticon-files-and-folders"></i></span>view lyrics</a></li>
            <li><a href="#"><span class="opt_icon"><i class="flaticon-trash"></i></span>delete</a></li>
        </ul>
    </li>
</ul>';
}

// header('Content-Type: application/json');
// echo
//     json_encode(
//         $music_data,
//         JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE

//     );