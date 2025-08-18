<?php

include './connection.php';

$sql = "";
$metadata_sql = "";
$delete = "";

if (isset($_POST['music_id'])) {

    // 1. Eksekusi query untuk 'recents_music'
    $stmt_recents = $db->prepare("INSERT INTO recents_music (uid_music, played_at) VALUES (?, NOW())");
    $stmt_recents->bind_param("i", $_POST['music_id']); // i = integer
    if (!$stmt_recents->execute()) {
        die("Error inserting recents: " . $stmt_recents->error);
    }
    $stmt_recents->close();

    // 2. Eksekusi query untuk 'metadata_music' (INI YANG ANDA CARI)
    $stmt_metadata = $db->prepare(
        "INSERT INTO metadata_music (metadata_id_music, codec_name, music_quality, sample_rate, bit_rate, bits_per_raw_sample) VALUES (?, ?, ?, ?, ?, ?)"
    );
    // Tipe data: i = integer, s = string
    $stmt_metadata->bind_param(
        "isssss", 
        $_POST['music_id'],
        $_POST['codec_name'],
        $_POST['quality'],
        $_POST['sample_rate'],
        $_POST['bit_rate'],
        $_POST['bits_per_raw_sample']
    );

    if (!$stmt_metadata->execute()) {
        // Jika gagal, error akan terlihat di sini!
        die("Error inserting metadata: " . $stmt_metadata->error);
    }
    $stmt_metadata->close();
    
    // 3. Eksekusi query untuk 'delete'
    $delete_sql = "DELETE FROM recents_music
                   WHERE uid_recents NOT IN (
                       SELECT uid_recents FROM (
                           SELECT uid_recents FROM recents_music ORDER BY played_at DESC LIMIT 500
                       ) AS last_500
                   )";
    if (!$db->query($delete_sql)) {
        die("Error deleting old recents: " . $db->error);
    }

    echo "Success";

} else {
    // Beri respons jika tidak ada POST data
    http_response_code(400);
    echo "Error: music_id is not set.";
}

// Tutup koneksi di akhir
$db->close();