<?php
require_once __DIR__ . '/status_card.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    render_status_card(
        "Link Invalid",
        "Link Expired or Invalid",
        "This confirmation link is invalid or has already been used.<br><br>If you still wish to receive updates, please enter your email again on our blog.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$pendingFile = "/home/user0/files/personal/email/pending-subscribers.json";
if (!file_exists($pendingFile)) {
    render_status_card(
        "Link Invalid",
        "Link Expired or Invalid",
        "This confirmation link is invalid or has already been used.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$pending = json_decode(file_get_contents($pendingFile), true) ?: [];

if (!isset($pending[$token])) {
    render_status_card(
        "Link Invalid",
        "Link Expired or Invalid",
        "This confirmation link is invalid or has already been used.<br><br>If you still wish to receive updates, please enter your email again on our blog.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$record = $pending[$token];
$email = isset($record['email']) ? strtolower(trim($record['email'])) : '';
$createdAt = isset($record['created_at']) ? (int)$record['created_at'] : 0;
$now = time();

// Expiration threshold: 72 hours (259200 seconds)
if (($now - $createdAt) > 259200) {
    unset($pending[$token]);
    file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));
    render_status_card(
        "Link Expired",
        "Confirmation Link Expired",
        "This confirmation link has expired (valid for 72 hours).<br><br>Please return to the blog to request a fresh confirmation link.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailList = "/home/user0/files/personal/email/subscriber-email-list.txt";
    $activeEmails = [];
    if (file_exists($emailList)) {
        $activeEmails = array_map('trim', file($emailList, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    if (!in_array($email, $activeEmails, true)) {
        file_put_contents($emailList, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    // Clean consumed token
    unset($pending[$token]);
    file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

    // Send Telegram Notification
    $configFile = "/home/user0/files/personal/email/telegram.ini";
    if (file_exists($configFile)) {
        $config = parse_ini_file($configFile);
        if (!empty($config['token']) && !empty($config['chat_id'])) {
            $text = "Confirmed Subscriber (Active): " . $email;
            $url = "https://api.telegram.org/bot" . $config['token'] . "/sendMessage?chat_id=" . $config['chat_id'] . "&text=" . urlencode($text);
            @file_get_contents($url);
        }
    }

    render_status_card(
        "Subscription Confirmed",
        "Subscription Confirmed!",
        "Thank you! Your email address <strong>" . htmlspecialchars($email) . "</strong> has been confirmed.<br><br>You are now subscribed to receive our weekly Saturday morning digest.",
        "success",
        "Continue to Blog",
        "/blog"
    );
}

render_status_card(
    "Link Invalid",
    "Link Expired or Invalid",
    "This confirmation link could not be validated.",
    "warning",
    "Return to Blog",
    "/blog"
);
?>
