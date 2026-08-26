<?php
$url = "https://cybeat.sibeux.my.id/api/music/song/?type=album&uid=2189";
$headers = get_headers($url, 1);
print_r($headers);
