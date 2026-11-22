-- Check music yang hilang
SELECT * FROM `dominant_colors`
left JOIN musics on musics.cover = dominant_colors.image_url
WHERE musics.id_music is null;

-- Delete music yang hilang
DELETE dominant_colors.* FROM `dominant_colors`
left JOIN musics on musics.cover = dominant_colors.image_url
WHERE musics.id_music is null;