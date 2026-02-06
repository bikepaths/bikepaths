<?php
$samesite = 'strict';
$lifetime = 31536000;
ini_set('session.gc_maxlifetime', $lifetime);
if(PHP_VERSION_ID < 70300) {
    session_set_cookie_params($lifetime);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'samesite' => $samesite
    ]);
}

session_start();

function login()
{
    if (session_status() == PHP_SESSION_NONE) return false;
    if (isset($_SESSION[site_url()]['user']) && !empty($_SESSION[site_url()]['user'])) {
        return true;
    } else {
        return false;
    }
}

if (rtrim($_SERVER['REQUEST_URI'], '/') != site_path() . '/login-mfa') {
    if (isset($_SESSION['mfa_pwd']) && isset($_SESSION['mfa_uid'])) {
        unset($_SESSION['mfa_pwd']);
        unset($_SESSION['mfa_uid']);
    }
}
