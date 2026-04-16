<?php
// api/simple_api.php - 简化版API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? 'test';

$response = [
    'status' => 'success',
    'message' => '简化API测试成功',
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => phpversion(),
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost'
    ]
];

// 模拟数据
if ($action === 'status') {
    $response['statistics'] = [
        'students' => 42,
        'societies' => 15,
        'matches' => 128
    ];
} elseif ($action === 'students') {
    $response['data'] = [
        [
            'student_id' => 1,
            'nickname' => 'John Smith',
            'faculty' => 'Business School',
            'major' => 'Business Management'
        ],
        [
            'student_id' => 2,
            'nickname' => 'Emma Wilson',
            'faculty' => 'Health Sciences',
            'major' => 'Nursing'
        ]
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>