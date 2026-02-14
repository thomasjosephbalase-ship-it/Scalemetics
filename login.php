<?php
session_start();
require_once 'db_connect.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    $data = $_POST;
}

// Validate input
if (empty($data['username']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];
$remember = isset($data['remember']) ? true : false;

try {
    // Get user by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash 
        FROM users 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Handle "Remember Me" - 30 days persistent login [citation:6]
        if ($remember) {
            $remember_token = bin2hex(random_bytes(32));
            $expiry = time() + (60 * 60 * 24 * 30); // 30 days
            
            // Store token in database
            $tokenStmt = $pdo->prepare("
                UPDATE users 
                SET remember_token = ?, remember_token_expiry = ? 
                WHERE id = ?
            ");
            $tokenStmt->execute([$remember_token, $expiry, $user['id']]);
            
            // Set secure cookie
            setcookie('remember_token', $remember_token, $expiry, '/', '', false, true);
            setcookie('remember_user', $user['id'], $expiry, '/', '', false, true);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'username' => $user['username']
        ]);
        
    } else {
        // Generic error message for security [citation:3]
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}
?>