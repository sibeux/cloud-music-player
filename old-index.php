<?php
include "./database/db.php";

function deleteJSON()
{
    // include "./json/delete.php";

    // delete all data in json file
    // Path to the JSON file
    $jsonFilePath = './json/music.json';

    // Open the JSON file for writing
    $file = fopen($jsonFilePath, 'w');

    // Truncate the file (erase its contents)
    ftruncate($file, 0);

    // Close the file
    fclose($file);
}
;

deleteJSON();

// File json yang akan dibaca
$file = "./json/music.json";

// Mendapatkan file json
$anggota = file_get_contents($file);

// Mendecode anggota.json
$data = json_decode($anggota, true);

// initiate variable
$id_music = 0;
$title = "";
$artist = "";
$album = "";
$cover = "";
$favorite = 0;
$link_drive = "";

$gdrive_api_query = $db->query("SELECT gdrive_api FROM API");
$gdrive_api_key = mysqli_fetch_assoc($gdrive_api_query);

function checkUrlFromDrive(string $url_db, string $gdrive_api_key)
{
    if (strpos($url_db, "drive.google.com") !== false) {
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url_db, $matches);
        return "https://www.googleapis.com/drive/v3/files/{$matches[1]}?alt=media&key={$gdrive_api_key}";
    } else {
        return $url_db;
    }

}
?>

<!DOCTYPE html>
<!-- 
Template Name: Tunein
Version: 1.0.0
Author:Webstrot 

-->
<!--[if !IE]><!-->
<html lang="zxx">
<!--[endif]-->

<head>
    <meta charset="utf-8" />
    <!-- <title id="title_doc">iTunein Responsive HTML Template</title> -->
    <title id="title_doc">Cybeat - Music Player </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="description" content="Tunein,music,song" />
    <meta name="keywords" content="Tunein,music,song" />
    <meta name="author" content="" />
    <meta name="MobileOptimized" content="320" />
    <!--Template style -->
    <link rel="stylesheet" type="text/css" href="css/animate.css" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="css/flaticon.css" />
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
    <link rel="stylesheet" type="text/css" href="css/owl.carousel.css">
    <link rel="stylesheet" type="text/css" href="css/owl.theme.default.css">
    <link rel="stylesheet" type="text/css" href="css/nice-select.css" />
    <link rel="stylesheet" type="text/css" href="css/swiper.css" />
    <link rel="stylesheet" type="text/css" href="css/magnific-popup.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/responsive.css" />
    <!--favicon-->
    <!-- <link id="title_icon" rel="shortcut icon" type="image/png" href="images/favicon.png" /> -->
    <link id="title_icon" rel="shortcut icon" type="image/x-icon" href="images/cybeat.png" />
</head>

