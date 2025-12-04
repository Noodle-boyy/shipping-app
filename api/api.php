<?php
require_once __DIR__ . '/../includes/koneksi.php';

// Simple token auth (generate token per user or use API key table)
$API_KEY = $_GET['key'] ?? '';
// for demo, allow blank? better validate token in production
if (empty($API_KEY)) {
    http_response_code(401);
    echo json_encode(['error'=>'API key required']);
    exit;
}

// map key to user or just allow in internal network
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
if ($action == 'list') {
    $q = $conn->query("SELECT * FROM shipping_plan ORDER BY vanning_date ASC LIMIT 1000");
    $arr = [];
    while($r = $q->fetch_assoc()) $arr[] = $r;
    echo json_encode(['data'=>$arr]);
    exit;
}

if ($action == 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM shipping_plan WHERE id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    echo json_encode(['data'=>$r]);
    exit;
}

// untuk create/update/delete, sebaiknya gunakan token + method untuk keamanan
http_response_code(400);
echo json_encode(['error'=>'invalid action']);
exit;   