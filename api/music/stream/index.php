<?php
// Izinkan semua origin atau sesuaikan dengan kebutuhan
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// PENTING: Tambahkan 'Range' di Allow-Headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Range");
// PENTING: Beritahu player bahwa server mendukung range request
header("Access-Control-Expose-Headers: Content-Range, Content-Length, Accept-Ranges");

// Tangani request OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../database/db.php';
require_once __DIR__ . '/../../stream_drive.php'; // utils dipanggil via stream_drive.php
require_once __DIR__ . '/../../get_hmac_token.php';

try {
    // $auth = new BearerAuth($secretKey);
    // $user = $auth->validate(false);
    // $userId = isset($user['sub']) ? $user['sub'] : 0;
    // $userRole = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    $musicId = isset($_GET['music_id']) ? $_GET['music_id'] : null;
    $fileType = isset($_GET['file_type']) ? $_GET['file_type'] : "audio";

    if ($fileType === "image") {
        $coverUrl = urlFormatter($_GET['cover_url']);
        if ($coverUrl['type'] == 'gdrive') {
            streamingMusicFromGdrive($db, "111", $coverUrl['url'], "image", $allApiData, $ffprobePath);
        } else if ($coverUrl['type'] == 'cdncloudflare') {
            $url = "https://cdn.sibeux.my.id/" . $coverUrl['url'];
            header("Location: " . $url, true, 302);
        } else {
            header("Location: " . $coverUrl['url'], true, 302);
        }
        die();
    }

    // $sql = "SELECT m.link_gdrive, a.is_private 
    $sql = "SELECT m.link_gdrive 
            FROM musics m
            -- JOIN album_musics am ON m.id_music = am.id_music
            -- JOIN albums a ON am.id_playlist = a.uid
            WHERE m.id_music = ? 
            LIMIT 1;";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $musicId);
    $stmt->execute();
    $result = $stmt->get_result();
    $music = $result->fetch_assoc();
    $stmt->close();

    // if (!$music) {
    //     http_response_code(404);
    //     echo json_encode([
    //         "status" => "error",
    //         "error" => "music_not_found",
    //         "message" => "Music not found",
    //     ]);
    //     die();
    // }

    // // Cek Akses
    // if ($music['is_private'] == 1 && $userRole === 'user') {
    //     http_response_code(403);
    //     echo json_encode([
    //         "status" => "error",
    //         "error" => "access_denied",
    //         "message" => "Akses ditolak: Konten Premium",
    //     ]);
    //     die();
    // }

    $musicUrl = urlFormatter($music['link_gdrive']);
    if ($musicUrl['type'] == 'gdrive') {
        streamingMusicFromGdrive($db, $musicId, $musicUrl['url'], $fileType, $allApiData, $ffprobePath);
    } else if ($musicUrl['type'] == 'cdncloudflare') {
        $path = str_replace("cdncloudflare", '', $musicUrl['url']);
        streamMusicFromCF($secretKey, $db, $ffprobePath, $path, $musicId);
    } else {
        header("Location: " . $musicUrl['url'], true, 302);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
}