<?php
namespace com\icemalta\kahuna\model;

use com\icemalta\kahuna\model\DBConnect;
use \PDO;

class AccessToken
{
    public static function create($userId)
    {
        $db = DBConnect::getInstance()->getConnection();

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $db->prepare("
            INSERT INTO AccessToken (token, expiryDate, userId)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$token, $expiry, $userId]);

        return $token;
    }

    public static function validate($token)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM AccessToken
            WHERE token = ? AND expiryDate > NOW()
        ");

        $stmt->execute([$token]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($token)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("
            DELETE FROM AccessToken
            WHERE token = ?
        ");

        return $stmt->execute([$token]);
    }
}