DELIMITER //
CREATE PROCEDURE GetSongByPlaylist(IN p_playlistId INT, IN p_userId INT)
BEGIN
    SELECT
        m.id_music, m.title, m.artist, m.cover, m.disc_number,
        a.name as album,
        mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        cache_musics.cache_music_id,
        pm.id_playlist_music
        FROM musics m
        JOIN album_musics am on am.id_music = m.id_music
        JOIN albums a on am.id_playlist = a.uid
        JOIN playlist_musics pm on pm.id_music = m.id_music
        JOIN playlists p on p.playlist_id = pm.id_playlist
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc on m.cover = dc.image_url
        WHERE pm.id_playlist = p_playlistId and p.user_id = p_userId
        ORDER BY pm.created_at ASC;
END //
DELIMITER ;