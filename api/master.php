<?php
require_once __DIR__.'/../includes/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($action === 'countries') {
    $sql = "SELECT id, name, iso_code FROM countries WHERE name LIKE ? ORDER BY name LIMIT 30";
    $like = "%$q%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $arr = [];
    while ($r = $res->fetch_assoc()) $arr[] = $r;
    echo json_encode($arr);
    exit;
}

if ($action === 'ports') {
    $sql = "SELECT p.id, p.name, p.code, c.name AS country FROM ports p LEFT JOIN countries c ON p.country_id = c.id WHERE (p.name LIKE ? OR p.code LIKE ?) ORDER BY p.name LIMIT 50";
    $like = "%$q%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $arr = [];
    while ($r = $res->fetch_assoc()) $arr[] = $r;
    echo json_encode($arr);
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'invalid action']);
exit;