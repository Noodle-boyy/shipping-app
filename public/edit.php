<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php'); exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM shipping_plan WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { header('Location: index.php'); exit; }
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Shipping Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <header class="bg-white border-b border-gray-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Shipping Plan</h1>
          <p class="text-sm text-gray-600 mt-1">Pengguna: <?=htmlspecialchars($user['full_name'] ?? $user['username'])?></p>
        </div>
        <div class="flex gap-2">
          <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
          <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow p-6 sm:p-8">
      <form method="post" action="save.php" enctype="multipart/form-data" class="space-y-8">
        <input type="hidden" name="_csrf" value="<?=htmlspecialchars(csrf_token())?>">
        <input type="hidden" name="id" value="<?=intval($row['id'])?>">
        <input type="hidden" name="existing_pdf" value="<?=htmlspecialchars($row['pdf_do'])?>">

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vanning Date</label>
            <input name="vanning_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['vanning_date'])?>">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">ETD</label>
            <input name="etd" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['etd'])?>">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CY Open</label>
            <input name="cy_open" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['cy_open'])?>">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CY Closing</label>
            <input name="cy_closing" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['cy_closing'])?>">
          </div>
        </div>

        <!-- Location -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
            <input name="destination" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['destination'])?>">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
            <div class="relative">
              <input id="country-input" name="country" type="text" autocomplete="off" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Type country..." value="<?=htmlspecialchars($row['country'])?>">
              <ul id="country-suggestions" class="absolute z-50 w-full bg-white border border-gray-200 mt-1 rounded shadow-sm hidden max-h-48 overflow-auto text-sm"></ul>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Port</label>
            <div class="relative">
              <input id="port-input" name="port" type="text" autocomplete="off" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Type port..." value="<?=htmlspecialchars($row['port'])?>">
              <input id="port-code" name="port_code" type="hidden" value="<?=htmlspecialchars($row['port_code'])?>">
              <ul id="port-suggestions" class="absolute z-50 w-full bg-white border border-gray-200 mt-1 rounded shadow-sm hidden max-h-48 overflow-auto text-sm"></ul>
            </div>
          </div>
        </div>

        <!-- DO -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">DO No</label>
          <input name="do_no" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['do_no'])?>">
        </div>

        <!-- Vessel & PIC -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vessel Name</label>
            <input name="vessel_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['vessel_name'])?>">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">PIC Name</label>
            <input name="pic_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=htmlspecialchars($row['pic_name'])?>">
          </div>
        </div>

        <!-- Containers -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Container 20' FT</label>
            <input name="container_20" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=intval($row['container_20'])?>">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Container 40' HC</label>
            <input name="container_40hc" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="<?=intval($row['container_40hc'])?>">
          </div>
        </div>

        <!-- File upload and actions (same as add) -->
        <div>
          <?php if (!empty($row['pdf_do'])): ?>
            <div class="mb-3">
              <a href="/shipping-app/uploads/do_files/<?=htmlspecialchars($row['pdf_do'])?>" target="_blank" class="text-blue-600 underline"><?=htmlspecialchars($row['pdf_do'])?></a>
              <label class="ml-3 text-sm"><input type="checkbox" name="remove_pdf" value="1"> Remove</label>
            </div>
          <?php endif; ?>
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center" onclick="document.getElementById('pdf_input').click()">
            <p class="text-gray-700">Klik untuk upload DO PDF baru</p>
          </div>
          <input id="pdf_input" name="pdf_do" type="file" accept=".pdf" class="hidden">
        </div>

        <div class="flex gap-3 pt-6 border-t border-gray-200">
          <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg">Update Plan</button>
          <a href="index.php" class="flex-1 text-center bg-gray-300 py-3 rounded-lg">Cancel</a>
        </div>
      </form>
    </div>
  </main>

  <script>
  // reuse the same JS from add.php
  function debounce(fn, wait=250){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), wait); }; }
  function escapeHtml(s){ return String(s).replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  function makeSuggestions(inputEl, listEl, endpoint, onSelect){
    inputEl.addEventListener('input', debounce(async function(){
      const q = this.value.trim();
      if (!q) { listEl.classList.add('hidden'); listEl.innerHTML=''; return; }
      try {
        const res = await fetch('/shipping-app/api/master.php?action='+endpoint+'&q='+encodeURIComponent(q));
        if (!res.ok) throw new Error('network');
        const data = await res.json();
        if (!data || data.length===0){ listEl.classList.add('hidden'); listEl.innerHTML=''; return; }
        listEl.innerHTML = data.map(item => {
          if (endpoint === 'ports') {
            return `<li data-code="${item.code}" data-name="${escapeHtml(item.name)}" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">${escapeHtml(item.name)} <span class="text-xs text-gray-500">(${item.code}${item.country? ' • '+escapeHtml(item.country):''})</span></li>`;
          } else {
            return `<li data-id="${item.id}" data-name="${escapeHtml(item.name)}" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">${escapeHtml(item.name)} ${item.iso_code? '<span class="text-xs text-gray-500">('+escapeHtml(item.iso_code)+')</span>':''}</li>`;
          }
        }).join('');
        listEl.classList.remove('hidden');
      } catch(e) { listEl.classList.add('hidden'); listEl.innerHTML=''; }
    }));

    listEl.addEventListener('click', function(e){
      const li = e.target.closest('li'); if(!li) return;
      if (endpoint === 'ports') {
        onSelect({name: li.getAttribute('data-name'), code: li.getAttribute('data-code')});
      } else {
        onSelect({name: li.getAttribute('data-name')});
      }
      listEl.classList.add('hidden');
    });

    document.addEventListener('click', (e)=>{
      if (!inputEl.contains(e.target) && !listEl.contains(e.target)) listEl.classList.add('hidden');
    });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    const countryInput = document.getElementById('country-input');
    const countryList = document.getElementById('country-suggestions');
    const portInput = document.getElementById('port-input');
    const portList = document.getElementById('port-suggestions');
    const portCodeEl = document.getElementById('port-code');

    if (countryInput) makeSuggestions(countryInput, countryList, 'countries', ({name})=>{ countryInput.value = name; });
    if (portInput) makeSuggestions(portInput, portList, 'ports', ({name, code})=>{ portInput.value = name; if (portCodeEl) portCodeEl.value = code; });
  });
  </script>
</body>
</html>