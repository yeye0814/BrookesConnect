<?php
class Database {
    private $host = "localhost";
    private $db_name = "matching";  // 改回 matching
    private $username = "root";
    private $password = ""; // XAMPP默认空密码
    private $port = 3306;
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // 构建DSN
            $dsn = "mysql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=utf8mb4";
            
            // 设置PDO选项
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            error_log("数据库连接成功 - 连接到: " . $this->db_name);
            
        } catch(PDOException $exception) {
            error_log("数据库连接错误: " . $exception->getMessage());
            
            // 返回友好的错误信息
            throw new Exception(json_encode([
                "status" => "error",
                "message" => "无法连接到数据库",
                "details" => [
                    "host" => $this->host,
                    "database" => $this->db_name,
                    "error" => $exception->getMessage()
                ]
            ]));
        }

        return $this->conn;
    }
    
    /**
     * 测试连接并返回详细信息
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            
            // 获取数据库信息
            $dbInfo = $conn->query("SELECT DATABASE() as db_name, VERSION() as version")->fetch();
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'success' => true,
                'database' => $dbInfo['db_name'],
                'version' => $dbInfo['version'],
                'tables' => $tables,
                'table_count' => count($tables)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>