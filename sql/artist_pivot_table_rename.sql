-- Rename the populated pivot table while preserving its rows and indexes.
-- Run in the CyBeat database after confirming that music_artists exists and
-- cybeat_music_artists does not.

RENAME TABLE music_artists TO cybeat_music_artists;

SELECT COUNT(*) AS preserved_song_artist_links
FROM cybeat_music_artists;
