<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "1 START<br>";

require __DIR__ . '/vendor/autoload.php';

echo "2 AUTOLOAD OK<br>";

require __DIR__ . '/func.php';

echo "3 FUNC OK<br>";

require __DIR__ . '/database/db.php';

echo "4 DB OK<br>";
