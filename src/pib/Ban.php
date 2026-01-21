<?php

namespace pib;
use PDO;
class Ban
{
    public PDO $database;
    public function __construct(PDO $dbConnection)
    {
        $this->database = $dbConnection;
    }

    function getBan(string $ip_address, int $user_id) {
        $stmt = $this->database->prepare("SELECT Bans.StartDate, Bans.EndDate FROM Bans WHERE IpAddress = :ip_address OR UserId = :id");
        $stmt->execute(["ip_address" => $ip_address, "id" => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getBans() {
        $stmt = $this->database->prepare("SELECT Bans.*, Users.Nickname FROM Bans LEFT JOIN Users ON Bans.UserID = Users.Id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);


    }
    function addBan(?int $userId, ?string $ipAddress, \DateTime $startDate, \DateTime $endDate)
    {
        $query = "INSERT INTO `Bans` (UserId, IpAddress, StartDate, EndDate) VALUES (:userId, :ipAddress, :startDate, :endDate)";

        $stmt = $this->database->prepare($query);
        $stmt->execute([
            "userId" => $userId,
            "ipAddress" => $ipAddress,
            "startDate" => $startDate->format("Y-m-d H:i:s"),
            "endDate" => $endDate->format("Y-m-d H:i:s")
        ]);
    }

    public function deleteBan(int $id)
    {
        $query = "DELETE FROM `Bans` WHERE `Id` = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute(["id" => $id]);
    }

    public function isUserBanned(?int $userId, ?string $ipAddress): bool
    {
        $query = "SELECT * FROM `Bans` WHERE (`UserId` = :userId OR `IpAddress` = :ipAddress) AND `EndDate` > NOW()";
        $stmt = $this->database->prepare($query);
        $stmt->execute(["userId" => $userId, "ipAddress" => $ipAddress]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) return count($result) > 0;
        else return false;
    }



}