<?php
/**
 * Menambahkan unique index pada tabel `category_albums`.
 * 
 * Constraint ini memastikan tidak ada duplikat data antara FK ID `categories` dan `albums`.
 *
 * @table category_albums
 * @constraint idx_unique_categoryalbum_id
 * 
 * ALTER TABLE category_albums
 * ADD UNIQUE INDEX idx_unique_categoryalbum_id (category_id, album_id);
 */

ob_start('ob_gzhandler'); // aktifkan gzip (opsional)
ini_set('memory_limit', '256M'); // atau '512M' kalau perlu

require_once __DIR__ . '/../../init.php';

try {

    $auth = new BearerAuth($secretKey);
    $user = $auth->validate();
    $userId = $user['sub'];
    $role = $user['data']['role'];

    require_once __DIR__ . '/get_album.php';
    require_once __DIR__ . '/get_category.php';
    require_once __DIR__ . '/get_playlist.php';

    // id, type, name, author/jumlah_lagu, cover, have_disc, played_at";

    $list_album = get_album($db, $userId, $role);
    $list_category = get_category($db, $userId);
    $list_playlist = get_playlist($db, $userId);

    $data = [
        'album' => $list_album,
        'category' => $list_category,
        'playlist' => $list_playlist
    ];

    // Header anti-cache
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode([
        "status" => "success",
        "data" => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
} finally {
    // Tutup koneksi setelah semua selesai
    if (isset($db)) {
        // Sesuaikan dengan driver: $db->close() untuk MySQLi, $db = null untuk PDO
        $db->close();
    }
}
exit;