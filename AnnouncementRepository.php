<?php

class AnnouncementRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function findAll(): array
    {
        $statement = $this->database->query(
            "SELECT id, title, body FROM announcements ORDER BY id"
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $title, string $body): void
    {
        $statement = $this->database->prepare(
            "INSERT INTO announcements (title, body) VALUES (:title, :body)"
        );
        $statement->execute([
            "title" => $title,
            "body" => $body,
        ]);
    }
}
