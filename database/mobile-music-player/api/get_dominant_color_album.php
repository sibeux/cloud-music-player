<?php

global $ffprobePath, $db;
include './connection.php';
require_once __DIR__ . '/../../../api/image-dominant-color/get_color.php';

function sendJsonResponse(array $data, int $responseCode = 200)
{
	http_response_code($responseCode);
	header('Content-Type: application/json');
	echo json_encode($data);
	die();
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$albumId = isset($data['albumId']) ? $data['albumId'] : 0;
$albumCover = isset($data['albumCover']) ? $data['albumCover'] : '';

if ($albumCover != '') {
	$dominant_color = getDominantColors($albumCover, $db);

	if ($dominant_color != null) {
		sendJsonResponse([
			"status" => "success",
			"albumId" => $albumId,
			"dominant_color" => $dominant_color,
		]);
	} else {
		sendJsonResponse([
			"status" => "error",
			"albumId" => $albumId,
			"reason" => "dominant color null",
		], 400);
	}
} else {
	sendJsonResponse([
		"status" => "error",
		"albumId" => $albumId,
		"reason" => "image_url not set",
	], 400);
}
$db->close();