<?php
require_once __DIR__ . "/AnnouncementRepository.php";

$database = new PDO("sqlite:" . __DIR__ . "/announcements.sqlite");
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$database->exec("
    CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    body TEXT NOT NULL
    )
");

$repository = new AnnouncementRepository($database);

$announcements = $repository->findAll(); 

echo json_encode($announcements) . PHP_EOL;
