<?php
namespace com\icemalta\kahuna\api;

require 'com/icemalta/kahuna/model/DBConnect.php';
require 'com/icemalta/kahuna/model/User.php';
require 'com/icemalta/kahuna/model/AccessToken.php';
require 'com/icemalta/kahuna/model/Product.php';
require 'com/icemalta/kahuna/model/RegisteredProduct.php';
require 'com/icemalta/kahuna/util/ApiUtil.php';

use com\icemalta\kahuna\model\DBConnect;
use com\icemalta\kahuna\model\User;
use com\icemalta\kahuna\model\AccessToken;
use com\icemalta\kahuna\model\Product;
use com\icemalta\kahuna\model\RegisteredProduct;
use com\icemalta\kahuna\util\ApiUtil;

cors();

$endpoints = [];
$requestData = [];
header("Content-Type: application/json; charset=UTF-8");

function sendResponse($data = null, $code = 200, $error = null)
{
    http_response_code($code);
    $response = [];
    if (!is_null($data)) $response['data'] = $data;
    if (!is_null($error)) $response['error'] = $error;
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        $requestData = $_GET;
        break;
    case 'POST':
        $requestData = $_POST;
        if (empty($requestData)) {
            $json = file_get_contents("php://input");
            $requestData = json_decode($json, true) ?? [];
        }
        break;
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData);
        break;
    case 'DELETE':
        break;
    default:
        sendResponse(null, 405, 'Method not allowed.');
        exit;
}

$parsedURI = parse_url($_SERVER["REQUEST_URI"]);
$path = explode('/', trim($parsedURI["path"], '/'));
$endPoint = end($path);
if ($endPoint === 'api' || $endPoint === 'index.php') $endPoint = "/";

/* ROOT TEST */
$endpoints["/"] = function($requestMethod, $requestData) {
    $db = DBConnect::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) as total FROM Product");
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    sendResponse("Database connected. Products in table: " . $result['total']);
};

/* CREATE ACCOUNT */
$endpoints["createAccount"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "POST") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }
    if (empty($requestData["email"]) || empty($requestData["password"])) {
        sendResponse(null, 400, "Missing required fields.");
        return;
    }
    $role = $requestData["role"] ?? "client";
    $created = User::create($requestData["email"], $requestData["password"], $role);
    if ($created) {
        sendResponse("Account created successfully.", 201);
    } else {
        sendResponse(null, 400, "Email already exists or account creation failed.");
    }
};

/* LOGIN */
$endpoints["login"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "POST") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }
    if (empty($requestData["email"]) || empty($requestData["password"])) {
        sendResponse(null, 400, "Missing email or password.");
        return;
    }

    $user = User::login($requestData["email"], $requestData["password"]);
    if (!$user) {
        sendResponse(null, 401, "Invalid email or password.");
        return;
    }

    $token = AccessToken::create($user['id']);

    $responseData = [
        'userId' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'token' => $token
    ];

    sendResponse($responseData, 200);
};

/* LOGOUT */
$endpoints["logout"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "POST") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }

    $token = $requestData['token'] ?? '';
    if (empty($token)) {
        sendResponse(null, 400, "Missing token.");
        return;
    }

    $user = ApiUtil::getUserFromToken($token);
    if (!$user) {
        sendResponse(null, 401, "Invalid or expired token.");
        return;
    }

    $deleted = AccessToken::delete($token);
    if ($deleted) {
        sendResponse("Successfully logged out.");
    } else {
        sendResponse(null, 500, "Logout failed.");
    }
};

/* REGISTER PRODUCT */
$endpoints["registerProduct"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "POST") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }

    $user = ApiUtil::getUserFromToken($requestData['token'] ?? '');
    if (!$user) {
        sendResponse(null, 401, "Invalid or expired token.");
        return;
    }

    if (empty($requestData["serialNumber"]) || empty($requestData["purchaseDate"])) {
        sendResponse(null, 400, "Missing required fields.");
        return;
    }

    $product = Product::getBySerial($requestData["serialNumber"]);
    if (!$product) {
        sendResponse(null, 404, "Product not found.");
        return;
    }

    $registered = RegisteredProduct::register(
        $user['id'],
        $product['id'],
        $requestData["purchaseDate"]
    );

    if ($registered) {
        sendResponse("Product registered successfully.");
    } else {
        sendResponse(null, 500, "Registration failed.");
    }
};

/* VIEW PRODUCTS */
$endpoints["viewProducts"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "GET") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }

    $user = ApiUtil::getUserFromToken($requestData['token'] ?? '');
    if (!$user) {
        sendResponse(null, 401, "Invalid or expired token.");
        return;
    }

    $products = RegisteredProduct::getByUser($user['id']);
    sendResponse($products);
};

/* ADMIN ADD PRODUCT */
$endpoints["addProduct"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "POST") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }

    // authenticate user
    $user = ApiUtil::getUserFromToken($requestData['token'] ?? '');
    if (!$user) {
        sendResponse(null, 401, "Invalid or expired token.");
        return;
    }

    // check admin role
    if ($user['role'] !== 'admin') {
        sendResponse(null, 403, "Only admins can add products.");
        return;
    }

    // required fields
    $serial = $requestData['serialNumber'] ?? '';
    $name = $requestData['name'] ?? '';
    $warranty = $requestData['warrantyYears'] ?? '';

    if (empty($serial) || empty($name) || empty($warranty)) {
        sendResponse(null, 400, "Missing required fields.");
        return;
    }

    // add product
    $added = Product::add($serial, $name, $warranty);
    if ($added) {
        sendResponse("Product added successfully.", 201);
    } else {
        sendResponse(null, 500, "Failed to add product.");
    }
};

/* VIEW SINGLE PRODUCT */
$endpoints["viewProduct"] = function($requestMethod, $requestData) {
    if ($requestMethod !== "GET") {
        sendResponse(null, 405, "Method not allowed.");
        return;
    }

    // authenticate user
    $user = ApiUtil::getUserFromToken($requestData['token'] ?? '');
    if (!$user) {
        sendResponse(null, 401, "Invalid or expired token.");
        return;
    }

    // serial number required
    $serial = $requestData['serialNumber'] ?? '';
    if (empty($serial)) {
        sendResponse(null, 400, "Missing serialNumber.");
        return;
    }

    // fetch the registered product for this user
    $product = RegisteredProduct::getByUserAndSerial($user['id'], $serial);
    if (!$product) {
        sendResponse(null, 404, "Product not found for this user.");
        return;
    }

    sendResponse($product);
};

/* 404 */
$endpoints["404"] = function($requestMethod, $requestData) {
    sendResponse(null, 404, "Endpoint not found.");
};

function cors()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
}

try {
    if (isset($endpoints[$endPoint])) {
        $endpoints[$endPoint]($requestMethod, $requestData);
    } else {
        $endpoints["404"]($requestMethod, $requestData);
    }
} catch (\Exception $e) {
    sendResponse(null, 500, $e->getMessage());
}