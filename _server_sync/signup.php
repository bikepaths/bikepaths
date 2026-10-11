<?php
if (isset($_POST["email"])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailList = "/home/user0/files/personal/email/subscriber-email-list.txt";
        file_put_contents($emailList, $email . PHP_EOL, FILE_APPEND);

        $configFile = "/home/user0/files/personal/email/telegram.ini";
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile);
            if (!empty($config['token']) && !empty($config['chat_id'])) {
                $text = "New Subscriber: " . $email;
                $url = "https://api.telegram.org/bot" . $config['token'] . "/sendMessage?chat_id=" . $config['chat_id'] . "&text=" . urlencode($text);
                @file_get_contents($url);
            }
        }
    }
}
header("Location: /blog");
?>