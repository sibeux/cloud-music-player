<?php
function getLibraryAlbums($db, int $userId, string $role): array
{
    if ($userId <= 0) return [];
    $privacy = in_array($role, ['premium', 'admin'], true) ? '1=1' : 'a.is_private = 0';
    $stmt = $db->prepare("SELECT a.uid AS id, a.name AS title, a.image AS cover,
        a.author, a.have_disc, dc.bg_color, l.created_at, p.created_at AS pin_at,
        (SELECT MAX(rm.played_at) FROM recent_musics rm
            WHERE rm.user_id = l.user_id AND rm.recentable_album_id = a.uid
                AND rm.recentable_album_type = 'album') AS played_at
        FROM cybeat_album_library l JOIN albums a ON a.uid = l.album_id
        LEFT JOIN dominant_colors dc ON dc.image_url = a.image
        LEFT JOIN album_pins p ON p.user_id = l.user_id
            AND p.pinnable_album_id = a.uid AND p.pinnable_album_type = 'album'
        WHERE l.user_id = ? AND $privacy
        ORDER BY p.created_at IS NULL,
            p.created_at ASC,
            CASE WHEN p.created_at IS NULL
                THEN GREATEST(COALESCE(played_at, '1000-01-01'), l.created_at)
            END DESC, a.uid DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($items as &$item) {
        $item['id'] = (int) $item['id'];
        $item['type'] = 'album';
        $item['cover'] = coverUrlFormatter($item['cover']);
    }
    return $items;
}
