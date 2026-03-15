<?php

function pin($db, $userId, $albumId, $albumType) {
    $stmt = $db->prepare("INSERT INTO album_pins (user_id, pinnable_album_id, pinnable_album_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $albumId, $albumType);
    $stmt->execute();
    $stmt->close();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Album successfully pinned"
    ]);
}
