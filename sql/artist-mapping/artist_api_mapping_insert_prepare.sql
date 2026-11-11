-- Prepare a dedicated staging table for artist_api_mapping_insert_batch.csv.
-- Run this before importing the CSV. It clears only this staging table.
-- Do not run this file after importing the CSV, or the staged rows will be cleared.

SELECT DATABASE() AS active_database, VERSION() AS mysql_version;

CREATE TABLE IF NOT EXISTS artist_api_mapping_insert_stage (
    source_credit VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    song_count INT UNSIGNED NOT NULL,
    id_artist INT NOT NULL,
    artist_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    matched_component VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    source_mapping_status VARCHAR(64) NOT NULL,
    mapping_method VARCHAR(64) NOT NULL,
    KEY idx_artist_api_mapping_stage_artist (id_artist)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_bin;

TRUNCATE TABLE artist_api_mapping_insert_stage;

-- Import artist_api_mapping_insert_batch.csv into artist_api_mapping_insert_stage.
-- Keep the CSV column order: source_credit, song_count, id_artist, artist_name,
-- matched_component, source_mapping_status, mapping_method.
