<?php
namespace pib;
use PDO;
class Board
{
    public PDO $database;
    public function __construct(PDO $dbConnection)
    {
        $this->database = $dbConnection;
    }

    function getBoards()
    {
        $boards = $this->database->prepare("SELECT * FROM `Boards`");
        $boards->execute();
        return $boards->fetchAll(PDO::FETCH_ASSOC);
    }

    function getBoardsWithStats() {
        $boards = $this->database->prepare("
        SELECT
    brd.*,
    usr.Nickname as CreatorNickname,
    COUNT(DISTINCT thd.Id) AS ThreadCount,
    COUNT(DISTINCT cmt.Id) AS CommentCount
FROM
    `Boards` brd
        JOIN piBoard.Users usr ON usr.Id = brd.CreatorId
        LEFT JOIN
    `piBoard`.`Threads` thd ON brd.Id = thd.BoardId
        LEFT JOIN
    `piBoard`.`Comments` cmt ON cmt.ThreadId = thd.Id
GROUP BY
    brd.Id");
        $boards->execute();
        return $boards->fetchAll(PDO::FETCH_ASSOC);
    }
    function getBoardsName()
    {
        $boards = $this->database->prepare("SELECT FullName FROM `Boards`");
        $boards->execute();
        return $boards->fetchAll(PDO::FETCH_ASSOC);
    }
    function addBoard(string $id, int $creatorid, string $fullname)
    {
        $boards = $this->database->prepare("INSERT INTO `Boards`(Id, CreatorId, FullName) VALUES (:id, :creatorid, :fullname)");
        $boards->execute(["id"=>$id, "creatorid"=>$creatorid, "fullname"=>$fullname]);
    }
    function deleteBoard(string $id): bool
    {
        $query = "DELETE FROM `Boards` WHERE `Id` = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute(["id" => $id]);
    }

    public function getBoard(string $id)
    {
        $query = "SELECT * FROM `Boards` WHERE `Id` = :id";
        $stmt = $this->database->prepare($query);
        $stmt->execute(["id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}