-- Prepare a staging table for user-reviewed artist mappings.
-- This clears only artist_unmapped_manual_mapping_stage.

SELECT DATABASE() AS active_database, VERSION() AS mysql_version;

CREATE TABLE IF NOT EXISTS artist_unmapped_manual_mapping_stage (
    source_credit VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    song_count INT UNSIGNED NOT NULL,
    artist_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    KEY idx_artist_unmapped_manual_source (source_credit(191)),
    KEY idx_artist_unmapped_manual_name (artist_name(191))
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_bin;

TRUNCATE TABLE artist_unmapped_manual_mapping_stage;

-- Import artist_unmapped_manual_mapping_batch.csv with these columns in order:
-- source_credit, song_count, artist_name.
-- Do not import the CSV header as a data row.
-- Expected after import: 49 mappings, 46 exact source credits, 12 artists,
-- and 1,914 artist-song links.

SELECT
    COUNT(*) AS staged_mappings,
    COUNT(DISTINCT BINARY source_credit) AS staged_source_credits,
    COUNT(DISTINCT BINARY artist_name) AS staged_artists,
    COALESCE(SUM(song_count), 0) AS expected_artist_song_links
FROM artist_unmapped_manual_mapping_stage;

