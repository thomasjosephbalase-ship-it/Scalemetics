<?php
session_start();

// Clear session
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Clear remember me cookies [citation:6]
setcookie('remember_token', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');

// Also clear tokens from database if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'db_connect.php';
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_token_expiry = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Logout token clear error: " . $e->getMessage());
    }
}

// Redirect to homepage
header('Location: ../index.html');
exit;
?>