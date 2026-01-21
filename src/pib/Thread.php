<?php

namespace pib;

use PDO;
class Thread
{
    public PDO $database;
    public function __construct(PDO $dbConnection)
    {
        $this->database = $dbConnection;
    }
    function getThreads(string $boardId)
    {
        $Threads = $this->database->prepare("SELECT * FROM `Threads` WHERE `BoardId` = :boardId");
        $Threads->execute(["boardId" => $boardId]);
        return $Threads->fetchAll(PDO::FETCH_ASSOC);
    }

    function getThreadsWithUser(string $boardId): false|array
    {
        $Threads = $this->database->prepare("SELECT Threads.*, Users.Nickname FROM `Threads` LEFT JOIN Users ON Users.Id = Threads.CreatorId  WHERE `BoardId` = :boardId");
        $Threads->execute(["boardId" => $boardId]);
        return $Threads->fetchAll(PDO::FETCH_ASSOC);
    }

    function getThread(int $threadId): false|array
    {
        $stmt = $this->database->prepare("SELECT Threads.*, Users.Nickname FROM `Threads` LEFT JOIN Users ON Users.Id = Threads.CreatorId WHERE Threads.Id = :id");
        $stmt->execute(["id" => $threadId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    function addThread(?int $creatorId, string $ipAddress, string $boardId, string $title, string $content, ?string $mediaPath = null, ?string $mediaType = null): int
    {
        $query = "INSERT INTO `Threads` (CreatorId, IpAddress, BoardId, Title, Content, MediaPath, MediaType) VALUES (:creatorId, :ipAddress, :boardId, :title, :content, :mediaPath, :mediaType)";

        $stmt = $this->database->prepare($query);
        $stmt->execute([
            "creatorId" => $creatorId,
            "ipAddress" => $ipAddress,
            "boardId" => $boardId,
            "title" => $title,
            "content" => $content,
            "mediaPath" => $mediaPath,
            "mediaType" => $mediaType
        ]);
        return $this->database->lastInsertId();
    }
    function deleteThread(string $id): bool
    {
        $query = "DELETE FROM `Threads` WHERE `Id` = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute(["id" => $id]);
    }
}