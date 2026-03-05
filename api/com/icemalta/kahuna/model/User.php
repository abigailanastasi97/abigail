<?php
namespace com\icemalta\kahuna\model;

use com\icemalta\kahuna\model\DBConnect;
use \PDO;

class User
{
    public static function create($email, $password, $role = "client")
    {
        $db = DBConnect::getInstance()->getConnection();

        // check if email already exists
        $check = $db->prepare("SELECT id FROM User WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        $stmt = $db->prepare("
            INSERT INTO User (email, password, role)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        ]);
    }

    public static function login($email, $password)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }
}