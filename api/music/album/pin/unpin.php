<?php

function unpin($db, $userId, $albumId, $albumType) {
    $stmt = $db->prepare("DELETE FROM album_pins WHERE user_id = ? AND pinnable_album_id = ? AND pinnable_album_type = ?");
    $stmt->bind_param("iis", $userId, $albumId, $albumType);
    $stmt->execute();
    $stmt->close();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Album successfully unpinned"
    ]);
}