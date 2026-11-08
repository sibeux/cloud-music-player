<?php

require_once __DIR__ . '/../SongDTO.php';

/**
 * Query handler untuk mengambil lagu berdasarkan category dengan fitur search dan sort.
 *
 * @param mysqli $db
 * @param string|int $categoryId
 * @param string $role
 * @param string $search
 * @param string $sortBy
 * @param string $sortOrder
 * @return SongDTO[]
 */
function getSongsByCategory($db, $categoryId, $role = 'free', $search = '', $sortBy = 'default', $sortOrder = 'asc')
{
    $joinCondition = "";
    $whereCategory = "1=1";
    $params = [];
    $types = "";

    // Kategori 6 adalah kategori "All" / tampilkan semua lagu
    if ((string) $categoryId !== "6") {
        $joinCondition = "
            JOIN category_albums ca ON ca.album_id = a.uid
            JOIN categories c ON c.category_id = ca.category_id
        ";
        $whereCategory = "c.category_id = ?";
        $params[] = (int) $categoryId;
        $types .= "i";
    }

    $privacyCondition = in_array($role, ['premium', 'admin'], true) ? '1=1' : 'a.is_private = 0';

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
            $orderBy = "m.title $direction, m.id_music ASC";
            break;
        case 'artist':
            $orderBy = "m.artist $direction, m.title ASC, m.id_music ASC";
            break;
        case 'album':
            $orderBy = "a.name $direction, m.disc_number ASC, m.id_music ASC";
            break;
        case 'disc_number':
        case 'disc':
            $orderBy = "m.disc_number $direction, m.id_music ASC";
            break;
        case 'date_added':
        case 'recently_added':
        case 'created_at':
            $orderBy = "m.id_music $direction";
            break;
        case 'default':
        default:
            $orderBy = "m.id_music ASC";
            break;
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
        $joinCondition
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc ON m.cover = dc.image_url
        WHERE $whereCategory AND $privacyCondition $searchClause
        ORDER BY $orderBy;";

    $stmt = $db->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $songs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_map(function ($song) {
        return new SongDTO($song);
    }, $songs);
}
