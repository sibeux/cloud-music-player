<?php

class ArtistMergeException extends RuntimeException
{
    private $httpStatus;
    private $errorCode;

    public function __construct($httpStatus, $errorCode, $message)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}

function mergeArtists($db, $sourceArtistId, $targetArtistId, $confirmedSourceName)
{
    if ($sourceArtistId < 1 || $targetArtistId < 1 || $sourceArtistId === $targetArtistId) {
        throw new ArtistMergeException(422, 'invalid_artist_selection', 'Pilih dua Artist yang berbeda.');
    }

    if (!is_string($confirmedSourceName) || trim($confirmedSourceName) === '') {
        throw new ArtistMergeException(422, 'source_name_confirmation_required', 'Ketik nama Artist sumber untuk mengonfirmasi merge.');
    }

    ensureArtistMergeTablesAreTransactional($db);

    $db->begin_transaction();

    try {
        // Lock artist rows in a stable order so concurrent merges cannot target stale rows.
        $artistsStmt = $db->prepare(
            'SELECT id_artist, name FROM cybeat_artists WHERE id_artist IN (?, ?) ORDER BY id_artist ASC FOR UPDATE'
        );
        $artistsStmt->bind_param('ii', $sourceArtistId, $targetArtistId);
        $artistsStmt->execute();
        $artistRows = $artistsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $artistsStmt->close();

        $artists = [];
        foreach ($artistRows as $artist) {
            $artists[(int) $artist['id_artist']] = $artist;
        }

        if (!isset($artists[$sourceArtistId]) || !isset($artists[$targetArtistId])) {
            throw new ArtistMergeException(404, 'artist_not_found', 'Salah satu Artist tidak ditemukan. Muat ulang daftar Artist.');
        }

        $source = $artists[$sourceArtistId];
        $target = $artists[$targetArtistId];
        if ($source['name'] !== $confirmedSourceName) {
            throw new ArtistMergeException(409, 'source_name_changed', 'Nama Artist sumber berubah. Periksa ulang sebelum merge.');
        }

        // Lock both link sets, then count overlaps before moving links.
        $linksStmt = $db->prepare(
            'SELECT id_music, id_artist
             FROM cybeat_music_artists
             WHERE id_artist IN (?, ?)
             ORDER BY id_music ASC, id_artist ASC
             FOR UPDATE'
        );
        $linksStmt->bind_param('ii', $sourceArtistId, $targetArtistId);
        $linksStmt->execute();
        $links = $linksStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $linksStmt->close();

        $sourceMusicIds = [];
        $targetMusicIds = [];
        foreach ($links as $link) {
            if ((int) $link['id_artist'] === $sourceArtistId) {
                $sourceMusicIds[(int) $link['id_music']] = true;
            } elseif ((int) $link['id_artist'] === $targetArtistId) {
                $targetMusicIds[(int) $link['id_music']] = true;
            }
        }

        $sourceLinkCount = count($sourceMusicIds);
        $targetLinkCount = count($targetMusicIds);
        $sharedLinkCount = count(array_intersect_key($sourceMusicIds, $targetMusicIds));

        $moveStmt = $db->prepare(
            'INSERT IGNORE INTO cybeat_music_artists (id_music, id_artist)
             SELECT id_music, ? FROM cybeat_music_artists WHERE id_artist = ?'
        );
        $moveStmt->bind_param('ii', $targetArtistId, $sourceArtistId);
        $moveStmt->execute();
        $movedLinkCount = $moveStmt->affected_rows;
        $moveStmt->close();

        $deleteLinksStmt = $db->prepare('DELETE FROM cybeat_music_artists WHERE id_artist = ?');
        $deleteLinksStmt->bind_param('i', $sourceArtistId);
        $deleteLinksStmt->execute();
        $deleteLinksStmt->close();

        $deleteArtistStmt = $db->prepare('DELETE FROM cybeat_artists WHERE id_artist = ?');
        $deleteArtistStmt->bind_param('i', $sourceArtistId);
        $deleteArtistStmt->execute();
        if ($deleteArtistStmt->affected_rows !== 1) {
            $deleteArtistStmt->close();
            throw new RuntimeException('Source Artist was not deleted.');
        }
        $deleteArtistStmt->close();

        $db->commit();

        return [
            'removed_artist' => [
                'id_artist' => $sourceArtistId,
                'name' => $source['name'],
            ],
            'kept_artist' => [
                'id_artist' => $targetArtistId,
                'name' => $target['name'],
            ],
            'source_song_count' => $sourceLinkCount,
            'target_song_count_before' => $targetLinkCount,
            'shared_song_count' => $sharedLinkCount,
            'moved_link_count' => $movedLinkCount,
            'target_song_count_after' => $sourceLinkCount + $targetLinkCount - $sharedLinkCount,
        ];
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}

function ensureArtistMergeTablesAreTransactional($db)
{
    $result = $db->query(
        "SELECT TABLE_NAME, ENGINE
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME IN ('cybeat_artists', 'cybeat_music_artists')"
    );
    $engines = [];
    while ($row = $result->fetch_assoc()) {
        $engines[$row['TABLE_NAME']] = strtoupper((string) $row['ENGINE']);
    }
    $result->free();

    if (($engines['cybeat_artists'] ?? null) !== 'INNODB' || ($engines['cybeat_music_artists'] ?? null) !== 'INNODB') {
        throw new ArtistMergeException(
            409,
            'artist_merge_requires_innodb',
            'Merge belum dapat dilakukan karena tabel Artist belum mendukung transaksi yang aman.'
        );
    }
}
