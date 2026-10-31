<?php
require_once __DIR__ . '/status_card.php';

if (!empty($_POST["website"])) {
    header("Location: /blog");
    exit;
}

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
