-- Migration: Add foreign key constraints to cybeat_album_library
-- Automatically cascades deletion of library entries when user or album is deleted.

-- 1. Match column types exactly with parent tables:
--    - users.user_id: INT UNSIGNED
--    - albums.uid:    INT (SIGNED)
ALTER TABLE cybeat_album_library
    MODIFY user_id INT UNSIGNED NOT NULL,
    MODIFY album_id INT NOT NULL;

-- 2. Clean up orphan rows if any exist before applying foreign keys
DELETE cal FROM cybeat_album_library cal
LEFT JOIN users u ON u.user_id = cal.user_id
WHERE u.user_id IS NULL;

DELETE cal FROM cybeat_album_library cal
LEFT JOIN albums a ON a.uid = cal.album_id
WHERE a.uid IS NULL;

-- 3. Add foreign key constraints
ALTER TABLE cybeat_album_library
    ADD CONSTRAINT fk_cybeat_album_library_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cybeat_album_library_album
        FOREIGN KEY (album_id) REFERENCES albums (uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
