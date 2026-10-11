<?php
if (isset($_POST["email"])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        file_put_contents("/home/user0/files/personal/email/subscriber-email-list.txt", $email . PHP_EOL, FILE_APPEND);
        $token = "6101935016:AAHVDi_guB7M7RP8w_e-oiWHJFZzdW3T_yY";
        $chat_id = "-1001932260528";
        $text = "New Subscriber: " . $email;
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($text);
        file_get_contents($url);
    }
}
header("Location: /blog");
?>