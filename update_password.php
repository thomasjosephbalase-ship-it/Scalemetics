<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
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

// Validate required fields
if (!$data || empty($data['token']) || empty($data['password'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Token and password are required'
    ]);
    exit;
}

$token = trim($data['token']);
$new_password = $data['password'];

// Password strength validation
$errors = [];

if (strlen($new_password) < 8) {
    $errors[] = 'at least 8 characters';
}

if (!preg_match('/[A-Z]/', $new_password)) {
    $errors[] = 'at least one uppercase letter';
}

if (!preg_match('/[0-9]/', $new_password)) {
    $errors[] = 'at least one number';
}

if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
    $errors[] = 'at least one special character';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Password must contain: ' . implode(', ', $errors)
    ]);
    exit;
}

try {
    // Check if PDO is connected
    if (!isset($pdo)) {
        throw new Exception('Database connection failed');
    }

    // ===== DEBUG: CHECK IF USERS TABLE EXISTS =====
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'ERROR: users table does not exist! Run the SQL fix.']);
        exit;
    }

    // ===== DEBUG: GET TABLE STRUCTURE =====
    $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
    error_log("Users table columns: " . implode(', ', $columns));

    // ===== VERIFY TOKEN IS VALID AND NOT EXPIRED =====
    $stmt = $pdo->prepare("
        SELECT id, email, username 
        FROM users 
        WHERE reset_token = ? AND reset_token_expiry > ?
    ");
    $stmt->execute([$token, time()]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid or expired reset token. Please request a new password reset link.'
        ]);
        exit;
    }
    
    // ===== DETECT CORRECT PASSWORD COLUMN NAME =====
    $passwordColumn = 'password';
    $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    if ($columnCheck->rowCount() == 0) {
        // Try password_hash
        $columnCheck2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
        if ($columnCheck2->rowCount() > 0) {
            $passwordColumn = 'password_hash';
        } else {
            echo json_encode(['success' => false, 'message' => 'ERROR: No password column found! Run the SQL fix.']);
            exit;
        }
    }
    
    // ===== HASH THE NEW PASSWORD =====
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // ===== UPDATE PASSWORD AND CLEAR RESET TOKEN =====
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET $passwordColumn = ?, reset_token = NULL, reset_token_expiry = NULL 
        WHERE id = ?
    ");
    
    $result = $updateStmt->execute([$hashed_password, $user['id']]);
    
    if ($result) {
        error_log("Password updated successfully for user ID: " . $user['id'] . " - Email: " . $user['email']);
        
        echo json_encode([
            'success' => true, 
            'message' => '✅ Password updated successfully! You can now login with your new password.',
            'user_id' => $user['id']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update password. No rows affected.'
        ]);
    }
    
} catch (PDOException $e) {
    // ===== SHOW THE ACTUAL ERROR =====
    error_log("Update password database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'DATABASE ERROR: ' . $e->getMessage()  // THIS WILL SHOW THE REAL PROBLEM
    ]);
    
} catch (Exception $e) {
    error_log("Update password general error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'ERROR: ' . $e->getMessage()
    ]);
}
?>