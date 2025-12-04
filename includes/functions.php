<?php
session_start();
require_once __DIR__.'/koneksi.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, full_name, role_id FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ?: null;
}

function is_admin() {
    $u = current_user();
    return $u && $u['role_id'] == 1;
}

function flash($name, $msg = null) {
    if ($msg === null) {
        if(isset($_SESSION['flash'][$name])) {
            $m = $_SESSION['flash'][$name];
            unset($_SESSION['flash'][$name]);
            return $m;
        }
        return null;
    } else {
        $_SESSION['flash'][$name] = $msg;
    }
}

// Format tanggal dari YYYY-MM-DD ke DD Mon YY
function format_date($date_string) {
    if (empty($date_string)) return '-';
    $date = DateTime::createFromFormat('Y-m-d', $date_string);
    if (!$date) return htmlspecialchars($date_string);
    return $date->format('d M y');
}
?>
