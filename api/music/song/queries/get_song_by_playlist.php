<?php

require_once __DIR__ . '/../SongDTO.php';

/**
 * Query handler untuk mengambil lagu berdasarkan playlist dengan fitur search dan sort.
 *
 * @param mysqli $db
 * @param int $playlistId
 * @param int $userId
 * @param string $search
 * @param string $sortBy
 * @param string $sortOrder
 * @return SongDTO[]
 */
function getSongsByPlaylist($db, $playlistId, $userId, $search = '', $sortBy = 'default', $sortOrder = 'asc')
{
    // Verifikasi kepemilikan playlist
    $checkStmt = $db->prepare("SELECT playlist_id FROM playlists WHERE playlist_id = ? AND user_id = ? LIMIT 1");
    $checkStmt->bind_param("ii", $playlistId, $userId);
    $checkStmt->execute();
    $playlist = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$playlist) {
        sendSongJsonError(404, 'playlist_not_found', 'Playlist tidak ditemukan atau bukan milik Anda.');
    }

    $params = [(int) $playlistId, (int) $userId];
    $types = "ii";

    // Filter Search
    $searchClause = "";
    $trimmedSearch = trim($search);
    if ($trimmedSearch !== '') {
        $searchClause = "AND (m.title LIKE ? OR m.artist LIKE ? OR a.name LIKE ?)";
        $searchParam = '%' . $trimmedSearch . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }

    // Resolusi Sort
    $normalizedSort = strtolower(trim($sortBy));
    $normalizedOrder = strtolower(trim($sortOrder));

    if ($normalizedSort === 'newest') {
        $normalizedSort = 'date_added';
        $normalizedOrder = 'desc';
    } elseif ($normalizedSort === 'oldest') {
        $normalizedSort = 'date_added';
        $normalizedOrder = 'asc';
    } elseif (str_ends_with($normalizedSort, '_desc')) {
        $normalizedSort = substr($normalizedSort, 0, -5);
        $normalizedOrder = 'desc';
    } elseif (str_ends_with($normalizedSort, '_asc')) {
        $normalizedSort = substr($normalizedSort, 0, -4);
        $normalizedOrder = 'asc';
    }

    $direction = ($normalizedOrder === 'desc') ? 'DESC' : 'ASC';

    switch ($normalizedSort) {
        case 'alphabetical':
        case 'title':
        case 'name':
            $orderBy = "m.title $direction, pm.created_at ASC";
            break;
        case 'artist':
            $orderBy = "m.artist $direction, m.title ASC";
            break;
        case 'album':
            $orderBy = "a.name $direction, m.title ASC";
            break;
        case 'date_added':
        case 'recently_added':
        case 'created_at':
            $orderBy = "pm.created_at $direction, pm.id_playlist_music $direction";
            break;
        case 'default':
        default:
            $orderBy = "pm.created_at ASC, pm.id_playlist_music ASC";
            break;
    }

    $query = "SELECT
        m.id_music, m.title, m.artist, m.cover, m.disc_number,
        a.name as album,
        mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        cache_musics.cache_music_id,
        pm.id_playlist_music
        FROM musics m
        JOIN album_musics am ON am.id_music = m.id_music
        JOIN albums a ON am.id_playlist = a.uid
        JOIN playlist_musics pm ON pm.id_music = m.id_music
        JOIN playlists p ON p.playlist_id = pm.id_playlist
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc ON m.cover = dc.image_url
        WHERE pm.id_playlist = ? AND p.user_id = ? $searchClause
        ORDER BY $orderBy;";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param($types, ...$params);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtSong->close();

    return hydrateSongsWithArtists($db, $songs);
}