<body class="index4_body_wrapper">
    <!-- top navi wrapper Start -->
    <div class="m24_main_wrapper">

        <div class="row">
            <div class="col-2" style="background-color: #1fd660;">

            </div>
            <div class="col-10">
                <!-- Main section Start -->
                <div class="body_main_header">
                    <!-- top songs wrapper start -->
                    <div class="top_songs_wrapper">
                        <div class="row">
                            <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12 fixed_top_songs">
                                <div class="song_heading_wrapper ms_cover">
                                    <div class="ms_heading_wrapper white_heading_wrapper">
                                        <h1>日本の歌</h1>
                                    </div>
                                    <div>
                                        <div class="album_list_wrapper album_list_wrapper_shop">
                                            <ul class="album_list_name ms_cover">
                                                <li>#</li>
                                                <li class="song_title_width">Judul</li>
                                                <li class="song_title_width">Album</li>
                                                <li class="text-center">time</li>
                                                <li class="text-center">Favorite</li>

                                                <li class="text-center">More</li>
                                            </ul>
                                            <?php
                                            $sql_music = "SELECT * FROM music ORDER BY title ASC";
                                            $result_music = $db->query($sql_music);

                                            $count_music = mysqli_num_rows($result_music);

                                            $number_music = 1;

                                            while ($array_data_music = mysqli_fetch_array($result_music)) {

                                                $link_drive = checkUrlFromDrive($array_data_music['link_gdrive'], $gdrive_api_key['gdrive_api']);
                                                $current_number_music = $number_music;
                                                $id_music = $array_data_music['id_music'];
                                                $category = $array_data_music['category'];
                                                $title = $array_data_music['title'];
                                                $artist = $array_data_music['artist'];
                                                $favorite = $array_data_music['favorite'];
                                                $album = $array_data_music['album'];
                                                $time = $array_data_music['time'];
                                                $cover = $array_data_music['cover'];
                                                $date_added = $array_data_music['date_added'];

                                                // Data array baru
                                                $data[] = array(
                                                    'id_music' => $number_music,
                                                    'uid' => $id_music,
                                                    'category' => $category,
                                                    'link' => $link_drive,
                                                    'title' => $title,
                                                    'artist' => $artist,
                                                    'album' => $album,
                                                    'time' => $time,
                                                    'cover' => $cover,
                                                    'favorite' => $favorite,
                                                    'date_added' => $date_added,
                                                );

                                                // Mengencode data menjadi json
                                                $jsonfile = json_encode($data, JSON_PRETTY_PRINT);

                                                // Menyimpan data ke dalam anggota.json
                                                $anggota = file_put_contents($file, $jsonfile);

                                                if ($array_data_music['link_spotify'] == null) {

                                                    // cut string title if too long
                                                    if (strlen($array_data_music['title']) > 25) {
                                                        $title = substr($array_data_music['title'], 0, 25) . "...";
                                                    } else {
                                                        $title = $array_data_music['title'];
                                                    }

                                                    // cut string artist if too long
                                                    if (strlen($array_data_music['artist']) > 35) {
                                                        $artist = substr($array_data_music['artist'], 0, 35) . "...";
                                                    } else {
                                                        $artist = $array_data_music['artist'];
                                                    }

                                                    // cut string album if too long
                                                    if (strlen($array_data_music['album']) > 80) {
                                                        $album = substr($array_data_music['album'], 0, 80) . "...";
                                                    } else {
                                                        $album = $array_data_music['album'];
                                                    }

                                                    $cover = checkUrlFromDrive($array_data_music['cover'], $gdrive_api_key['gdrive_api']);
                                                    $time = $array_data_music['time'];

                                                    // get data time from file music
                                                    // echo "<script type='module'>
                                                    //     import { getDataTimeFileMusic } from './js/getDataTime.js';
                                                    //     getDataTimeFileMusic('{$array_data_music['link_gdrive']}', {$number_music}-1);
                                                    // </script>";
                                            
                                                } else {
                                                    echo "<script type='module'>
                                                        import { getDataFromAPISpotify } from './js/api.spotify.js';
                                                        getDataFromAPISpotify('{$array_data_music['link_spotify']}', {$number_music}-1, 'null');
                                                    </script>";
                                                }
                                                ?>
                                            <ul class="album_inner_list_padding">
                                                <li style="cursor: pointer;"><a><span class="play_no">
                                                            <?php echo $number_music; ?>
                                                        </span>
                                                        <span class="play_hover"
                                                            onclick="animatedPlayMusic(<?php echo $number_music - 1 ?>,'<?php echo $link_drive ?>','<?php echo $count_music ?>', '<?php echo $id_music ?>')"><i
                                                                class="flaticon-play-button"></i></span></a>
                                                </li>
                                                <li class="song_title_width">
                                                    <div class="top_song_artist_wrapper">

                                                        <img src="<?php echo $cover ?>" alt="img" class="cover_music">

                                                        <div class="top_song_artist_contnt">
                                                            <h1><a style="cursor: pointer;" class="title_music">
                                                                    <?php echo $title ?>
                                                                </a></h1>
                                                            <p class="various_artist_text"><a class="artist_music">
                                                                    <?php echo $artist ?>
                                                                </a>
                                                            </p>
                                                        </div>

                                                    </div>
                                                </li>
                                                <li class="song_title_width"><a class="album_music">
                                                        <?php echo $album ?>
                                                    </a>
                                                </li>
                                                <li class="text-center"><a class="time_music"><?php echo $time ?></a>
                                                </li>
                                                <li class="text-center favorite-text-center">
                                                    <?php
                                                        // initiate variable $favorite
                                                        $is_favorite = $favorite;
                                                        if ($is_favorite == 1) { ?>
                                                    <i class="fas fa-heart"
                                                        onclick="changeFavoriteButton(<?php echo $current_number_music - 1 ?>)"
                                                        style="color: #1fd660;"></i>
                                                    <?php } else { ?>
                                                    <i class="far fa-heart"
                                                        onclick="changeFavoriteButton(<?php echo $current_number_music - 1 ?>)"
                                                        style="color: #fff;"></i>
                                                    <?php } ?>
                                                </li>
                                                <li class="text-center top_song_artist_playlist">
                                                    <div class="ms_tranding_more_icon">
                                                        <i class="flaticon-menu" style="color: white;"></i>
                                                    </div>
                                                    <ul class="tranding_more_option">
                                                        <li><a href="#"><span class="opt_icon"><i
                                                                        class="flaticon-playlist"></i></span>Add To
                                                                playlist</a>
                                                        </li>
                                                        <li><a href="#"><span class="opt_icon"><i
                                                                        class="flaticon-star"></i></span>favourite</a>
                                                        </li>
                                                        <li><a href="#"><span class="opt_icon"><i
                                                                        class="flaticon-share"></i></span>share</a></li>
                                                        <li><a href="#"><span class="opt_icon"><i
                                                                        class="flaticon-files-and-folders"></i></span>view
                                                                lyrics</a></li>
                                                        <li><a href="#"><span class="opt_icon"><i
                                                                        class="flaticon-trash"></i></span>delete</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                            <?php
                                                $number_music++;
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- top navi wrapper end -->
    </div>
    <!-- top songs wrapper end -->

    <!-- footer Wrapper start -->
    <div class="foter_top_wrapper footer_top_wrapper2 ms_cover">
        <ul>
            <li><a href="#"><i class="fab fa-facebook-f"></i></a>
            </li>
            <li><a href="#"><i class="fab fa-twitter"></i></a>
            </li>
            <li><a href="#"><i class="fab fa-instagram"></i></a>
            </li>
            <li> <a href="#"><i class="fab fa-linkedin-in"></i></a> </li>

            <li><a href="#"><i class="fab fa-google-plus-g"></i></a>
            </li>
            <li><a href="#"><i class="fab fa-pinterest-p"></i></a>
            </li>
            <li><a href="#"><i class="fab fa-tumblr"></i></a>
            </li>
            <li> <a href="#"><i class="fab fa-behance"></i></a> </li>
            <li> <a href="#"><i class="fab fa-dribbble"></i></a> </li>
            <li> <a href="#"><i class="fab fa-whatsapp"></i></a> </li>
        </ul>
    </div>
    <div class="section2_bottom_wrapper index4_bottom_wrapper ms_cover">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
                <div class="btm_foter_box">
                    <p>Copyright © 2024 <a href="index.html" style="text-decoration: underline;"> SIBEUX </a>
                        Template downloaded from <a href="#">
                            Envato.</a></p>
                </div>
            </div>
        </div>
    </div>

    <!--footer wrapper end-->

    <!-- playllist wrapper start -->
    <div class="adonis-player-wrap index3_adoins_wrapper index4_playlist_wrap">
        <div id="adonis_jp_container" class="master-container-holder" role="application" aria-label="media player">
            <div id="adonis_jplayer_main" class="jp-jplayer"></div>
            <div class="adonis-player-horizontal">
                <div class="row adonis-player">
                    <div class="jp_controls_wrapper">
                        <div class="row">
                            <div class="col-sm">
                                <div class="row">
                                    <div class="col-10">
                                        <div class="song-infos">
                                            <div class="image-container">
                                                <img src="https://d2y6mqrpjbqoe6.cloudfront.net/image/upload/f_auto,q_auto/media/library-400/216_636967437355378335Your_Lie_Small_hq.jpg"
                                                    alt="" id="cover_now_play" />
                                            </div>
                                            <div class="song-description">
                                                <p class="title" id="title">
                                                    Watashitachi wa Sou Yatte Ikite Iku Jinshu na no
                                                </p>
                                                <p class="artist" id="artist">Masaru Yokoyama</p>
                                            </div>
                                            <i class="fa fa-heart" id="favorite-music-bar" style="color: #1fd660;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- progress bar -->
                            <div class="col-sm" style="text-align: center;">
                                <div class="progress-controller">
                                    <div class="control-buttons">
                                        <i class="fas fa-random"></i>
                                        <i class="fas fa-step-backward"></i>
                                        <i class="play-pause fas fa-play" id="play-icon" onclick="playPause()"></i>
                                        <i class="fas fa-step-forward" id="next-music"></i>
                                        <i class="fas fa-undo-alt"></i>
                                    </div>

                                    <div class="progress-container">
                                        <span id="first-minute">0:00</span>
                                        <div class=" progress-bar">
                                            <input onmouseenter="green(this)" onmouseleave="white(this)" type="range"
                                                value="0" id="range" />
                                        </div>
                                        <span id="last-minute">0:00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm" style="text-align: right;">
                                <audio controls id="player_music">
                                    <source src="" type="audio/mp3">
                                </audio>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- playlist wrapper end -->
    <!--custom js files-->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/modernizr.js"></script>
    <script src="js/plugin.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery.inview.min.js"></script>
    <script src="js/jquery.magnific-popup.js"></script>
    <script src="js/swiper.min.js"></script>
    <script src="js/comboTreePlugin.js"></script>
    <script src="js/mp3/jquery.jplayer.min.js"></script>
    <script src="js/mp3/jplayer.playlist.js"></script>
    <script src="js/owl.carousel.js"></script>
    <script src="js/mp3/player.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/progress.bar.music.js"></script>
    <!-- custom js-->

</body>

</html>