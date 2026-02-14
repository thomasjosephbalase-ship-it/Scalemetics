<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

// Set header to JSON
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// If no JSON, try POST
if (!$data) {
    $data = $_POST;
}

// Validate email
if (empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$email = trim($data['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if PDO is connected
    if (!isset($pdo)) {
        throw new Exception('Database connection failed');
    }

    // Check if users table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() == 0) {
        // Create users table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                reset_token VARCHAR(255) NULL,
                reset_token_expiry INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($createTable);
    }

    // Check if email exists in database
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Generate secure random token
        $reset_token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hour validity
        
        // Check if reset_token column exists
        $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
        if ($columnCheck->rowCount() == 0) {
            // Add columns if they don't exist
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password");
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_token_expiry INT NULL AFTER reset_token");
        }
        
        // Store token in database
        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $result = $updateStmt->execute([$reset_token, $expiry, $user['id']]);
        
        if ($result) {
            // Create reset link - FIXED: Use absolute path
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = dirname($_SERVER['PHP_SELF']);
            $basePath = str_replace('\\', '/', $basePath);
            
            // Build the reset password URL
            $reset_link = $protocol . $host . $basePath . '/reset_password.php?token=' . urlencode($reset_token);
            
            // Return success with link
            echo json_encode([
                'success' => true,
                'message' => 'Password reset link generated successfully',
                'reset_link' => $reset_link,
                'token' => $reset_token,
                'user_id' => $user['id']
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate reset token']);
            exit;
        }
        
    } else {
        // Email not found - for security, return same message
        echo json_encode([
            'success' => false, 
            'message' => 'Email address not found in our records. Please check and try again.'
        ]);
        exit;
    }
    
} catch (PDOException $e) {
    // Log database error
    error_log("Forgot password database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again later.'
    ]);
    exit;
    
} catch (Exception $e) {
    // Log general error
    error_log("Forgot password general error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred. Please try again.'
    ]);
    exit;
}
?>