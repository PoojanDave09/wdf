<?php
session_start();

define('SESSION_TIMEOUT', 900); // 15 minutes

function is_logged_in() {
    if (isset($_SESSION['username'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
