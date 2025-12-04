<?php
require_once __DIR__.'/../includes/auth_check.php';
require_once __DIR__.'/../includes/koneksi.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT * FROM shipping_plan WHERE 1=1 ";
$params = [];
if ($search !== '') {
    $sql .= " AND (destination LIKE ? OR do_no LIKE ? OR vessel_name LIKE ?)";
    $like = "%".$search."%";
    $params = [$like,$like,$like];
}
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s',count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=shipping_plan_'.date('Ymd').'.csv');
$out = fopen('php://output', 'w');
fputcsv($out, ['Vanning Date','Destination','Country','Port','DO No','Vessel','ETD','CY Open','CY Closing','DEPO','CY','20','40','Total','PIC']);
while($row = $res->fetch_assoc()){
    fputcsv($out, [
        $row['vanning_date'],$row['destination'],$row['country'],$row['port'],$row['do_no'],$row['vessel_name'],
        $row['etd'],$row['cy_open'],$row['cy_closing'],$row['depo_name'],$row['cy_name'],$row['container_20'],$row['container_40hc'],$row['total_container'],$row['pic_name']
    ]);
}
fclose($out);
exit;
