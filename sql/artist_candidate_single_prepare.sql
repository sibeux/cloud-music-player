-- Candidate-single Artist migration preparation.
-- Run only against the database that contains musics, albums, and album_musics.
-- This file creates empty tables and performs read-only validation queries.
-- It does not insert artists or music_artist links.

SELECT DATABASE() AS active_database, VERSION() AS mysql_version;

CREATE TABLE IF NOT EXISTS cybeat_artists (
    id_artist INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (id_artist),
    UNIQUE KEY uq_cybeat_artists_name (name)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS cybeat_music_artists (
    id_music INT NOT NULL,
    id_artist INT NOT NULL,
    PRIMARY KEY (id_music, id_artist),
    KEY idx_cybeat_music_artists_artist_music (id_artist, id_music)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_bin;

-- Import artist_single_mapping_batch.csv into this empty table via phpMyAdmin.
-- The CSV headers match these columns and the batch currently has 1,348 rows.
CREATE TABLE IF NOT EXISTS artist_mapping_stage (
    source_credit VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    artist_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    song_count INT UNSIGNED NOT NULL,
    mapping_status VARCHAR(32) NOT NULL,
    PRIMARY KEY (source_credit)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_bin;

-- Expected stage summary after CSV import: 1,348 rows and 17,459 songs.
SELECT
    COUNT(*) AS staged_credits,
    SUM(song_count) AS staged_song_count,
    SUM(mapping_status = 'candidate_for_insert') AS candidate_credits
FROM artist_mapping_stage;

-- This must return zero rows before any Artist IDs are assigned.
-- A result means the live musics.artist values or song counts differ from the CSV.
SELECT
    s.source_credit,
    s.song_count AS csv_song_count,
    COUNT(m.id_music) AS database_song_count
FROM artist_mapping_stage AS s
LEFT JOIN musics AS m
    ON BINARY TRIM(m.artist) = BINARY s.source_credit
WHERE s.mapping_status = 'candidate_for_insert'
GROUP BY s.source_credit, s.song_count
HAVING COUNT(m.id_music) <> s.song_count
ORDER BY s.source_credit;

-- No foreign keys are added here because the storage engines of the existing
-- tables have not been confirmed. Source credits in musics.artist remain intact.
