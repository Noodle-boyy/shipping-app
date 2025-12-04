<?php
require_once __DIR__.'/functions.php';
if (!is_logged_in()) {
    header('Location: /public/login.php');
    exit;
}
?>
