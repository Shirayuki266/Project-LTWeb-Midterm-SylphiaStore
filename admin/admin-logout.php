<?php
session_start();

/* Xóa cookie session */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Xóa session */
session_unset();
session_destroy();

/* Redirect */
header("Location: login.php");
exit;
?>