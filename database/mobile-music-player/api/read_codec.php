<?php
$file = $_POST['url'];
// Jalankan ffprobe untuk mendapatkan codec info dalam format JSON
$command = "/home/sibe5579/ffmpeg/fffprobe -i \"$file\" -show_streams -show_format -print_format json 2>&1";
// $metadata = shell_exec("ffmpeg -i $file -f ffmetadata -");
$codec = shell_exec($command);

// echo "<pre>$metadata</pre>";
echo "<pre>$codec</pre>";