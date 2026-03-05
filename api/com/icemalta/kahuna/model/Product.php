<?php
namespace com\icemalta\kahuna\model;

use com\icemalta\kahuna\model\DBConnect;
use \PDO;

class Product
{
    // Get product by serial number
    public static function getBySerial($serialNumber)
    {
        $db = DBConnect::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Product WHERE serialNumber = ?");
        $stmt->execute([$serialNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new product (for admin)
    public static function add($serialNumber, $name, $warrantyYears)
    {
        $db = DBConnect::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO Product (serialNumber, name, warrantyYears)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$serialNumber, $name, $warrantyYears]);
    }

    // Optional: get all products (could be used later)
    public static function getAll()
    {
        $db = DBConnect::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM Product");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}