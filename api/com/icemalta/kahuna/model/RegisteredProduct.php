<?php
namespace com\icemalta\kahuna\model;

use com\icemalta\kahuna\model\DBConnect;
use \PDO;

class RegisteredProduct
{
    // Register a product for a user
    public static function register($userId, $productId, $purchaseDate)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("
            INSERT INTO RegisteredProduct (purchaseDate, userId, productId)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([
            $purchaseDate,
            $userId,
            $productId
        ]);
    }

    // Get all products registered by a user
    public static function getByUser($userId)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT p.serialNumber, p.name, p.warrantyYears, rp.purchaseDate
            FROM RegisteredProduct rp
            JOIN Product p ON rp.productId = p.id
            WHERE rp.userId = ?
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single registered product by user and serial number
    public static function getByUserAndSerial($userId, $serialNumber)
    {
        $db = DBConnect::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT p.serialNumber, p.name, p.warrantyYears, rp.purchaseDate
            FROM RegisteredProduct rp
            JOIN Product p ON rp.productId = p.id
            WHERE rp.userId = ? AND p.serialNumber = ?
        ");

        $stmt->execute([$userId, $serialNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}