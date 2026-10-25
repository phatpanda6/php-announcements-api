<?php
require_once __DIR__ . "/AnnouncementRepository.php";

$database = new PDO("sqlite::memory:");
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$database->exec("
    CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    body TEXT NOT NULL
    )
");

$repository = new AnnouncementRepository($database);

$findAllTest = $repository->findAll();

if ($findAllTest === []) {
    echo json_encode(["PASS" => "empty database returns no announcements"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "empty database returns no announcements"]) . PHP_EOL;
    exit(1);
}

$repository->create("Music club", "Meet after school.");

$actual = $repository->findAll();

$expected = [
    [
        "id" => 1,
        "title" => "Music club",
        "body" => "Meet after school."
    ]
];

if ($actual === $expected) {
    echo json_encode(["PASS" => "creates and retrieves announcement"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "creates and retrieves announcement"]) . PHP_EOL;
    exit(1);
}

$repository->create("Principal's update", "Bring your student's permission slip.");

$actual = $repository->findAll();

$expected = [
    "id" => 2,
    "title" => "Principal's update",
    "body" => "Bring your student's permission slip."
];

if ($actual[1] === $expected) {
    echo json_encode(["PASS" => "preserves apostrophes in saved text"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "preserves apostrophes in saved text"]) . PHP_EOL;
    exit(1);
}
