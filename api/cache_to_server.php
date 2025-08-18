<?php

require_once __DIR__ . '/../database/mobile-music-player/api/connection.php';

function cacheMusicToServer($fileId, $accessToken, $musicId){
    global $db;
    // --- Konfigurasi Cache Lokal ---
    // Fungsi: Menentukan lokasi dan durasi penyimpanan file cache.
    $cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host'; // Nama folder untuk menyimpan cache
    // URL publik yang bisa diakses oleh klien untuk folder cache
    // **PENTING**: URL ini harus benar dan bisa diakses dari internet.
    $cacheUrl = 'https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/music-host';
    // Fungsi $cacheDuration adalah untuk mendownload ulang file dari GDRIVE-
    // jika sudah expired. Kita set ke 1 tahun, karena file lagu statis banget.
    $cacheDuration = 31536000; // Durasi cache dalam detik (86400 = 24 jam)

    $cacheFileUrl = $cacheUrl . '/' . basename($fileId);

    // --- Pastikan direktori cache ada dan bisa ditulisi ---
    // Fungsi: Membuat folder cache jika belum ada.
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            http_response_code(500);
            die("Failed to create cache directory.");
        }
    }
    
    // --- Tentukan path file cache ---
    // Fungsi: Membuat path file unik untuk setiap fileId di dalam folder cache.
    // basename() digunakan untuk keamanan, mencegah directory traversal.
    $cacheFilePath = $cacheDir . '/' . basename($fileId);

    // --- Buka file cache untuk ditulis ---
    // Fungsi: Membuka file di server lokal untuk menampung data dari Google Drive.
    $cacheFp = fopen($cacheFilePath, 'w');
    if (!$cacheFp) {
        http_response_code(500);
        log_message("Could not open cache file for writing: $cacheFilePath");
        die("Could not open cache file for writing.");
    }

    // --- Kunci file cache untuk mencegah penulisan ganda ---
    // Fungsi: Mencegah proses lain menulis ke file yang sama saat sedang diunduh.
    if (!flock($cacheFp, LOCK_EX)) {
        fclose($cacheFp);
        http_response_code(503);
        log_message("Could not get lock on cache file. Server is busy.");
        die("Could not get lock on cache file. Server is busy.");
    }

	// --- Unduh file dari Google Drive dan simpan ke cache ---
    $driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
    $ch = curl_init($driveUrl);
    
    $curlHeadersToGoogle = ["Authorization: Bearer " . $accessToken];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersToGoogle);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // --- Alihkan output cURL ke file cache, bukan ke browser ---
    // Fungsi: Opsi ini mengarahkan semua data yang diterima cURL untuk ditulis ke file handle ($cacheFp).
    curl_setopt($ch, CURLOPT_FILE, $cacheFp);
    
    curl_exec($ch);

    if (curl_errno($ch)) {
        log_message("cURL Error on downloading to cache: " . curl_error($ch));
        // --- Hapus file cache yang gagal/rusak ---
        // Fungsi: Membersihkan file yang tidak lengkap jika unduhan gagal.
        flock($cacheFp, LOCK_UN);
        fclose($cacheFp);
        unlink($cacheFilePath); // Hapus file yang gagal
        http_response_code(500);
        die("Failed to download file from Google Drive.");
    }
    
    curl_close($ch);

    // --- Lepas kunci dan tutup file handle cache ---
    // Fungsi: Menyelesaikan proses penulisan ke file cache.
    flock($cacheFp, LOCK_UN);
    fclose($cacheFp);

    // Masukkan ke sql bahwa file dengan ID ini telah di-cache.
    $stmt = $db->prepare("INSERT INTO cache_music (cache_music_id) VALUES (?)");
    $stmt->bind_param("i", $musicId);
    if (!$stmt->execute()) {
        die("Error inserting recents: " . $stmt->error);
    }
    $stmt->close();

    log_message("Caching process success for fileId: $fileId.");
}