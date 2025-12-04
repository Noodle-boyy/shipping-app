<?php
require_once __DIR__.'/../includes/auth_check.php';
require_once __DIR__.'/../includes/koneksi.php';
require_once __DIR__.'/../includes/csrf.php';
require_once __DIR__.'/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_POST['_csrf']) || !csrf_verify($_POST['_csrf'])) {
    die('Invalid CSRF token');
}

// collect fields (trim strings)
$vanning_date = isset($_POST['vanning_date']) ? trim($_POST['vanning_date']) : null;
$destination = isset($_POST['destination']) ? trim($_POST['destination']) : null;
$country = isset($_POST['country']) ? trim($_POST['country']) : null;
$port = isset($_POST['port']) ? trim($_POST['port']) : null;
$port_code = isset($_POST['port_code']) ? trim($_POST['port_code']) : null;
$do_no = isset($_POST['do_no']) ? trim($_POST['do_no']) : null;
$vessel_name = isset($_POST['vessel_name']) ? trim($_POST['vessel_name']) : null;
$etd = isset($_POST['etd']) ? trim($_POST['etd']) : null;
$cy_open = isset($_POST['cy_open']) ? trim($_POST['cy_open']) : null;
$cy_closing = isset($_POST['cy_closing']) ? trim($_POST['cy_closing']) : null;
$depo_name = isset($_POST['depo_name']) ? trim(``$_POST['depo_name']) : null;
$cy_name = isset($_POST['cy_name']) ? trim($_POST['cy_name']) : null;
$pic_name = isset($_POST['pic_name']) ? trim($_POST['pic_name']) : null;

// numeric fields
$container_20 = isset($_POST['container_20']) && $_POST['container_20'] !== '' ? intval($_POST['container_20']) : 0;
$container_40hc = isset($_POST['container_40hc']) && $_POST['container_40hc'] !== '' ? intval($_POST['container_40hc']) : 0;

$id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
$existing_pdf = isset($_POST['existing_pdf']) ? $_POST['existing_pdf'] : null;
$user_id = $_SESSION['user_id'] ?? null;

// file upload
$upload_dir = __DIR__ . '/../uploads/do_files/';
if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
$pdf_filename = $existing_pdf;
if (!empty($_FILES['pdf_do']['name'])) {
    $file = $_FILES['pdf_do'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf'];
    $maxBytes = 8 * 1024 * 1024;
    if (in_array($ext, $allowed) && $file['size'] <= $maxBytes) {
        $newname = uniqid('do_') . '.' . $ext;
        $target = $upload_dir . $newname;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            if (!empty($existing_pdf) && $existing_pdf !== $newname) {
                $oldpath = $upload_dir . $existing_pdf;
                if (is_file($oldpath)) @unlink($oldpath);
            }
            $pdf_filename = $newname;
        }
    }
}
if (isset($_POST['remove_pdf']) && $_POST['remove_pdf']=='1') {
    if (!empty($existing_pdf)) { $oldpath = $upload_dir . $existing_pdf; if (is_file($oldpath)) @unlink($oldpath); }
    $pdf_filename = null;
}

if ($id === null) {
    $sql = "INSERT INTO shipping_plan (vanning_date,destination,country,port,port_code,do_no,vessel_name,etd,cy_open,cy_closing,depo_name,cy_name,container_20,container_40hc,pic_name,pdf_do,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die('Prepare failed: '.htmlspecialchars($conn->error));
    $types = 'ssssssssssssiissi';
    $stmt->bind_param(
        $types,
        $vanning_date,
        $destination,
        $country,
        $port,
        $port_code,
        $do_no,
        $vessel_name,
        $etd,
        $cy_open,
        $cy_closing,
        $depo_name,
        $cy_name,
        $container_20,
        $container_40hc,
        $pic_name,
        $pdf_filename,
        $user_id
    );
    if (!$stmt->execute()) die('Insert failed: '.htmlspecialchars($stmt->error));
    $stmt->close();
} else {
    $sql = "UPDATE shipping_plan SET vanning_date=?, destination=?, country=?, port=?, port_code=?, do_no=?, vessel_name=?, etd=?, cy_open=?, cy_closing=?, depo_name=?, cy_name=?, container_20=?, container_40hc=?, pic_name=?, pdf_do=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die('Prepare failed: '.htmlspecialchars($conn->error));
    $types = 'ssssssssssssiissi';
    $stmt->bind_param(
        $types,
        $vanning_date,
        $destination,
        $country,
        $port,
        $port_code,
        $do_no,
        $vessel_name,
        $etd,
        $cy_open,
        $cy_closing,
        $depo_name,
        $cy_name,
        $container_20,
        $container_40hc,
        $pic_name,
        $pdf_filename,
        $id
    );
    if (!$stmt->execute()) die('Update failed: '.htmlspecialchars($stmt->error));
    $stmt->close();
}

header('Location: index.php');
exit;
