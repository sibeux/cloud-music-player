<?php
$file = $_POST['url'];

// Jalankan ffprobe untuk mendapatkan codec info dalam format JSON
$command = "ffprobe -i \"$file\" -show_streams -show_format -print_format json 2>&1";
// $metadata = shell_exec("ffmpeg -i $file -f ffmetadata -");
$codec = shell_exec($command);

// echo "<pre>$metadata</pre>";
// echo "<pre>$codec</pre>";

// Convert the data array to JSON format
$json_data = json_encode($codec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Check if JSON conversion was successful
if ($json_data === false) {
    die("JSON encoding failed");
}

// Output the JSON data
echo $json_data;