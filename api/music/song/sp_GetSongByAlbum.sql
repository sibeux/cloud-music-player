DELIMITER //
CREATE PROCEDURE GetSongByAlbum(IN p_albumId INT)
BEGIN
    SELECT
            m.id_music, m.title, m.artist, m.cover, m.disc_number,
            a.name as album,
            mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
            dc.bg_color, dc.text_color,
            cache_musics.cache_music_id
        FROM musics m
        /*
        Ini bentuk komen multi-line dan lebih aman.
        JOIN playlist ON music.album LIKE CONCAT('%', TRIM(BOTH '\r\n' FROM playlist.name), '%') */
        JOIN album_musics am on am.id_music = m.id_music
        JOIN albums a on am.id_playlist = a.uid
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc on m.cover = dc.image_url
        WHERE a.uid = p_albumId
        ORDER BY m.title ASC;
END //
DELIMITER ;