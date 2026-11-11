-- Apply the user-reviewed artist mappings from the manual staging CSV.
-- Expected stage: 49 mappings, 46 exact source credits, 12 target artists,
-- totaling 1,914 artist-song links.
-- Existing API entries LiSA and R . O . N must resolve uniquely.
-- Other approved artist names are created only if no case-insensitive match exists.

SELECT DATABASE() AS active_database;

START TRANSACTION;

SET @manual_artist_stage_rows = (
    SELECT COUNT(*) FROM artist_unmapped_manual_mapping_stage
);

SET @manual_artist_source_credits = (
    SELECT COUNT(DISTINCT BINARY source_credit)
    FROM artist_unmapped_manual_mapping_stage
);

SET @manual_artist_targets = (
    SELECT COUNT(DISTINCT BINARY artist_name)
    FROM artist_unmapped_manual_mapping_stage
);

SET @manual_artist_expected_links = (
    SELECT COUNT(*)
    FROM artist_unmapped_manual_mapping_stage AS s
    INNER JOIN musics AS m
        ON BINARY TRIM(
            TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
        ) = BINARY s.source_credit
);

SET @manual_artist_stage_expected_links = (
    SELECT COALESCE(SUM(song_count), 0)
    FROM artist_unmapped_manual_mapping_stage
);

SET @manual_artist_validation_errors =
    IF(@manual_artist_stage_rows = 49, 0, 1)
    + IF(@manual_artist_source_credits = 46, 0, 1)
    + IF(@manual_artist_targets = 12, 0, 1)
    + IF(
        @manual_artist_expected_links = @manual_artist_stage_expected_links,
        0,
        1
    )
    + (
        SELECT COUNT(*)
        FROM (
            SELECT BINARY source_credit AS source_key, BINARY artist_name AS artist_key
            FROM artist_unmapped_manual_mapping_stage
            GROUP BY BINARY source_credit, BINARY artist_name
            HAVING COUNT(*) > 1
        ) AS duplicate_mappings
    )
    + (
        SELECT COUNT(*)
        FROM (
            SELECT
                BINARY s.source_credit AS source_key,
                MIN(s.song_count) AS min_song_count,
                MAX(s.song_count) AS max_song_count,
                COUNT(DISTINCT m.id_music) AS database_song_count
            FROM artist_unmapped_manual_mapping_stage AS s
            LEFT JOIN musics AS m
                ON BINARY TRIM(
                    TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
                ) = BINARY s.source_credit
            GROUP BY BINARY s.source_credit
            HAVING min_song_count <> max_song_count
                OR database_song_count <> max_song_count
        ) AS source_count_mismatches
    )
    + (
        SELECT COUNT(*)
        FROM (
            SELECT s.artist_name, COUNT(a.id_artist) AS matching_artists
            FROM (
                SELECT DISTINCT artist_name
                FROM artist_unmapped_manual_mapping_stage
            ) AS s
            LEFT JOIN cybeat_artists AS a
                ON (
                    BINARY s.artist_name = BINARY 'R . O . N'
                    AND LOWER(REPLACE(TRIM(a.name), ' ', '')) = 'r.o.n'
                )
                OR (
                    BINARY s.artist_name <> BINARY 'R . O . N'
                    AND LOWER(TRIM(a.name)) = LOWER(s.artist_name)
                )
            GROUP BY BINARY s.artist_name
            HAVING (
                s.artist_name IN ('LiSA', 'R . O . N')
                AND matching_artists <> 1
            ) OR (
                s.artist_name NOT IN ('LiSA', 'R . O . N')
                AND matching_artists > 1
            )
        ) AS artist_name_ambiguities
    )
    + IF(
        (
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME IN (
                  'artist_unmapped_manual_mapping_stage',
                  'cybeat_artists',
                  'cybeat_music_artists'
              )
              AND UPPER(ENGINE) = 'INNODB'
        ) = 3,
        0,
        1
    );

INSERT IGNORE INTO cybeat_artists (name)
SELECT DISTINCT s.artist_name
FROM artist_unmapped_manual_mapping_stage AS s
WHERE @manual_artist_validation_errors = 0
  AND BINARY s.artist_name NOT IN (BINARY 'LiSA', BINARY 'R . O . N')
  AND NOT EXISTS (
      SELECT 1
      FROM cybeat_artists AS existing_artist
      WHERE LOWER(TRIM(existing_artist.name)) = LOWER(s.artist_name)
  );

INSERT IGNORE INTO cybeat_music_artists (id_music, id_artist)
SELECT m.id_music, a.id_artist
FROM artist_unmapped_manual_mapping_stage AS s
INNER JOIN musics AS m
    ON BINARY TRIM(
        TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
    ) = BINARY s.source_credit
INNER JOIN cybeat_artists AS a
    ON (
        BINARY s.artist_name = BINARY 'R . O . N'
        AND LOWER(REPLACE(TRIM(a.name), ' ', '')) = 'r.o.n'
    )
    OR (
        BINARY s.artist_name <> BINARY 'R . O . N'
        AND LOWER(TRIM(a.name)) = LOWER(s.artist_name)
    )
WHERE @manual_artist_validation_errors = 0;

COMMIT;

SELECT
    IF(@manual_artist_validation_errors = 0, 'applied', 'blocked') AS result,
    @manual_artist_validation_errors AS validation_errors,
    @manual_artist_stage_rows AS staged_mappings,
    @manual_artist_source_credits AS staged_source_credits,
    @manual_artist_targets AS target_artists,
    @manual_artist_expected_links AS expected_artist_song_links,
    @manual_artist_stage_expected_links AS csv_artist_song_links;

SELECT COUNT(*) AS candidate_artist_links_still_missing
FROM artist_unmapped_manual_mapping_stage AS s
INNER JOIN musics AS m
    ON BINARY TRIM(
        TRIM(BOTH CHAR(13) FROM TRIM(BOTH CHAR(10) FROM m.artist))
    ) = BINARY s.source_credit
INNER JOIN cybeat_artists AS a
    ON (
        BINARY s.artist_name = BINARY 'R . O . N'
        AND LOWER(REPLACE(TRIM(a.name), ' ', '')) = 'r.o.n'
    )
    OR (
        BINARY s.artist_name <> BINARY 'R . O . N'
        AND LOWER(TRIM(a.name)) = LOWER(s.artist_name)
    )
LEFT JOIN cybeat_music_artists AS ma
    ON ma.id_music = m.id_music
    AND ma.id_artist = a.id_artist
WHERE ma.id_music IS NULL;

