<?php
require_once __DIR__.'/../includes/koneksi.php';
require_once __DIR__.'/../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && password_verify($password, $r['password_hash'])) {
        $_SESSION['user_id'] = $r['id'];
        header('Location: index.php');
        exit;
    } else {
        $err = "Username atau password salah";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Shipping Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="bg-white rounded-lg shadow-lg p-8">
      <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Shipping Plan</h1>
        <p class="text-gray-600 text-sm mt-1">Sistem Manajemen Pengiriman</p>
      </div>

      <?php if($err): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/></svg>
          <span><?=htmlspecialchars($err)?></span>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
          <input name="username" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="masukkan username">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input name="password" type="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="masukkan password">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
          Login
        </button>
      </form>

      <div class="mt-4 text-center text-gray-600 text-sm">
        <p>Demo: admin / change_me123</p>
      </div>
    </div>
  </div>
</body>
</html>
