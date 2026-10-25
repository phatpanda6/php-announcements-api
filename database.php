<?php
require_once __DIR__ . "/database-connection.php";
require_once __DIR__ . "/AnnouncementRepository.php";

$repository = new AnnouncementRepository($database);

$announcements = $repository->findAll(); 

echo json_encode($announcements) . PHP_EOL;
