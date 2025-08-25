<?php
// Bagian Setup dan API Key (sudah cukup baik, sedikit perbaikan)
include "./database/db.php";
include "./func.php"; // Diasumsikan berisi fungsi compressImage()

// Fungsi untuk mendapatkan API key (lebih baik menggunakan cURL untuk timeout)
function getApiKey() {
    $url = "https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/gdrive_api.php";
    $response = @file_get_contents($url); // Gunakan @ untuk menekan warning jika gagal

    if ($response === false) {
        // Sebaiknya tidak menggunakan die() di production, tapi log error
        error_log("Gagal mengakses API gdrive_api.php");
        return null; // Kembalikan null jika gagal
    }

    $data = json_decode($response, true);
    $gmailData = array_filter($data, fn($item) => str_contains($item['email'], '@gmail.com'));

    if (empty($gmailData)) {
        error_log("Tidak ada Gmail API key yang ditemukan.");
        return null;
    }

    $gmailData = array_values($gmailData);
    return $gmailData[array_rand($gmailData)]['gdrive_api'];
}

// Fungsi untuk konversi URL Google Drive
function checkUrlFromDrive(string $url_db, string $gdrive_api_key) {
    if (str_contains($url_db, "drive.google.com") && preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url_db, $matches)) {
        return "https://www.googleapis.com/drive/v3/files/{$matches[1]}?alt=media&key={$gdrive_api_key}";
    }
    return $url_db;
}

$api_key = getApiKey();
if (!$api_key) {
    die('Tidak dapat mengambil API Key. Halaman tidak dapat dimuat.');
}

// ---- LOGIKA UNTUK MENAMPILKAN DAFTAR MUSIK ----
// Fungsi ini akan kita gunakan juga di file `load_more.php`
function render_music_list($db, $api_key, $page = 1) {
    $limit = 100;
    $offset = ($page - 1) * $limit;
    
    // [OPTIMASI KEAMANAN] Gunakan Prepared Statements untuk mencegah SQL Injection
    $stmt = $db->prepare("SELECT * FROM music ORDER BY title ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result_music = $stmt->get_result();

    $number_music = $offset + 1; // Penomoran dimulai dari offset
    
    // Loop dan render setiap baris musik
    while ($music = $result_music->fetch_assoc()) {
        // [OPTIMASI PERFORMA] Pindahkan kompresi gambar ke proses background/cron job.
        // Di sini, kita hanya akan mengubah URL-nya.
        $image_url = checkUrlFromDrive($music['cover'], $api_key);
        // $cover = compressImage($image_url); // HINDARI INI! Anggap saja URL sudah optimal.
        $cover = $image_url; // Langsung gunakan URL asli atau URL thumbnail jika ada.

        // [OPTIMASI TAMPILAN] Siapkan data untuk `data-*` attributes
        $music_data_json = htmlspecialchars(json_encode([
            'id_music'    => $music['id_music'],
            'artist'      => $music['artist'],
            'title'       => $music['title'],
            'cover'       => $cover,
            'link_gdrive' => checkUrlFromDrive($music['link_gdrive'], $api_key),
            'favorite'    => $music['favorite'],
            'time'        => $music['time']
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        
        // [OPTIMASI KEAMANAN] Gunakan htmlspecialchars untuk mencegah XSS
        $title = htmlspecialchars($music['title'], ENT_QUOTES, 'UTF-8');
        $artist = htmlspecialchars($music['artist'], ENT_QUOTES, 'UTF-8');
        $album = htmlspecialchars($music['album'], ENT_QUOTES, 'UTF-8');
        $time = htmlspecialchars($music['time'], ENT_QUOTES, 'UTF-8');
        
        $is_favorite = (int)$music['favorite'] === 1;
        $heart_class = $is_favorite ? 'fas fa-heart' : 'far fa-heart';
        $heart_color = $is_favorite ? 'color: #1fd660;' : 'color: #fff;';

        // Gunakan sintaks Heredoc untuk HTML yang lebih bersih
        echo <<<HTML
        <ul class="album_inner_list_padding music-item" data-music-json='{$music_data_json}'>
            <li>
                <a>
                    <span class="play_no">{$number_music}</span>
                    <span class="play_hover"><i class="flaticon-play-button"></i></span>
                </a>
            </li>
            <li class="song_title_width">
                <div class="top_song_artist_wrapper">
                    <img data-src="{$cover}" src="images/placeholder.gif" alt="Cover for {$title}" class="cover_music lazy">
                    <div class="top_song_artist_contnt">
                        <h1><a class="title_music">{$title}</a></h1>
                        <p class="various_artist_text"><a class="artist_music">{$artist}</a></p>
                    </div>
                </div>
            </li>
            <li class="song_title_width"><a class="album_music">{$album}</a></li>
            <li class="text-center"><a class="time_music">{$time}</a></li>
            <li class="text-center favorite-text-center">
                <i class="{$heart_class} favorite-icon" style="{$heart_color}"></i>
            </li>
            <li class="text-center top_song_artist_playlist">
                </li>
        </ul>
        HTML;

        $number_music++;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title id="title_doc">Cybeat - Music Player</title>
</head>
<body class="index4_body_wrapper">
    <div class="album_list_wrapper album_list_wrapper_shop">
        <ul class="album_list_name ms_cover">
            <li>#</li>
            <li class="song_title_width">Title</li>
            </ul>
        
        <div id="music-list-container">
            <?php
                // Tampilkan halaman pertama saat load awal
                render_music_list($db, $api_key, 1);
            ?>
        </div>
    </div>
    <button id="load-more-btn" data-page="2">Load More</button>
    <script>
        // Logika untuk play music, load more, dll.
        document.addEventListener('DOMContentLoaded', function() {
            const musicListContainer = document.getElementById('music-list-container');

            // Event listener untuk memutar musik (delegasi event)
            musicListContainer.addEventListener('click', function(event) {
                const target = event.target;
                const playButton = target.closest('.play_hover');
                
                if (playButton) {
                    const musicItem = target.closest('.music-item');
                    const musicData = JSON.parse(musicItem.dataset.musicJson);
                    
                    // Panggil fungsi player Anda dengan data yang sudah bersih
                    // contoh: playMusic(musicData);
                    console.log("Playing:", musicData);
                }
            });

            // Logika untuk tombol "Load More"
            const loadMoreBtn = document.getElementById('load-more-btn');
            loadMoreBtn.addEventListener('click', function() {
                const currentPage = parseInt(this.dataset.page, 10);
                this.textContent = 'Loading...';
                this.disabled = true;

                fetch(`load_more.php?page=${currentPage}`)
                    .then(response => response.text())
                    .then(html => {
                        if (html.trim() !== '') {
                            musicListContainer.insertAdjacentHTML('beforeend', html);
                            this.dataset.page = currentPage + 1; // Siapkan untuk halaman berikutnya
                            this.textContent = 'Load More';
                            this.disabled = false;
                        } else {
                            this.textContent = 'End of List';
                            this.style.display = 'none'; // Sembunyikan tombol jika sudah tidak ada data
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more music:', error);
                        this.textContent = 'Error. Try again.';
                        this.disabled = false;
                    });
            });
        });
    </script>
</body>
</html>