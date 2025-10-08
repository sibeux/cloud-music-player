<?php

global $ffprobePath, $db;
include './connection.php';
require_once __DIR__ . '/../../../api/image-dominant-color/get_color.php';

// --- Fungsi Helper ---
function sendJsonResponse(array $data, int $responseCode = 200)
{
    http_response_code($responseCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    die();
}

if (isset($_POST['image_url'])){
	$dominant_color = null;
	$dominant_color = getDominantColors($_POST['image_url'], $db);

	if ($dominant_color != null){
		sendJsonResponse([
	        "success" => true,
	        "dominant_color" => $dominant_color,
	    ]);
	} else {
		sendJsonResponse([
	        "success" => false,
	        "reason" => "dominant color null",
	    ], 400);
	}
} else {
	sendJsonResponse([
	        "success" => false,
	        "reason" => "image_url not set",
	], 400);
}

// Close connection in the end
$db->close();