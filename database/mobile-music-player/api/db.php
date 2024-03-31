<?php

include './db.php';

$query = $db->query("SELECT * FROM music");
$result = array();

while ($row = $query->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode($result);
