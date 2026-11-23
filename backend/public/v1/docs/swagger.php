<?php

// Generates the OpenAPI document (JSON) from the attributes in doc_setup.php and paths/*.php.
// Swagger UI (index.html in this folder) loads this file.

// A single PHP warning in the output would break the JSON.
ini_set('display_errors', '0');

require_once __DIR__ . '/../../../vendor/autoload.php';

// Base URL of the API, worked out from this script's URL so it works on XAMPP and in production:
// /trackify/backend/public/v1/docs/swagger.php -> /trackify/backend
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 4));
$basePath = rtrim($basePath, '/');
define('API_BASE_URL', $basePath === '' ? '/' : $basePath);

// swagger-php reads attributes through reflection, so the classes must be loaded first.
require_once __DIR__ . '/doc_setup.php';
$pathFiles = glob(__DIR__ . '/paths/*.php');
foreach ($pathFiles as $file) {
    require_once $file;
}

$openapi = (new \OpenApi\Generator())->generate(array_merge([__DIR__ . '/doc_setup.php'], $pathFiles));

header('Content-Type: application/json; charset=utf-8');
echo $openapi->toJson();
