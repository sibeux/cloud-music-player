<?php

require_once __DIR__ . '/get_four_cover.php';

const ALLOWED_EXPLORE_CATEGORIES = [
    1 => 'IndoPride',
    2 => '日本の歌',
    3 => 'Jowo Mletre',
    4 => 'Worldwide',
    5 => 'Instrumental',
];

/**
 * Mengambil informasi detail kategori (id, nama, cover).
 *
 * @param mysqli $db
 * @param int $categoryId
 * @param string $role
 * @return array|null
 */
function getCategoryExploreInfo($db, int $categoryId, string $role = 'free'): ?array
{
    if (!array_key_exists($categoryId, ALLOWED_EXPLORE_CATEGORIES)) {
        return null;
    }

    $defaultName = ALLOWED_EXPLORE_CATEGORIES[$categoryId];

    $stmt = $db->prepare("SELECT category_id, name, cover FROM categories WHERE category_id = ? LIMIT 1");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $name = $row['name'] ?? $defaultName;
    $cover = $row['cover'] ?? null;

    if (empty($cover)) {
        $cover = getFourCoverCategory($db, $categoryId, $role);
    } else {
        $cover = coverUrlFormatter($cover);
    }

    return [
        'id' => $categoryId,
        'name' => $name,
        'cover' => $cover,
    ];
}

/**
 * Mengambil album terbaru berdasarkan kategori dengan keyset pagination (kursor a.uid DESC).
 *
 * @param mysqli $db
 * @param int $categoryId
 * @param string $role
 * @param int|null $cursor (uid album terakhir yang sudah diterima)
 * @param int $limit
 * @return array
 */
function getExploreAlbumsByCategory($db, int $categoryId, string $role = 'free', ?int $cursor = null, int $limit = 10, string $search = '', ?string $sortBy = null, string $sortDirection = 'desc'): array
{
    $privacyCondition = in_array($role, ['premium', 'admin'], true) ? '1=1' : 'a.is_private = 0';
    $search = trim($search);
    $pattern = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $fetchLimit = $limit + 1; // Ambil +1 untuk deteksi has_more secara akurat tanpa COUNT(*)

    if ($sortBy !== null) {
        $columns = ['default' => 'a.uid', 'alphabetical' => 'a.name', 'created' => 'created_at'];
        $column = $columns[$sortBy] ?? 'a.uid';
        $direction = $sortDirection === 'asc' ? 'ASC' : 'DESC';
        $orderBy = "$column $direction, a.uid $direction";
        $offset = max(0, $cursor ?? 0);
        $query = "SELECT
            a.uid,
            a.name,
            a.image,
            a.author,
            a.have_disc,
            (SELECT MIN(dc.bg_color) FROM dominant_colors dc WHERE dc.image_url = a.image) AS bg_color,
            (
                SELECT MIN(am.date_created)
                FROM album_musics am
                WHERE am.id_playlist = a.uid
            ) AS created_at
        FROM albums a
        WHERE EXISTS (
            SELECT 1 FROM category_albums ca
            WHERE ca.album_id = a.uid AND ca.category_id = ?
        )
            AND $privacyCondition
            AND (? = '' OR a.name LIKE ? ESCAPE '!' OR a.author LIKE ? ESCAPE '!')
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isssii", $categoryId, $search, $pattern, $pattern, $fetchLimit, $offset);
    } else if ($cursor !== null && $cursor > 0) {
        $query = "SELECT
            a.uid,
            a.name,
            a.image,
            a.author,
            a.have_disc,
            (SELECT MIN(dc.bg_color) FROM dominant_colors dc WHERE dc.image_url = a.image) AS bg_color,
            (
                SELECT MIN(am.date_created)
                FROM album_musics am
                WHERE am.id_playlist = a.uid
            ) AS created_at
        FROM albums a
        WHERE EXISTS (
            SELECT 1 FROM category_albums ca
            WHERE ca.album_id = a.uid AND ca.category_id = ?
        )
            AND $privacyCondition
            AND (? = '' OR a.name LIKE ? ESCAPE '!' OR a.author LIKE ? ESCAPE '!')
            AND a.uid < ?
        ORDER BY a.uid DESC
        LIMIT ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isssii", $categoryId, $search, $pattern, $pattern, $cursor, $fetchLimit);
    } else {
        $query = "SELECT
            a.uid,
            a.name,
            a.image,
            a.author,
            a.have_disc,
            (SELECT MIN(dc.bg_color) FROM dominant_colors dc WHERE dc.image_url = a.image) AS bg_color,
            (
                SELECT MIN(am.date_created)
                FROM album_musics am
                WHERE am.id_playlist = a.uid
            ) AS created_at
        FROM albums a
        WHERE EXISTS (
            SELECT 1 FROM category_albums ca
            WHERE ca.album_id = a.uid AND ca.category_id = ?
        )
            AND $privacyCondition
            AND (? = '' OR a.name LIKE ? ESCAPE '!' OR a.author LIKE ? ESCAPE '!')
        ORDER BY a.uid DESC
        LIMIT ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isssi", $categoryId, $search, $pattern, $pattern, $fetchLimit);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $albums = [];
    while ($row = $result->fetch_assoc()) {
        $albums[] = [
            'id' => (int) $row['uid'],
            'type' => 'album',
            'title' => $row['name'],
            'cover' => coverUrlFormatter($row['image']),
            'bg_color' => $row['bg_color'],
            'author' => $row['author'],
            'have_disc' => (int) $row['have_disc'],
            'created_at' => $row['created_at'],
        ];
    }
    $stmt->close();

    $hasMore = count($albums) > $limit;
    if ($hasMore) {
        $albums = array_slice($albums, 0, $limit);
    }

    $nextCursor = null;
    if (!empty($albums) && $hasMore) {
        $lastItem = end($albums);
        $nextCursor = $sortBy !== null ? max(0, $cursor ?? 0) + $limit : $lastItem['id'];
    }

    return [
        'albums' => $albums,
        'next_cursor' => $nextCursor,
        'has_more' => $hasMore,
    ];
}

/**
 * Mengambil section untuk seluruh 5 kategori yang diperbolehkan.
 *
 * @param mysqli $db
 * @param string $role
 * @param int $limitPerCategory
 * @return array
 */
function getAllExploreCategories($db, string $role = 'free', int $limitPerCategory = 10): array
{
    $sections = [];

    foreach (ALLOWED_EXPLORE_CATEGORIES as $categoryId => $categoryName) {
        $categoryInfo = getCategoryExploreInfo($db, $categoryId, $role);
        $exploreData = getExploreAlbumsByCategory($db, $categoryId, $role, null, $limitPerCategory);

        $sections[] = [
            'category' => $categoryInfo,
            'albums' => $exploreData['albums'],
            'next_cursor' => $exploreData['next_cursor'],
            'has_more' => $exploreData['has_more'],
        ];
    }

    return $sections;
}
