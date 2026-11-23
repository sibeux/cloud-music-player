-- Run once on the CyBeat database after deploying the cover URL normalization fix.
-- Pause requests/jobs that write dominant_colors until this script finishes.
-- DDL statements implicitly commit in MySQL/MariaDB; this is not an atomic migration.
-- Use a full image_url unique key, not a prefix key: different covers can share a prefix.

-- Preserve the rows removed below for inspection/recovery.
CREATE TABLE IF NOT EXISTS dominant_colors_backup_20261124 LIKE dominant_colors;

INSERT IGNORE INTO dominant_colors_backup_20261124
SELECT dc.*
FROM dominant_colors dc
WHERE dc.image_url = 'cdncloudflare/api/music/stream'
   OR EXISTS (
       SELECT 1
       FROM dominant_colors newer
       WHERE newer.image_url = dc.image_url
         AND newer.dominantcolor_id > dc.dominantcolor_id
   );

-- These cache entries lost their cover identity and cannot be mapped back reliably.
-- The application recalculates their colors when the covers are processed again.
DELETE FROM dominant_colors
WHERE image_url = 'cdncloudflare/api/music/stream';

-- Keep the most recently inserted cache entry (highest ID) for each image URL.
DELETE older
FROM dominant_colors older
JOIN dominant_colors newer
  ON newer.image_url = older.image_url
 AND newer.dominantcolor_id > older.dominantcolor_id;

-- Makes the existing INSERT ... ON DUPLICATE KEY UPDATE safe for concurrent writes.
ALTER TABLE dominant_colors
    ADD UNIQUE KEY uq_dominant_colors_image_url (image_url);

-- Verification: the first query should return no rows.
SELECT image_url, COUNT(*) AS duplicate_count
FROM dominant_colors
GROUP BY image_url
HAVING COUNT(*) > 1;

SHOW INDEX FROM dominant_colors
WHERE Key_name = 'uq_dominant_colors_image_url';
