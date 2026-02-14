<?php
session_start();
require_once 'db_connect.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

// Get JSON data from JavaScript fetch
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// If no JSON, try regular POST
if (!$data) {
    $data = $_POST;
}

// Validate required fields
if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password = $data['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check password strength (optional)
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

try {
    // Check if username or email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already taken']);
        exit;
    }
    
    // Hash password with bcrypt
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new user
    $insertStmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash) 
        VALUES (?, ?, ?)
    ");
    
    $insertStmt->execute([$username, $email, $password_hash]);
    
    // Get the new user ID
    $userId = $pdo->lastInsertId();
    
    // Set session for auto-login after registration
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful!',
        'user_id' => $userId,
        'username' => $username
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>