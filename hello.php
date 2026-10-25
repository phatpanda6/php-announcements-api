<?php
require_once __DIR__ . "/database-connection.php";
require_once __DIR__ . "/AnnouncementRepository.php";
require_once __DIR__ . "/AnnouncementValidator.php";

$repository = new AnnouncementRepository($database);
$validator = new AnnouncementValidator();

header("Content-Type: application/json");
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $rawBody = file_get_contents("php://input");
    $data = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]) . PHP_EOL;
        exit;
    }
    $title = $data["title"] ?? null;
    if (!$validator->isNonEmptyString($title)) {
        http_response_code(422);
        echo json_encode(["error" => "Title must be a non-empty string"]);
        exit;
    }
    $body = $data["body"] ?? null;
    if (!$validator->isNonEmptyString($body)) {
        http_response_code(422);
        echo json_encode(["error" => "Body must be a non empty string"]);
        exit;
    }
    $validatedAnnouncement = [
        "title" => trim($title),
        "body" => trim($body),
    ];
    $repository->create(
        $validatedAnnouncement["title"],
        $validatedAnnouncement["body"]
    );

    http_response_code(201);
    echo json_encode($validatedAnnouncement) . PHP_EOL;
    exit;
}

if ($method !== "GET") {
    http_response_code(405);
    header("Allow: GET, POST");
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$announcements = $repository->findAll();


echo json_encode($announcements) . PHP_EOL;
