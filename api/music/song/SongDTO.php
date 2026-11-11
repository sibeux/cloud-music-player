<?php

require_once __DIR__ . '/../../../utils/utils.php';

class SongDTO implements JsonSerializable {
    public $id_music;
    public $title;
    public $artist;
    public $artists;
    public $cover;
    public $disc_number;
    public $album;
    public $metadata_id_music;
    public $codec_name;
    public $music_quality;
    public $sample_rate;
    public $bit_rate;
    public $bits_per_raw_sample;
    public $bg_color;
    public $text_color;
    public $cache_music_id;
    public $played_at;

    public function __construct($data) {
        $this->id_music = isset($data['id_music']) ? (int)$data['id_music'] : 0;
        $this->title = isset($data['title']) ? $data['title'] : '';
        $this->artist = isset($data['artist']) ? $data['artist'] : '';
        $this->artists = isset($data['artists']) && is_array($data['artists']) ? $data['artists'] : [];
        $this->cover = isset($data['cover']) ? coverUrlFormatter($data['cover']) : '';
        $this->disc_number = isset($data['disc_number']) ? (int)$data['disc_number'] : 1;
        $this->album = isset($data['album']) ? $data['album'] : '';
        $this->metadata_id_music = isset($data['metadata_id_music']) ? (int)$data['metadata_id_music'] : null;
        $this->codec_name = isset($data['codec_name']) ? $data['codec_name'] : null;
        $this->music_quality = isset($data['music_quality']) ? $data['music_quality'] : null;
        $this->sample_rate = isset($data['sample_rate']) ? $data['sample_rate'] : null;
        $this->bit_rate = isset($data['bit_rate']) ? $data['bit_rate'] : null;
        $this->bits_per_raw_sample = isset($data['bits_per_raw_sample']) ? $data['bits_per_raw_sample'] : null;
        $this->bg_color = isset($data['bg_color']) ? $data['bg_color'] : null;
        $this->text_color = isset($data['text_color']) ? $data['text_color'] : null;
        $this->cache_music_id = isset($data['cache_music_id']) ? (int)$data['cache_music_id'] : null;
        $this->played_at = isset($data['played_at']) ? $data['played_at'] : null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        $data = [
            'id_music' => $this->id_music,
            'title' => $this->title,
            'artist' => $this->artist,
            'artists' => $this->artists,
            'cover' => $this->cover,
            'disc_number' => $this->disc_number,
            'album' => $this->album,
            'metadata_id_music' => $this->metadata_id_music,
            'codec_name' => $this->codec_name,
            'music_quality' => $this->music_quality,
            'sample_rate' => $this->sample_rate,
            'bit_rate' => $this->bit_rate,
            'bits_per_raw_sample' => $this->bits_per_raw_sample,
            'bg_color' => $this->bg_color,
            'text_color' => $this->text_color,
            'cache_music_id' => $this->cache_music_id,
        ];
        if ($this->played_at !== null) {
            $data['played_at'] = $this->played_at;
        }
        return $data;
    }
}

/**
 * Attach normalized Artist entities to song rows without multiplying songs
 * when a future credit maps to more than one Artist ID.
 *
 * @param mysqli $db
 * @param array $songs Raw rows keyed by database column names.
 * @return SongDTO[]
 */
function hydrateSongsWithArtists($db, array $songs)
{
    if (empty($songs)) {
        return [];
    }

    $musicIds = array_values(array_unique(array_filter(array_map(function ($song) {
        return isset($song['id_music']) ? (int) $song['id_music'] : 0;
    }, $songs))));
    $artistsByMusic = [];

    foreach (array_chunk($musicIds, 500) as $idChunk) {
        if (empty($idChunk)) {
            continue;
        }

        $placeholders = implode(',', array_fill(0, count($idChunk), '?'));
        $types = str_repeat('i', count($idChunk));
        $query = "SELECT ma.id_music, a.id_artist, a.name
            FROM cybeat_music_artists ma
            JOIN cybeat_artists a ON a.id_artist = ma.id_artist
            WHERE ma.id_music IN ($placeholders)
            ORDER BY ma.id_music ASC, ma.id_artist ASC";
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$idChunk);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($artist = $result->fetch_assoc()) {
            $musicId = (int) $artist['id_music'];
            $artistsByMusic[$musicId][] = [
                'id_artist' => (int) $artist['id_artist'],
                'name' => $artist['name'],
            ];
        }

        $stmt->close();
    }

    return array_map(function ($song) use ($artistsByMusic) {
        $musicId = isset($song['id_music']) ? (int) $song['id_music'] : 0;
        $song['artists'] = $artistsByMusic[$musicId] ?? [];
        return new SongDTO($song);
    }, $songs);
}
