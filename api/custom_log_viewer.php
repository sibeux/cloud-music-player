<?php
$logFile = 'custom.log';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse($lines);
foreach ($lines as $line) {
    echo $line . "<br>";
}
