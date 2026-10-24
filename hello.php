<?php
function isNonEmptyString($value) : bool {
    return is_string($value) && trim($value) !== "";
}

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
    if (!isNonEmptyString($title)) {
        http_response_code(422);
        echo json_encode(["error" => "Title must be a non-empty string"]);
        exit;
    }
    $body = $data["body"] ?? null;
    if (!isNonEmptyString($body)) {
        http_response_code(422);
        echo json_encode(["error" => "Body must be a non empty string"]);
        exit;
    }
    $validatedAnnouncement = [
        "title" => trim($title),
        "body" => trim($body),
    ];
    echo json_encode($validatedAnnouncement) . PHP_EOL;
    exit;
}

if ($method !== "GET") {
    http_response_code(405);
    header("Allow: GET, POST");
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$announcements = [
    [
        "title" => "Sports day",
        "body" => "Bring your runners."
    ],
    [
        "title" => "Lunch time",
        "body" => "Time to eat!"
    ]
];


echo json_encode($announcements) . PHP_EOL;
