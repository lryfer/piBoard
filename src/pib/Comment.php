<?php
namespace pib;
use PDO;
class Comment
{
    public PDO $database;
    public function __construct(PDO $dbConnection)
    {
        $this->database = $dbConnection;
    }
    function getThreadComments(int $ThreadId)
    {
        $comments = $this->database->prepare("SELECT Comments.*, Users.Nickname  FROM `Comments` LEFT JOIN Users ON Users.Id = Comments.CreatorId where Comments.ThreadId = :threadId");
        $comments->execute(["threadId" => $ThreadId]);
        return $comments->fetchAll(PDO::FETCH_ASSOC);
    }

    function getThreadCommentsCount(int $ThreadId): int
    {
        $stmt = $this->database->prepare("SELECT COUNT(*) as count FROM `Comments` WHERE ThreadId = :threadId");
        $stmt->execute(["threadId" => $ThreadId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
    function addComment(?int $creatorId, string $ipAddress, int $threadId, string $content, ?string $mediaPath = null, ?string $mediaType = null): int
    {
        $query = "INSERT INTO `Comments` (CreatorId, IpAddress, ThreadId, Content, MediaPath, MediaType) VALUES (:creatorId, :ipAddress, :threadId, :content, :mediaPath, :mediaType)";
        $stmt = $this->database->prepare($query);
        $stmt->execute([
            "creatorId" => $creatorId,
            "ipAddress" => $ipAddress,
            "threadId" => $threadId,
            "content" => $content,
            "mediaPath" => $mediaPath,
            "mediaType" => $mediaType
        ]);

        return $this->database->lastInsertId();
    }
    function deleteComment(string $id): bool
    {
        $query = "DELETE FROM `Comments` WHERE `Id` = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute(["id" => $id]);
    }

}