<?php

declare(strict_types=1);
date_default_timezone_set("Africa/Tunis");

spl_autoload_register(function ($class) {
    $configPath = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $class . '.php';
    $controllerPath = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $class . '.php';
    $gatewayPath = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'gateways' . DIRECTORY_SEPARATOR . $class . '.php';

    if (file_exists(__DIR__ . $configPath)) {
        require_once __DIR__ . $configPath;
    } else if (file_exists(__DIR__ . $controllerPath)) {
        require_once __DIR__ . $controllerPath;
    } else if (file_exists(__DIR__ . $gatewayPath)) {
        require_once __DIR__ . $gatewayPath;
    }
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// JSON HEADERS
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json; charset=UTF-8");

$parts = explode("/", $_SERVER['REQUEST_URI']);
$resource = $parts[4] ?? NULL;
if (empty($resource)) {
    echo json_encode(["message" => "Welcome to DigiWire API"]);
    exit();
}

// DATABASE CONSTANTS (CREDENTIALS)
define("DB_HOST", "127.0.0.1");
define("DB_NAME", "ttei_db_v2");
define("DB_USER", "root");
define("DB_PASSWORD", "");
// define("DB_PASSWORD", "dNz!nb^JGY88w+PX");

// INITIALIZE DB
$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);

// ROUTING
switch ($resource) {

    // case "operator-count":
    //     $lineCode = new OperatorCountGateway($db);
    //     $controller = new OperatorCountController($lineCode);
    //     $controller->processRequest();
    //     break;

    case "prodline":
        $prodline = new ProdlineGateway($db);
        $controller = new ProdlineController($prodline);
        $controller->processRequest();
        break;
    
    // case "product-shift":
    //     $productRef = new ProductShiftGateway($db);
    //     $controller = new ProductShiftController($productRef);
    //     $controller->processRequest();
    //     break;
    
    // case "product-reference":
    //     $productShift = new ProductReferenceGateway($db);
    //     $controller = new ProductReferenceController($productShift);
    //     $controller->processRequest();
    //     break;

    default:
        http_response_code(404);
        echo json_encode([
            "error" => "NOT_FOUND",
        ]);
        break;
}