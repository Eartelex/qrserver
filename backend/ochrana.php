<?php
// ochrana.php je ochrana proti botom a cudzím refererom

// 1. IP adresa používateľa (Cloudflare alebo normálne)
$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
if ($ip === '74.48.170.188') return;
$real_ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']); // ak je cez Cloudflare

// 2. HTTP hlavičky
$referer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
$host = isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'])) : '';

// 3. Známí zlí boti – loguj a zablokuj ihneď, aj keď chýba referer
$suspected_bots = '/ahrefsbot|semrushbot|mj12bot|dotbot|curl|wget|python|libwww|httpclient|masscan|scrapy|okhttp|java|nutch|phpcrawl|urlgrabber/i';
if (preg_match($suspected_bots, $user_agent)) {
$log_dir = __DIR__ . '/_logy';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
$log_file = $log_dir . '/ip_log_' . date('Ymd_H') . '.txt';
file_put_contents($log_file, $ip . " " . date("H:i") . " UA: " . $user_agent . "\n", FILE_APPEND);
header('HTTP/1.1 403 Forbidden');
exit;
}
// 5. Vyparsuj hostiteľa z referera (napr. google.com, obuv-eshop.eu, atď.)
$referer_host = parse_url($referer, PHP_URL_HOST);
$referer_host = strtolower(preg_replace('/^www\./', '', $referer_host));

// 6. Ak je referer rovnaký ako vlastný web → povoliť
if ($referer_host === $host) return;

// 7. Povolené vyhľadávače (referer obsahuje doménu vyhľadávača)
$allowed_referers_prefixes = array('google.', 'bing.', 'yahoo.', 'duckduckgo.', 'seznam.', 'baidu.');
foreach ($allowed_referers_prefixes as $prefix) {
if (strpos($referer_host, $prefix) !== false) return;
}

// 8. Povolení legitímni roboti podľa User-Agenta
$allowed_bots = '/googlebot|bingbot|slurp|baiduspider|seznambot|duckduckbot|yandexbot|applebot|facebot|twitterbot|linkedinbot/i';
if (preg_match($allowed_bots, $user_agent)) return;

// 9. Príprava adresára na logovanie
$log_dir = __DIR__ . '/_logy';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

// 10. Vymaž staré logy (staršie ako 2 hodiny)
$now = time();
$files = glob($log_dir . '/*.txt');
foreach ($files as $file) {
if (is_file($file) && ($now - filemtime($file)) > 7200) {
unlink($file);
}
}

// 11. Limity pre cudzie referery — 30 požiadaviek za 2 hodiny
$limit = 30;
$window = 7200; // 2 hodiny
$limit_file = $log_dir . '/limit-' . md5($ip) . '.txt';

// Načítaj existujúce časové značky
$times = [];
if (file_exists($limit_file)) {
$lines = file($limit_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
$ts = intval(trim($line));
if ($now - $ts < $window) $times[] = $ts;
}
}

// Ak limit prekročený → zablokuj
if (count($times) >= $limit) {
header('HTTP/1.1 429 Too Many Requests');
exit;
}

// Pridaj aktuálny čas a ulož späť  
$times[] = $now;
file_put_contents($limit_file, implode("\n", $times));

return;
?>