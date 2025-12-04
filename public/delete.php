<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    die('Unauthorized: Only admin can delete');
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Get the record to check if it exists and get the pdf_do filename
$stmt = $conn->prepare("SELECT pdf_do FROM shipping_plan WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: index.php');
    exit;
}

// Delete the record from database
$stmt = $conn->prepare("DELETE FROM shipping_plan WHERE id = ?");
if (!$stmt) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    die('Delete failed: ' . htmlspecialchars($stmt->error));
}
$stmt->close();

// Delete the PDF file if it exists
if (!empty($row['pdf_do'])) {
    $pdf_path = __DIR__ . '/../uploads/do_files/' . $row['pdf_do'];
    if (is_file($pdf_path)) {
        @unlink($pdf_path);
    }
}

header('Location: index.php');
exit;