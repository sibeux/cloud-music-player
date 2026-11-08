<?php

require_once __DIR__ . '/../SongDTO.php';

/**
 * Query handler untuk mengambil lagu berdasarkan album standar.
 *
 * @param mysqli $db
 * @param int $albumId
 * @param string $role
 * @return SongDTO[]
 */
function getSongsByAlbum($db, $albumId, $role = 'free')
{
    // Cek status privasi album
    $stmt = $db->prepare("SELECT is_private FROM albums WHERE uid = ?");
    $stmt->bind_param("i", $albumId);
    $stmt->execute();
    $result = $stmt->get_result();
    $album = $result->fetch_assoc();
    $stmt->close();

    if (!$album) {
        sendSongJsonError(404, 'not_found', 'Album tidak ditemukan');
    }

    if ($album['is_private'] == 1 && !in_array($role, ['premium', 'admin'], true)) {
        sendSongJsonError(403, 'no_access', 'Anda tidak memiliki akses untuk melihat album ini');
    }

    $query = "SELECT
            m.id_music, m.title, m.artist, m.cover, m.disc_number,
            a.name as album,
            mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
            dc.bg_color, dc.text_color,
            cache_musics.cache_music_id
        FROM musics m
        JOIN album_musics am ON am.id_music = m.id_music
        JOIN albums a ON am.id_playlist = a.uid
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc ON m.cover = dc.image_url
        WHERE a.uid = ?
        ORDER BY m.id_music ASC;";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param("i", $albumId);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtSong->close();

    return array_map(function ($song) {
        return new SongDTO($song);
    }, $songs);
}
