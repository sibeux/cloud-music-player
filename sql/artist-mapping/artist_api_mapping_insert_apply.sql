-- Safely insert the reviewed artist-to-song links from the staging CSV.
-- Preconditions: import the full 799-row CSV after running the prepare script.
-- This script blocks all writes unless the stage count, catalog IDs, engines,
-- source-credit song counts, and source/artist pairs validate.
-- Strip boundary CR/LF from musics.artist before exact binary comparison.

SELECT DATABASE() AS active_database;

START TRANSACTION;

SET @artist_mapping_stage_rows = (
    SELECT COUNT(*) FROM artist_api_mapping_insert_stage
);

SET @artist_mapping_source_credits = (
    SELECT COUNT(DISTINCT BINARY source_credit)
    FROM artist_api_mapping_insert_stage
);

SET @artist_mapping_expected_links = (
    SELECT COUNT(*)
    FROM artist_api_mapping_insert_stage AS s
    INNER JOIN musics AS m
        ON BINARY TRIM(
            TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
        ) = BINARY s.source_credit
);

SET @artist_mapping_stage_expected_links = (
    SELECT COALESCE(SUM(song_count), 0)
    FROM artist_api_mapping_insert_stage
);

SET @artist_mapping_validation_errors =
    IF(@artist_mapping_stage_rows = 799, 0, 1)
    + IF(@artist_mapping_source_credits = 505, 0, 1)
    + IF(@artist_mapping_expected_links = @artist_mapping_stage_expected_links, 0, 1)
    + (
        SELECT COUNT(*)
        FROM (
            SELECT BINARY source_credit AS source_key, id_artist
            FROM artist_api_mapping_insert_stage
            GROUP BY BINARY source_credit, id_artist
            HAVING COUNT(*) > 1
        ) AS duplicate_pairs
    )
    + (
        SELECT COUNT(*)
        FROM artist_api_mapping_insert_stage AS s
        LEFT JOIN cybeat_artists AS a ON a.id_artist = s.id_artist
        WHERE a.id_artist IS NULL OR s.id_artist < 1
    )
    + (
        SELECT COUNT(*)
        FROM (
            SELECT
                BINARY s.source_credit AS source_key,
                MIN(s.song_count) AS min_song_count,
                MAX(s.song_count) AS max_song_count,
                COUNT(DISTINCT m.id_music) AS database_song_count
            FROM artist_api_mapping_insert_stage AS s
            LEFT JOIN musics AS m
                ON BINARY TRIM(
                    TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
                ) = BINARY s.source_credit
            GROUP BY BINARY s.source_credit
            HAVING min_song_count <> max_song_count
                OR database_song_count <> max_song_count
        ) AS source_count_mismatches
    )
    + IF(
        (
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME IN (
                  'artist_api_mapping_insert_stage',
                  'cybeat_artists',
                  'cybeat_music_artists'
              )
              AND UPPER(ENGINE) = 'INNODB'
        ) = 3,
        0,
        1
    );

INSERT IGNORE INTO cybeat_music_artists (id_music, id_artist)
SELECT m.id_music, s.id_artist
FROM artist_api_mapping_insert_stage AS s
INNER JOIN musics AS m
    ON BINARY TRIM(
        TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
    ) = BINARY s.source_credit
WHERE @artist_mapping_validation_errors = 0;

SET @artist_mapping_inserted_links = ROW_COUNT();

COMMIT;

SELECT
    IF(@artist_mapping_validation_errors = 0, 'applied', 'blocked') AS result,
    @artist_mapping_validation_errors AS validation_errors,
    @artist_mapping_stage_rows AS staged_rows,
    @artist_mapping_source_credits AS staged_source_credits,
    @artist_mapping_expected_links AS expected_song_artist_links,
    @artist_mapping_stage_expected_links AS csv_song_artist_links,
    @artist_mapping_inserted_links AS newly_inserted_links;

SELECT COUNT(*) AS candidate_links_still_missing
FROM artist_api_mapping_insert_stage AS s
INNER JOIN musics AS m
    ON BINARY TRIM(
        TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
    ) = BINARY s.source_credit
LEFT JOIN cybeat_music_artists AS ma
    ON ma.id_music = m.id_music
    AND ma.id_artist = s.id_artist
WHERE ma.id_music IS NULL;

-- INSERT IGNORE makes reruns safe for existing (id_music, id_artist) pairs.
-- A result of 'blocked' means no links were inserted; inspect stage/source data.
