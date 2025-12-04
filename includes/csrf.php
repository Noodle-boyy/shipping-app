<?php
function csrf_token() {
    if (!isset($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['_csrf'];
}
function csrf_verify($token) {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}
?>
