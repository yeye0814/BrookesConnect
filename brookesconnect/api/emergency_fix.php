<?php
// emergency_fix.php - 紧急修复API
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$action = $_GET['action'] ?? 'status';

try {
    // 直接数据库连接
    $host = "localhost";
    $dbname = "matching";  // 改回 matching
    $username = "root";
    $password = ""; // XAMPP默认空密码
    
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $response = [
        "status" => "success",
        "message" => "Emergency API - Database Connected",
        "database" => "matching",
        "action" => $action,
        "timestamp" => date('Y-m-d H:i:s')
    ];
    
    switch ($action) {
        case 'status':
            $student_count = $conn->query("SELECT COUNT(*) as c FROM students WHERE is_active = TRUE")->fetch()['c'] ?? 0;
            $society_count = $conn->query("SELECT COUNT(*) as c FROM brookes_societies WHERE is_active = TRUE")->fetch()['c'] ?? 0;
            $match_count = $conn->query("SELECT COUNT(*) as c FROM matches WHERE is_active = TRUE")->fetch()['c'] ?? 0;
            
            $response["statistics"] = [
                "students" => (int)$student_count,
                "societies" => (int)$society_count,
                "matches" => (int)$match_count
            ];
            break;
            
        case 'tables':
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $response["tables"] = $tables;
            $response["table_count"] = count($tables);
            break;
    }
    
} catch (PDOException $e) {
    $response = [
        "status" => "error",
        "message" => "数据库连接失败",
        "error" => $e->getMessage(),
        "database" => "matching",
        "debug" => [
            "host" => $host,
            "username" => $username,
            "password" => "****"
        ]
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>