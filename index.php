<?php
define('HOST', 'localhost');
define('SIBEUX', 'sibk1922');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');
$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

// initiate variable
$id_music = 0;
$title = "";
$artist = "";
$album = "";
$cover = "";
$favorite = 0;
$link_drive = "";

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
    <title id="title_doc">iTunein Responsive HTML Template</title>
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
    <link rel="shortcut icon" type="image/png" href="images/favicon.png" />
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
                                        <h1>top songs</h1>
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
                                            $sql_music = "SELECT * FROM music ORDER BY id_music ASC";
                                            $result_music = $db->query($sql_music);
                                            $number_music = 1;

                                            while ($array_data_music = mysqli_fetch_array($result_music)) {

                                                $current_number_music = $number_music;
                                                $id_music = $array_data_music['id_music'];
                                                $favorite = $array_data_music['favorite'];
                                                $link_drive = $array_data_music['link_gdrive'];

                                                if ($array_data_music['link_spotify'] == null) {
                                                    $title = $array_data_music['title'];
                                                    $artist = $array_data_music['artist'];
                                                    $album = $array_data_music['album'];
                                                    $cover = $array_data_music['cover'];

                                                    // get data time from file music
                                                    echo "<script type='module'>
                                                        import { getDataTimeFileMusic } from './js/getDataTime.js';
                                                        getDataTimeFileMusic('{$array_data_music['link_gdrive']}', {$number_music}-1);
                                                    </script>";
                                                } else {
                                                    // get data song from spotify with API
                                                    echo "<script type='module'>
                                                        import { getDataFromAPISpotify } from './js/api.spotify.js';
                                                        getDataFromAPISpotify('{$array_data_music['link_spotify']}', {$number_music}-1);
                                                    </script>";
                                                }
                                            ?>
                                            <ul class="album_inner_list_padding">
                                                <li style="cursor: pointer;"><a><span class="play_no">
                                                            <?php echo $number_music; ?>
                                                        </span>
                                                        <span class="play_hover"
                                                            onclick="animatedPlayMusic(<?php echo $number_music - 1 ?>,'<?php echo $link_drive ?>')"><i
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
                                        <i class="play-pause fas fa-play" id="play-icon"></i>
                                        <i class="fas fa-step-forward"></i>
                                        <i class="fas fa-undo-alt"></i>
                                    </div>

                                    <div class="progress-container">
                                        <!-- <span>0:49</span>
                                        <div class="progress-bar">
                                            <div class="progress"></div>
                                        </div>
                                        <span>3:15</span> -->

                                        <audio id="player_music" autoplay controls>
                                            <source src="" type="audio/mp3">
                                        </audio>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm" style="text-align: right;">
                                One of three columns
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