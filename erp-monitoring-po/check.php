<?php
$file = 'app/Http/Controllers/DashboardController.php';
$content = file_get_contents($file);
$start = strpos($content, '$poRows = DB->table(\'purchase_orders\'');
if ($start === false) $start = strpos($content, '$poRows = DB::table');
$end = strpos($content, 'private function resolveDateRange');
echo "Start:$start End:$end\n";
echo "START:" . substr($content, $start, 80) . "\n";
echo "END:" . substr($content, $end-60, 60) . "\n";
