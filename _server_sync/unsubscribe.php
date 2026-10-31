<?php
require_once __DIR__ . '/status_card.php';

$email = isset($_GET['email']) ? strtolower(trim($_GET['email'])) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($email) || empty($token) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_status_card(
        "Invalid Link",
        "Invalid Request",
        "The unsubscribe parameters are missing or malformed.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$secretFile = "/home/user0/files/personal/email/newsletter_secret.key";
if (!file_exists($secretFile)) {
    render_status_card(
        "Error",
        "System Error",
        "Unable to verify request at this time.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$secret = trim(file_get_contents($secretFile));
$expectedToken = hash_hmac('sha256', $email, $secret);

if (!hash_equals($expectedToken, $token)) {
    render_status_card(
        "Invalid Link",
        "Invalid Unsubscribe Link",
        "This unsubscribe link could not be authenticated.",
        "warning",
        "Return to Blog",
        "/blog"
    );
}

$emailList = "/home/user0/files/personal/email/subscriber-email-list.txt";
if (file_exists($emailList)) {
    $subscribers = array_map('trim', file($emailList, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    $newSubscribers = array_filter($subscribers, function($item) use ($email) {
        return strtolower($item) !== $email;
    });

    if (count($newSubscribers) !== count($subscribers)) {
        $content = empty($newSubscribers) ? "" : implode(PHP_EOL, $newSubscribers) . PHP_EOL;
        file_put_contents($emailList, $content, LOCK_EX);

        $configFile = "/home/user0/files/personal/email/telegram.ini";
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile);
            if (!empty($config['token']) && !empty($config['chat_id'])) {
                $text = "Subscriber Unsubscribed: " . $email;
                $url = "https://api.telegram.org/bot" . $config['token'] . "/sendMessage?chat_id=" . $config['chat_id'] . "&text=" . urlencode($text);
                @file_get_contents($url);
            }
        }
    }
}

render_status_card(
    "Unsubscribed",
    "Successfully Unsubscribed",
    "The address <strong>" . htmlspecialchars($email) . "</strong> has been removed from our mailing list.<br><br>You will receive no further emails.",
    "success",
    "Return to Blog",
    "/blog"
);
?>
