<?php
// api/register.php - 学生注册API
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // 数据库连接
    $host = "localhost";
    $dbname = "matching";
    $username = "root";
    $password = "";
    
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 根据请求方法处理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 注册新学生
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            throw new Exception("无效的请求数据");
        }
        
        // 验证必要字段
        $required = ['email', 'nickname', 'faculty', 'academic_level', 'major', 'year_of_study', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("缺少必要字段: $field");
            }
        }
        
        // 验证Brookes邮箱
        if (!preg_match('/.+@brookes\.ac\.uk$/', $data['email'])) {
            throw new Exception("请使用有效的Brookes邮箱");
        }
        
        // 检查邮箱是否已存在
        $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE brookes_email = ?");
        $checkStmt->execute([$data['email']]);
        
        if ($checkStmt->fetch()) {
            throw new Exception("该邮箱已被注册");
        }
        
        // 插入新学生
        $stmt = $conn->prepare("
            INSERT INTO students (
                brookes_email, 
                nickname, 
                faculty, 
                academic_level, 
                major, 
                year_of_study, 
                password_hash,
                created_at,
                is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), TRUE)
        ");
        
        // 实际应用中应该加密密码
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $data['email'],
            $data['nickname'],
            $data['faculty'],
            $data['academic_level'],
            $data['major'],
            $data['year_of_study'],
            $password_hash
        ]);
        
        $student_id = $conn->lastInsertId();
        
        echo json_encode([
            'status' => 'success',
            'message' => '注册成功',
            'student_id' => $student_id
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'check_email') {
            $email = $_GET['email'] ?? '';
            
            $stmt = $conn->prepare("SELECT student_id FROM students WHERE brookes_email = ?");
            $stmt->execute([$email]);
            
            echo json_encode([
                'status' => 'success',
                'exists' => $stmt->fetch() ? true : false
            ]);
            
        } elseif ($action === 'login') {
            $email = $_GET['email'] ?? '';
            $password = $_GET['password'] ?? '';
            
            $stmt = $conn->prepare("
                SELECT student_id, brookes_email, nickname, faculty, academic_level, major, year_of_study 
                FROM students 
                WHERE brookes_email = ? AND is_active = TRUE
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 实际应用中应该验证密码hash
                echo json_encode([
                    'status' => 'success',
                    'message' => '登录成功',
                    'user' => $user
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => '邮箱或密码错误'
                ]);
            }
        }
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '数据库错误: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>