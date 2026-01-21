<?php

namespace pib;

use PDO;

class User
{
    public PDO $database;

    public function setDatabase(PDO $database): void
    {
        $this->database = $database;
    }

    public function __construct(PDO $dbConnection)
    {
        $this->database = $dbConnection;
    }

    public function getUsers()
    {
        $users = $this->database->prepare("SELECT * FROM `Users`");
        $users->execute();
        return $users->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser(int $id)
    {
        $users = $this->database->prepare("SELECT * FROM `Users` WHERE Id = :id");
        $users->execute(["id" => $id]);
        return $users->fetch(PDO::FETCH_ASSOC);
    }


    public function addUser(string $nickname, string $hash, ?string $role = null)
    {
        $hash = password_hash($hash, PASSWORD_DEFAULT);
        $query = "INSERT INTO `Users` (Nickname, Hash, Role) VALUES (:nickname, :hash, :role)";
        $stmt = $this->database->prepare($query);
        if (is_null($role)) {
            $stmt->execute([
                "nickname" => $nickname,
                "hash" => $hash,
                "role" => null
            ]);

        } else {
            $stmt->execute([
                "nickname" => $nickname,
                "hash" => $hash,
                "role" => $role
            ]);
        }
    }

    public function verifyLogin(string $nickname, string $password)
    {
        $query = "SELECT * FROM `Users` WHERE `Nickname` = :nickname";
        $stmt = $this->database->prepare($query);
        $stmt->execute(["nickname" => $nickname]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Hash'])) {
            return $user;
        } else {
            return false;
        }
    }

    public function updateUserRole(int $userId, ?string $role): bool
    {
        $query = "UPDATE users SET users.Role = :role WHERE users.Id = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute(["id" => $userId, "role" => $role]);
    }


}
