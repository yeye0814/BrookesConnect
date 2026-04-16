<?php
// api_test_simple.php - 最简单的API测试
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// 直接返回成功，不连接数据库
echo json_encode([
    "status" => "success",
    "message" => "API Test Endpoint",
    "timestamp" => date('Y-m-d H:i:s'),
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "request_uri" => $_SERVER['REQUEST_URI'],
    "query_string" => $_SERVER['QUERY_STRING'] ?? '',
    "server_name" => $_SERVER['SERVER_NAME'],
    "server_port" => $_SERVER['SERVER_PORT'],
    "document_root" => $_SERVER['DOCUMENT_ROOT']
], JSON_PRETTY_PRINT);
?>