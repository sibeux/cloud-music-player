-- Run once after removing any existing duplicate rows.
-- Prevents the same song from being inserted twice in one playlist under concurrent requests.
ALTER TABLE playlist_musics
    ADD UNIQUE KEY uq_playlist_musics_playlist_song (id_playlist, id_music);
