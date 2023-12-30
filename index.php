<?php
define('HOST', 'localhost');
define('SIBEUX', 'sibk1922');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');
$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
} else {
    echo "Connected successfully";
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
    <title>Tunein Responsive HTML Template</title>
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
        <div id="sidebar" class="index4_sidebar index3_sidebar">
            <div class="l-sidebar l2_sidebar">
                <div
                    class="c-header-icon js-hamburger crm_responsive_toggle d-block d-sm-block d-md-block d-lg-block d-xl-none">
                    <div class="hamburger-toggle">
                        <div class="toogle_clse">×</div>
                    </div>
                </div>
                <div id='cssmenu'>
                    <a href="index.html"><img src="images/logo3.png" alt="logo"></a>
                    <ul class="sidebb">
                        <li class='has-sub'><a href='#'><i class="flaticon-home"></i>index</a>
                            <ul>
                                <li>
                                    <a href="index.html"> <i class="flaticon-home"></i>index I</a>
                                </li>
                                <li><a href="index2.html"><i class="flaticon-home"></i>index II</a></li>
                                <li><a href="index3.html"><i class="flaticon-home"></i>index III</a></li>
                                <li><a href="index4.html"><i class="flaticon-home"></i>index IV</a></li>

                            </ul>
                        </li>
                        <li class='has-sub'><a href='#'><i class="flaticon-album"></i>albums</a>
                            <ul>
                                <li>
                                    <a href="album.html"> <i class="flaticon-vinyl"></i>album</a>
                                </li>
                                <li><a href="album_list.html"><i class="flaticon-playlist-1"></i>album list</a></li>
                                <li><a href="artist.html"><i class="flaticon-headphones"></i>artist</a></li>
                                <li><a href="artist_single.html"><i class="flaticon-speaker"></i>artist single</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- top navi wrapper end -->

        <!-- Main section Start -->
        <div class="body_main_header">

            <!-- top songs wrapper start -->
            <div class="top_songs_wrapper index3_top_songs_wrapper  index4_top_songs_wrapper ms_cover">
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
                                    <ul class="album_inner_list_padding">
                                        <li><a href="#"><span class="play_no">01</span><span class="play_hover"><i
                                                        class="flaticon-play-button"></i></span></a></li>
                                        <li class="song_title_width">
                                            <div class="top_song_artist_wrapper">

                                                <img src="images/tp1.png" alt="img">

                                                <div class="top_song_artist_contnt">
                                                    <h1><a href="#">Let me Love You</a></h1>
                                                    <p class="various_artist_text"><a href="#">Chrissy Costanza</a></p>
                                                </div>

                                            </div>
                                        </li>
                                        <li class="song_title_width"><a href="#">Kabir Singh — Arijit Singh 2019</a>
                                        </li>
                                        <li class="text-center"><a href="#">3:26</a></li>
                                        <li class="text-center favorite-text-center">
                                            <?php
                                            // initiate variable $favorite
                                            $favorite = false;
                                            $id_music = 0;
                                            if ($favorite) { ?>
                                            <i class="fas fa-heart"
                                                onclick="changeFavoriteButton(<?php echo $id_music ?>)"
                                                style="color: #1fd660;"></i>
                                            <?php } else { ?>
                                            <i class="far fa-heart"
                                                onclick="changeFavoriteButton(<?php echo $id_music ?>)"
                                                style="color: #fff;"></i>
                                            <?php } ?>

                                        </li>
                                        <li class="text-center top_song_artist_playlist">
                                            <div class="ms_tranding_more_icon">
                                                <i class="flaticon-menu" style="color: white;"></i>
                                            </div>
                                            <ul class="tranding_more_option">
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-playlist"></i></span>Add To playlist</a>
                                                </li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-star"></i></span>favourite</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-share"></i></span>share</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-files-and-folders"></i></span>view
                                                        lyrics</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-trash"></i></span>delete</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <ul class="album_inner_list_padding">
                                        <li><a href="#"><span class="play_no">02</span><span class="play_hover"><i
                                                        class="flaticon-play-button"></i></span></a></li>
                                        <li class="song_title_width">
                                            <div class="top_song_artist_wrapper">

                                                <img src="images/tp2.png" alt="img">

                                                <div class="top_song_artist_contnt">
                                                    <h1><a href="#">l wanna you</a></h1>
                                                    <p class="various_artist_text"><a href="#">Chrissy artist</a></p>
                                                </div>

                                            </div>
                                        </li>
                                        <li class="song_title_width"><a href="#">Kabir Singh — Arijit Singh 2019</a>
                                        </li>
                                        <li class="text-center"><a href="#">3:26</a></li>
                                        <<li class="text-center favorite-text-center">
                                            <i class="fa fa-heart" id="favorite-icon" style="color: #1fd660;"></i>
                                            </li>
                                            <li class="text-center top_song_artist_playlist">
                                                <div class="ms_tranding_more_icon">
                                                    <i class="flaticon-menu"></i>
                                                </div>
                                                <ul class="tranding_more_option">
                                                    <li><a href="#"><span class="opt_icon"><i
                                                                    class="flaticon-playlist"></i></span>Add To
                                                            playlist</a>
                                                    </li>
                                                    <li><a href="#"><span class="opt_icon"><i
                                                                    class="flaticon-star"></i></span>favourite</a></li>
                                                    <li><a href="#"><span class="opt_icon"><i
                                                                    class="flaticon-share"></i></span>share</a></li>
                                                    <li><a href="#"><span class="opt_icon"><i
                                                                    class="flaticon-files-and-folders"></i></span>view
                                                            lyrics</a></li>
                                                    <li><a href="#"><span class="opt_icon"><i
                                                                    class="flaticon-trash"></i></span>delete</a></li>
                                                </ul>
                                            </li>
                                    </ul>
                                    <ul class="album_inner_list_padding">
                                        <li><a href="#"><span class="play_no">03</span><span class="play_hover"><i
                                                        class="flaticon-play-button"></i></span></a></li>
                                        <li class="song_title_width">
                                            <div class="top_song_artist_wrapper">

                                                <img src="images/tp3.png" alt="img">

                                                <div class="top_song_artist_contnt">
                                                    <h1><a href="#">Let me Love You</a></h1>
                                                    <p class="various_artist_text"><a href="#">Chrissy Costanza</a></p>
                                                </div>

                                            </div>
                                        </li>
                                        <li class="song_title_width"><a href="#">Kabir Singh — Arijit Singh 2019</a>
                                        </li>
                                        <li class="text-center"><a href="#">3:26</a></li>
                                        <li class="text-center favorite-text-center">
                                            <i class="far fa-heart" id="favorite-icon" style="color: #fff;"></i>
                                        </li>
                                        <li class="text-center top_song_artist_playlist">
                                            <div class="ms_tranding_more_icon">
                                                <i class="flaticon-menu"></i>
                                            </div>
                                            <ul class="tranding_more_option">
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-playlist"></i></span>Add To playlist</a>
                                                </li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-star"></i></span>favourite</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-share"></i></span>share</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-files-and-folders"></i></span>view
                                                        lyrics</a></li>
                                                <li><a href="#"><span class="opt_icon"><i
                                                                class="flaticon-trash"></i></span>delete</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                                    alt="" />
                                            </div>
                                            <div class="song-description">
                                                <p class="title">
                                                    Watashitachi wa Sou Yatte Ikite Iku Jinshu na no
                                                </p>
                                                <p class="artist">Masaru Yokoyama</p>
                                            </div>
                                            <i class="fa fa-heart" id="favorite-icon" style="color: #1fd660;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                        <span>0:49</span>
                                        <div class="progress-bar">
                                            <div class="progress"></div>
                                        </div>
                                        <span>3:15</span>
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