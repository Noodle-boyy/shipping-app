<?php
require_once __DIR__.'/../includes/auth_check.php';
require_once __DIR__.'/../includes/koneksi.php';
require_once __DIR__.'/../includes/functions.php';
$user = current_user();

// Get filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

// Build query with filters
$sql = "SELECT * FROM shipping_plan WHERE 1=1 ";
$params = [];
$types = '';

// Text search filter
if ($search !== '') {
    $sql .= " AND (destination LIKE ? OR do_no LIKE ? OR vessel_name LIKE ? OR depo_name LIKE ? OR pic_name LIKE ?) ";
    $like = "%".$search."%";
    $params = array_merge($params, [$like,$like,$like,$like,$like]);
    $types .= str_repeat('s', 5);
}

// Date range filter
if ($from_date !== '') {
    $sql .= " AND vanning_date >= ? ";
    $params[] = $from_date;
    $types .= 's';
}

if ($to_date !== '') {
    $sql .= " AND vanning_date <= ? ";
    $params[] = $to_date;
    $types .= 's';
}

$sql .= " ORDER BY vanning_date DESC LIMIT 100";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Check if any filter is active
$has_filter = ($search !== '' || $from_date !== '' || $to_date !== '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shipping Plan - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Shipping Plan</h1>
          <p class="text-sm text-gray-600 mt-1">Hai, <span class="font-semibold"><?=htmlspecialchars($user['full_name'] ?? $user['username'])?></span></p>
        </div>
        <div class="flex flex-col xs:flex-row gap-2">
          <a href="add.php" class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Plan
          </a>
          <a href="export.php?q=<?=urlencode($search)?>&from_date=<?=urlencode($from_date)?>&to_date=<?=urlencode($to_date)?>" class="inline-flex items-center justify-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export
          </a>
          <a href="logout.php" class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Compact Search & Filter -->
    <div class="bg-white rounded-lg shadow mb-6 border border-gray-200">
      <!-- Filter Header (Always Visible) -->
      <div class="px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition" onclick="toggleFilter()">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          <span class="font-medium text-gray-900">Filter & Pencarian</span>
          <?php if ($has_filter): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              Aktif
            </span>
          <?php endif; ?>
        </div>
        <svg id="filter-icon" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
      </div>

      <!-- Filter Content (Collapsible) -->
      <form method="get" id="filter-form" class="hidden px-4 py-4 space-y-3 border-t border-gray-200">
        <!-- Text Search -->
        <div>
          <input name="q" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Cari: destination, DO No, vessel..." value="<?=htmlspecialchars($search)?>">
        </div>

        <!-- Date Range Filter -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <input name="from_date" type="date" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" value="<?=htmlspecialchars($from_date)?>" title="Dari Tanggal">
          <input name="to_date" type="date" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" value="<?=htmlspecialchars($to_date)?>" title="Sampai Tanggal">
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 flex-wrap pt-2">
          <button type="submit" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
          </button>
          <a href="index.php" class="inline-flex items-center gap-1 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg font-medium text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Reset
          </a>
        </div>

        <!-- Active Filters Display -->
        <?php if ($has_filter): ?>
          <div class="p-2 bg-blue-50 border border-blue-200 rounded text-sm">
            <div class="flex flex-wrap gap-2">
              <?php if ($search !== ''): ?>
                <span class="inline-flex items-center gap-1 bg-blue-200 text-blue-900 px-2 py-1 rounded text-xs font-medium">
                  <?=htmlspecialchars(substr($search, 0, 20))?>
                </span>
              <?php endif; ?>
              <?php if ($from_date !== ''): ?>
                <span class="inline-flex items-center gap-1 bg-blue-200 text-blue-900 px-2 py-1 rounded text-xs font-medium">
                  <?=format_date($from_date)?>
                </span>
              <?php endif; ?>
              <?php if ($to_date !== ''): ?>
                <span class="inline-flex items-center gap-1 bg-blue-200 text-blue-900 px-2 py-1 rounded text-xs font-medium">
                  <?=format_date($to_date)?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Result Info -->
    <div class="mb-4 text-sm text-gray-600 flex items-center justify-between">
      <p>Ditemukan <span class="font-semibold text-gray-900 text-base"><?=$result->num_rows?></span> data</p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 gap-6">
      <?php $no=1; while($row=$result->fetch_assoc()): ?>
      <div class="bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg">
          <div class="flex justify-between items-start gap-4">
            <div class="flex-1">
              <p class="text-sm opacity-90">No. <?=$no++?></p>
              <h3 class="text-lg font-bold"><?=htmlspecialchars($row['destination'])?> <span class="text-sm opacity-90">→ <?=htmlspecialchars($row['country'])?></span></h3>
              <p class="text-sm opacity-90 mt-1">DO: <span class="font-mono font-semibold"><?=htmlspecialchars($row['do_no'])?></span></p>
            </div>
            <div class="text-right">
              <p class="text-xs opacity-90">Vanning</p>
              <p class="text-lg font-bold"><?=format_date($row['vanning_date'])?></p>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <!-- Port Info -->
          <div class="border-l-4 border-blue-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">Port</p>
            <p class="text-sm font-semibold text-gray-900"><?=htmlspecialchars($row['port'])?></p>
          </div>

          <!-- Vessel Info -->
          <div class="border-l-4 border-green-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">Vessel</p>
            <p class="text-sm font-semibold text-gray-900"><?=htmlspecialchars(substr($row['vessel_name'], 0, 20))?></p>
          </div>

          <!-- ETD -->
          <div class="border-l-4 border-yellow-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">ETD</p>
            <p class="text-sm font-semibold text-gray-900"><?=format_date($row['etd'])?></p>
          </div>

          <!-- CY Open -->
          <div class="border-l-4 border-orange-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">CY Open</p>
            <p class="text-sm font-semibold text-gray-900"><?=format_date($row['cy_open'])?></p>
          </div>

          <!-- CY Closing -->
          <div class="border-l-4 border-red-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">CY Closing</p>
            <p class="text-sm font-semibold text-gray-900"><?=format_date($row['cy_closing'])?></p>
          </div>

          <!-- DEPO -->
          <div class="border-l-4 border-purple-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">DEPO</p>
            <p class="text-sm font-semibold text-gray-900"><?=htmlspecialchars(substr($row['depo_name'], 0, 15))?></p>
          </div>

          <!-- CY Name -->
          <div class="border-l-4 border-indigo-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">CY</p>
            <p class="text-sm font-semibold text-gray-900"><?=htmlspecialchars(substr($row['cy_name'], 0, 15))?></p>
          </div>

          <!-- PIC -->
          <div class="border-l-4 border-pink-400 pl-4">
            <p class="text-xs text-gray-600 uppercase font-semibold">PIC</p>
            <p class="text-sm font-semibold text-gray-900"><?=htmlspecialchars($row['pic_name'])?></p>
          </div>
        </div>

        <!-- Container Section -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
              <p class="text-xs text-gray-600 uppercase font-semibold">Container 20'</p>
              <p class="text-2xl font-bold text-blue-600"><?=intval($row['container_20'])?></p>
            </div>
            <div class="text-center border-l border-r border-gray-300">
              <p class="text-xs text-gray-600 uppercase font-semibold">Container 40'HC</p>
              <p class="text-2xl font-bold text-blue-600"><?=intval($row['container_40hc'])?></p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-600 uppercase font-semibold">Total</p>
              <p class="text-2xl font-bold text-green-600"><?=intval($row['total_container'])?></p>
            </div>
          </div>
        </div>

        <!-- Card Footer / Actions -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center flex-wrap gap-3">
          <div>
            <?php if($row['pdf_do']): ?>
              <a href="/shipping-app/uploads/do_files/<?=htmlspecialchars($row['pdf_do'])?>" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-lg transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
              </a>
            <?php else: ?>
              <span class="text-gray-400 text-sm">-</span>
            <?php endif;?>
          </div>
          <div class="flex gap-2">
            <a href="edit.php?id=<?=$row['id']?>" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit
            </a>
            <?php if(is_admin()): ?>
              <a href="delete.php?id=<?=$row['id']?>" onclick="return confirm('Hapus data ini?')" class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Empty State -->
    <?php if($result->num_rows === 0): ?>
      <div class="text-center py-16">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        <p class="text-gray-600 text-lg font-medium">Data tidak ditemukan</p>
        <p class="text-gray-500 text-sm mt-2">Coba ubah kata kunci pencarian atau ubah range tanggal</p>
      </div>
    <?php endif; ?>
  </main>

  <script>
    function toggleFilter() {
      const form = document.getElementById('filter-form');
      const icon = document.getElementById('filter-icon');
      form.classList.toggle('hidden');
      icon.style.transform = form.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    // Auto-expand filter if any filter is active
    <?php if ($has_filter): ?>
      document.getElementById('filter-form').classList.remove('hidden');
      document.getElementById('filter-icon').style.transform = 'rotate(180deg)';
    <?php endif; ?>
  </script>
</body>
</html>
