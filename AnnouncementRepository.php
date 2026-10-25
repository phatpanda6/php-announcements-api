<?php

class AnnouncementRepository
{
    private PDO $database; 

    public function __construct(PDO $database) 
    {
        $this->database = $database; 
    }

    public function findAll(): array {
        $statement = $this->database->query(
            "SELECT id, title, body FROM announcements ORDER BY id"
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}