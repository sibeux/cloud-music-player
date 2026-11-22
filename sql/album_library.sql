CREATE TABLE IF NOT EXISTS cybeat_album_library (
    user_id BIGINT UNSIGNED NOT NULL,
    album_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, album_id),
    KEY cybeat_album_library_album (album_id)
) ENGINE=InnoDB;

-- Preserve albums users previously pinned as saved library albums.
INSERT IGNORE INTO cybeat_album_library (user_id, album_id, created_at)
SELECT p.user_id, p.pinnable_album_id, p.created_at
FROM album_pins p JOIN albums a ON a.uid = p.pinnable_album_id
WHERE p.pinnable_album_type = 'album';
