<?php

require_once __DIR__ . '/../SongDTO.php';

/**
 * Query handler untuk mengambil lagu riwayat pemutaran (recent musics) dengan fitur search dan sort.
 *
 * @param mysqli $db
 * @param int $userId
 * @param string $search
 * @param string $sortBy
 * @param string $sortOrder
 * @return SongDTO[]
 */
function getSongsByRecent($db, $userId, $search = '', $sortBy = 'default', $sortOrder = 'desc')
{
    $userId = (int) $userId;
    if ($userId <= 0) {
        return [];
    }

    $params = [$userId];
    $types = "i";

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

    $direction = ($normalizedOrder === 'asc') ? 'ASC' : 'DESC';

    switch ($normalizedSort) {
        case 'alphabetical':
        case 'title':
        case 'name':
            $orderBy = "m.title $direction, rm.played_at DESC";
            break;
        case 'artist':
            $orderBy = "m.artist $direction, m.title ASC";
            break;
        case 'album':
            $orderBy = "a.name $direction, m.title ASC";
            break;
        case 'date_added':
        case 'recently_added':
        case 'default':
        default:
            $orderBy = "rm.played_at $direction, rm.uid_recents $direction";
            break;
    }

    $query = "SELECT
        m.id_music, m.title, m.artist, m.cover, m.disc_number,
        a.name as album,
        mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        cache_musics.cache_music_id,
        rm.played_at
        FROM recent_musics rm
        JOIN musics m ON m.id_music = rm.uid_music
        LEFT JOIN album_musics am ON am.id_music = m.id_music
        LEFT JOIN albums a ON am.id_playlist = a.uid
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc ON m.cover = dc.image_url
        WHERE rm.user_id = ?
        $searchClause
        ORDER BY $orderBy";

    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $songs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_map(function ($song) {
        return new SongDTO($song);
    }, $songs);
}
