<?php

global $ffprobePath;
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../../database/mobile-music-player/api/read_codec.php';
require_once __DIR__ . '/../image-dominant-color/get_color.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;

    if (isset($_POST['music_id']) && isset($_POST['album_id']) && isset($_POST['album_type'])) {
        $codec = null;
        $dominant_color = null;
        $music_id = $_POST['music_id'];
        $albumId = $_POST['album_id'];
        // toLower
        $albumType = strtolower($_POST['album_type']);

        // Eksekusi query untuk 'recents_music'
        if ($userId != 0) {
            $stmt_recents = $db->prepare("INSERT INTO recent_musics (uid_music, user_id, recentable_album_id, recentable_album_type) VALUES (?, ?, ?, ?)");
            $stmt_recents->bind_param("iiis", $music_id, $userId, $albumId, $albumType);
            if (!$stmt_recents->execute()) {
                die("Error inserting recents: " . $stmt_recents->error);
            }
            $stmt_recents->close();
        }

        // Ambil URL music dan cover dari database (menghindari SSRF)
        $stmt_music = $db->prepare("SELECT link_gdrive, cover FROM musics WHERE id_music = ?");
        $stmt_music->bind_param("i", $music_id);
        $stmt_music->execute();
        $result_music = $stmt_music->get_result();
        
        if ($row = $result_music->fetch_assoc()) {
            // Gunakan helper functions untuk mengubah raw data menjadi URL stream yang valid
            // agar ffprobe dan ColorThief bisa memprosesnya
            
            $music_url = resolveMusicStreamUrl($music_id, $row['link_gdrive'], $secretKey);

            $image_url = coverUrlFormatter($row['cover']);
            
            // Cek apakah codec sudah ada di database
            $stmt_codec = $db->prepare("SELECT 1 FROM metadata_musics WHERE metadata_id_music = ?");
            $stmt_codec->bind_param("i", $music_id);
            $stmt_codec->execute();
            $stmt_codec->store_result();
            $codec_exist = $stmt_codec->num_rows > 0;
            $stmt_codec->close();

            // Eksekusi query untuk 'metadata_music' jika belum ada
            if (!$codec_exist && !empty($music_url)) {
                $codec = checkCodecAudio($music_id, $music_url, $db, $ffprobePath);
            }

            // Normalisasi URL image untuk cek dominant color
            $originalImageUrl = $image_url;
            if (strpos($image_url, '555/cybeat/false/image') !== false) {
                if (preg_match("#/stream/([^/]+)/#", $image_url, $matches)) {
                    $originalImageUrl = "https://drive.google.com/file/d/" . $matches[1] . "/view?usp=drive_link";
                }
            } else if (strpos($image_url, 'cdn.sibeux.my.id') !== false) {
                $rawUrl = parse_url($image_url, PHP_URL_PATH);
                $originalImageUrl = "cdncloudflare" . $rawUrl;
            } else if (strpos($image_url, 'cover_url') !== false) {
                parse_str(parse_url($image_url, PHP_URL_QUERY), $query);
                $originalImageUrl = isset($query['cover_url']) ? $query['cover_url'] : $image_url;
            }

            // Cek apakah dominant color sudah ada di database
            $stmt_color = $db->prepare("SELECT 1 FROM dominant_colors WHERE image_url = ?");
            $stmt_color->bind_param("s", $originalImageUrl);
            $stmt_color->execute();
            $stmt_color->store_result();
            $color_exist = $stmt_color->num_rows > 0;
            $stmt_color->close();

            // Dapatkan dominant color dari cover jika belum ada
            if (!$color_exist && !empty($image_url)) {
                $dominant_color = getDominantColors($image_url, $db);
            }
        }
        $stmt_music->close();

        // Execution query for 'delete'
        if ($userId != 0 && mt_rand(1, 100) === 1) {
            $delete_sql = "DELETE FROM recent_musics
                        WHERE user_id = ?
                        AND uid_recents NOT IN (
                            SELECT uid_recents FROM (
                                SELECT uid_recents FROM recent_musics
                                WHERE user_id = ?
                                ORDER BY played_at DESC
                                LIMIT 500
                            ) AS last_500
                        )";
            $stmt_delete = $db->prepare($delete_sql);
            $stmt_delete->bind_param("ii", $userId, $userId);
            if (!$stmt_delete->execute()) {
                die("Error deleting old recents: " . $stmt_delete->error);
            }
            $stmt_delete->close();
        }

        // echo json response
        sendJsonResponse([
            "status" => "success",
            "message" => "Recent music successfully added.",
            "metadata" => "Metadata success processed dan saved.",
            "codec" => $codec ?? null,
            "dominant_color" => $dominant_color ?? null,
        ]);
    } else {
        // Add response if no ada POST data
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Error: music_id is not set."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
exit;