<?php
// ===== SCALEMATICS AUTHENTICATION CHECK =====
// This file checks if user is logged in

session_start();

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        // If not logged in, redirect to landing page
        header('Location: ../index.html');
        exit;
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
    return null;
}

// Auto-check remember me token
if (!isLoggedIn() && isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
    require_once 'db_connect.php';
    
    $token = $_COOKIE['remember_token'];
    $user_id = $_COOKIE['remember_user'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email 
            FROM users 
            WHERE id = ? AND remember_token = ? AND remember_token_expiry > ?
        ");
        $stmt->execute([$user_id, $token, time()]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            session_regenerate_id(true);
        }
    } catch (PDOException $e) {
        error_log("Auto login error: " . $e->getMessage());
    }
}
?>