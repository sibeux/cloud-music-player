-- Apply the validated candidate-single Artist mapping.
-- Preconditions:
--   1. Run only in the intended CyBeat database.
--   2. artist_mapping_stage has 1,348 candidate_for_insert rows totaling 17,459 songs.
--   3. The mismatch query in artist_candidate_single_prepare.sql returned no rows.
--
-- The original musics.artist text is retained. INSERT IGNORE makes this safe to
-- rerun when the unique Artist names and cybeat_music_artists primary key are intact.

START TRANSACTION;

INSERT IGNORE INTO cybeat_artists (name)
SELECT DISTINCT s.artist_name
FROM artist_mapping_stage AS s
WHERE s.mapping_status = 'candidate_for_insert';

INSERT IGNORE INTO cybeat_music_artists (id_music, id_artist)
SELECT m.id_music, a.id_artist
FROM artist_mapping_stage AS s
JOIN musics AS m
    ON BINARY TRIM(m.artist) = BINARY s.source_credit
JOIN cybeat_artists AS a
    ON BINARY a.name = BINARY s.artist_name
WHERE s.mapping_status = 'candidate_for_insert';

COMMIT;

-- Expected: 1,348 distinct Artist IDs and 17,459 song-to-Artist links.
SELECT
    COUNT(DISTINCT a.id_artist) AS mapped_artists,
    SUM(s.song_count) AS expected_song_count
FROM artist_mapping_stage AS s
JOIN cybeat_artists AS a
    ON BINARY a.name = BINARY s.artist_name
WHERE s.mapping_status = 'candidate_for_insert';

SELECT COUNT(*) AS mapped_song_artist_links
FROM cybeat_music_artists AS ma
JOIN musics AS m ON m.id_music = ma.id_music
JOIN artist_mapping_stage AS s
    ON BINARY TRIM(m.artist) = BINARY s.source_credit
WHERE s.mapping_status = 'candidate_for_insert';

-- Expected: zero songs from this batch without the matching Artist link.
SELECT COUNT(*) AS candidate_songs_without_matching_artist
FROM artist_mapping_stage AS s
JOIN musics AS m
    ON BINARY TRIM(m.artist) = BINARY s.source_credit
LEFT JOIN cybeat_artists AS a
    ON BINARY a.name = BINARY s.artist_name
LEFT JOIN cybeat_music_artists AS ma
    ON ma.id_music = m.id_music
    AND ma.id_artist = a.id_artist
WHERE s.mapping_status = 'candidate_for_insert'
  AND ma.id_music IS NULL;
