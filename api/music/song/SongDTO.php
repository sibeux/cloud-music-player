<?php

class SongDTO implements JsonSerializable {
    public $id_music;
    public $title;
    public $artist;
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

    public function __construct($data) {
        $this->id_music = isset($data['id_music']) ? (int)$data['id_music'] : 0;
        $this->title = isset($data['title']) ? $data['title'] : '';
        $this->artist = isset($data['artist']) ? $data['artist'] : '';
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
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return [
            'id_music' => $this->id_music,
            'title' => $this->title,
            'artist' => $this->artist,
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
    }
}
