<?php
include './connection.php';

$sql = "TRUNCATE TABLE cache_music";

if ($db->query($sql) === TRUE) {
    echo "[" . date("Y-m-d H:i:s") . "] cache_music berhasil dikosongkan dan ID reset ke 1.\n";
} else {
    echo "[" . date("Y-m-d H:i:s") . "] Gagal mengosongkan tabel: " . $db->error . "\n";
}

$db->close();
?>