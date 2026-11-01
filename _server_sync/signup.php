<?php
require_once __DIR__ . '/status_card.php';

// Strict Anti-Bot Pipeline
$secretFile = '/home/user0/files/personal/email/newsletter_secret.key';
$server_secret = file_exists($secretFile) ? trim(file_get_contents($secretFile)) : 'c7e9f4a18b20d365_bikepaths_sec';

function reject_bot($reason) {
    // Record rejection in tracking log
    $dropLog = '/home/user0/files/signup_drops.log';
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logLine = sprintf("[%s] BOT-DROP: IP=%s Reason=%s Agent=%s\n", date('Y-m-d H:i:s'), $clientIp, $reason, $agent);
    @file_put_contents($dropLog, $logLine, FILE_APPEND | LOCK_EX);

    // Return 403 Forbidden to trigger immediate Fail2ban firewall ban
    http_response_code(403);
    die("Access Denied: Automated submission rejected.");
}

// 1. Mandatory Envelope Verification
if (!isset($_POST["website"]) || empty($_POST["render_time"]) || empty($_POST["form_token"]) || empty($_POST["interaction_signal"])) {
    reject_bot("Missing or unpopulated envelope parameters");
}

// 2. Honeypot Check (Must exist and be strictly empty)
if ($_POST["website"] !== "") {
    reject_bot("Honeypot field populated");
}

// 3. Cryptographic Signature Verification
$render_time = (int)$_POST["render_time"];
$expected_token = hash_hmac('sha256', (string)$render_time, $server_secret);
if (!hash_equals($expected_token, (string)$_POST["form_token"])) {
    reject_bot("Forged or tampered token");
}

// 4. 15-Second Minimum Time Gate
$elapsed = time() - $render_time;
if ($elapsed < 15) {
    reject_bot("Speed floor violation: " . $elapsed . "s (minimum 15s required)");
}
if ($elapsed > 7200) {
    reject_bot("Token expired: " . $elapsed . "s");
}

// 5. Passive Human Interaction Check
if ($_POST["interaction_signal"] !== "human_verified") {
    reject_bot("Passive interaction signal missing or disarmed");
}

// --- Legitimate Human Processing ---
if (isset($_POST["email"])) {
    $email = strtolower(trim($_POST["email"]));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailList = "/home/user0/files/personal/email/subscriber-email-list.txt";
        if (file_exists($emailList)) {
            $activeEmails = array_map('trim', file($emailList, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            if (in_array($email, $activeEmails, true)) {
                render_status_card(
                    "Already Subscribed",
                    "Already Subscribed",
                    "The address <strong>" . htmlspecialchars($email) . "</strong> is already registered to receive the weekly Saturday digest.",
                    "info",
                    "Return to Blog",
                    "/blog"
                );
            }
        }

        $token = bin2hex(random_bytes(32));
        $pendingFile = "/home/user0/files/personal/email/pending-subscribers.json";
        $pending = [];
        if (file_exists($pendingFile)) {
            $pending = json_decode(file_get_contents($pendingFile), true) ?: [];
        }

        foreach ($pending as $tok => $data) {
            if (isset($data['email']) && $data['email'] === $email) {
                unset($pending[$tok]);
            }
        }

        $pending[$token] = [
            "email" => $email,
            "created_at" => time()
        ];
        file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

        $verifyUrl = "https://bikepaths.org/blog/verify.php?token=" . $token;
        $subject = "Confirm your subscription to bikepaths.org";
        $message = "Please confirm your subscription to the bikepaths.org weekly email digest by clicking the link below:\n\n"
                 . $verifyUrl . "\n\n"
                 . "This link expires in 72 hours.\n\n"
                 . "If you did not request this subscription, no action is needed, and you will not receive any further emails.\n\n"
                 . "Thank you,\n"
                 . "https://bikepaths.org\n";

        $headers = "From: BikePaths <newsletter@bikepaths.org>\r\n"
                 . "Reply-To: BikePaths <newsletter@bikepaths.org>\r\n"
                 . "X-Mailer: PHP/" . phpversion() . "\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n";

        @mail($email, $subject, $message, $headers);

        $configFile = "/home/user0/files/personal/email/telegram.ini";
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile);
            if (!empty($config['token']) && !empty($config['chat_id'])) {
                $text = "Pending Subscriber (Awaiting Confirmation): " . $email;
                $url = "https://api.telegram.org/bot" . $config['token'] . "/sendMessage?chat_id=" . $config['chat_id'] . "&text=" . urlencode($text);
                @file_get_contents($url);
            }
        }

        render_status_card(
            "Check Your Inbox",
            "Check Your Inbox",
            "A confirmation email has been dispatched to <strong>" . htmlspecialchars($email) . "</strong>.<br><br>Please click the confirmation link in the email to activate your subscription.<br><br><strong>Note: The link expires in 72 hours.</strong>",
            "info",
            "Return to Blog",
            "/blog"
        );
    } else {
        render_status_card(
            "Invalid Email",
            "Invalid Email Address",
            "The email address provided is not valid. Please return to the blog and try again.",
            "warning",
            "Return to Blog",
            "/blog"
        );
    }
}

header("Location: /blog");
exit;
?>