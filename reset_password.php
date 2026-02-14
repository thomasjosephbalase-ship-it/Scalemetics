<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

// Get token from URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

// If no token, redirect to forgot password
if (empty($token)) {
    header('Location: forgot_password.php');
    exit;
}

// Initialize variables
$valid = false;
$email = '';
$error = '';

// Verify token first
try {
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expiry > ?");
    $stmt->execute([$token, time()]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $valid = true;
        $email = $user['email'];
    }
} catch (PDOException $e) {
    error_log("Token verification error: " . $e->getMessage());
    $error = 'Database error occurred';
}

// Handle POST request for password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    
    // Validate
    if (empty($password) || empty($confirm)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    // Password strength validation
    $errors = [];
    if (strlen($password) < 8) $errors[] = 'at least 8 characters';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'one uppercase letter';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'one number';
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) $errors[] = 'one special character';
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain: ' . implode(', ', $errors)]);
        exit;
    }
    
    try {
        // Find user with this token
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_token_expiry > ?");
        $stmt->execute([$token, time()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired reset link']);
            exit;
        }
        
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
       $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");

        $updateStmt->execute([$hashed_password, $user['id']]);
        
        echo json_encode(['success' => true, 'message' => '✅ Password updated successfully! You can now login with your new password.']);
        exit;
        
    } catch (PDOException $e) {
        error_log("Reset password error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScaleMatics - Reset Password</title>
    
    <!-- FORCE LIGHT MODE -->
    <meta name="color-scheme" content="light only">
    <meta name="theme-color" content="#c5e0b4">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="../indext.css">
    
    <style>
        /* ===== EMERGENCY OVERRIDE - FORCE LIGHT MODE ===== */
        body {
            background: #e2f0d9 !important;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        
        .app-container {
            margin-top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .reset-password-container {
            background: #c5e0b4 !important;
            padding: 45px 40px;
            border-radius: 48px;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.4);
            position: relative;
            z-index: 100;
        }
        
        .reset-password-header h2 {
            color: #1a2e16 !important;
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 15px;
        }
        
        .reset-password-email {
            background: linear-gradient(145deg, rgba(53,91,46,0.15), rgba(53,91,46,0.05));
            padding: 16px 25px;
            border-radius: 60px;
            margin: 20px 0 30px;
            color: #1a2e16 !important;
            font-weight: 700;
            word-break: break-all;
            border: 1px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .reset-password-email i {
            color: #355b2e !important;
        }
        
        .password-reset-wrap {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .form-input {
            width: 100%;
            padding: 18px 25px;
            border-radius: 60px;
            border: none;
            font-size: 1rem;
            background: white !important;
            color: #1a2e16 !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            padding-right: 60px !important;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #355b2e !important;
            box-shadow: 0 0 0 4px rgba(53,91,46,0.15);
            transform: translateY(-2px);
        }
        
        .eye-reset-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #355b2e !important;
            font-size: 1.4rem;
            cursor: pointer !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.2s ease;
            z-index: 1000;
        }
        
        .eye-reset-btn:hover {
            background: rgba(53,91,46,0.15);
            transform: translateY(-50%) scale(1.1);
        }
        
        .reset-requirements {
            background: rgba(255,255,255,0.5) !important;
            padding: 20px 25px;
            border-radius: 30px;
            margin: 10px 0 20px;
            text-align: left;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .reset-req {
            font-size: 0.85rem;
            color: #1a2e16 !important;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn-reset {
            background: linear-gradient(145deg, #355b2e, #2a4523) !important;
            color: white !important;
            border: none;
            padding: 18px 35px;
            border-radius: 60px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            margin: 25px 0 20px;
            box-shadow: 0 10px 25px rgba(53,91,46,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-reset:hover {
            background: linear-gradient(145deg, #2d5026, #1e3a1a) !important;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(53,91,46,0.4);
        }
        
        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
            color: #355b2e !important;
            text-decoration: none;
            font-weight: 700;
            padding: 14px 28px;
            border-radius: 50px;
            background: rgba(255,255,255,0.4) !important;
            border: 1px solid rgba(255,255,255,0.5);
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(255,255,255,0.6) !important;
            transform: translateX(-5px);
        }
        
        .back-link i {
            font-size: 1.2rem;
        }
        
        .updating-loader {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #355b2e !important;
            font-weight: 700;
            margin: 20px 0;
            padding: 15px;
            background: rgba(255,255,255,0.5) !important;
            border-radius: 60px;
        }
        
        .hidden {
            display: none !important;
        }
        
        .fa-circle-check {
            color: #2c7a47 !important;
        }
        
        .fa-circle-xmark {
            color: #b34141 !important;
        }
        
        .success-message {
            background: rgba(44,122,71,0.15);
            color: #1f5420;
            padding: 15px;
            border-radius: 50px;
            text-align: center;
            font-weight: 600;
            border-left: 5px solid #2c7a47;
            margin-top: 20px;
        }
        
        .error-message {
            background: rgba(190,60,60,0.1);
            color: #b34141;
            padding: 15px;
            border-radius: 50px;
            text-align: center;
            font-weight: 600;
            border-left: 5px solid #b34141;
            margin-top: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fa-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="math-bg"></div>
        
        <div class="reset-password-container">
            <div class="reset-password-header">
                <h2><i class="fa-solid fa-key" style="color: #355b2e; margin-right: 10px;"></i> Reset Password</h2>
                
                <?php if ($valid): ?>
                    <div class="reset-password-email">
                        <i class="fa-regular fa-envelope"></i>
                        <?php echo htmlspecialchars($email); ?>
                    </div>
                <?php else: ?>
                    <div class="reset-password-email" style="color: #b34141 !important; background: rgba(190,60,60,0.1);">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <?php echo htmlspecialchars($error ?: 'Invalid or expired reset link'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($valid): ?>
                <form id="resetPasswordForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <!-- NEW PASSWORD WITH EYE TOGGLE -->
                    <div class="password-reset-wrap">
                        <input 
                            class="form-input" 
                            type="password" 
                            id="newPassword" 
                            name="password" 
                            placeholder="New password" 
                            required 
                            autocomplete="off"
                        />
                        <button class="eye-reset-btn" type="button" id="toggleNewPassword">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- CONFIRM PASSWORD WITH EYE TOGGLE -->
                    <div class="password-reset-wrap">
                        <input 
                            class="form-input" 
                            type="password" 
                            id="confirmPassword" 
                            name="confirm_password" 
                            placeholder="Confirm password" 
                            required 
                            autocomplete="off"
                        />
                        <button class="eye-reset-btn" type="button" id="toggleConfirmPassword">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- PASSWORD REQUIREMENTS -->
                    <div class="reset-requirements">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #1a2e16; font-weight: 700;">
                            <i class="fa-regular fa-shield" style="color: #355b2e;"></i>
                            Password must contain:
                        </div>
                        <div class="reset-req" id="resetReqLen">
                            <i class="fa-regular fa-circle-xmark"></i> At least 8 characters
                        </div>
                        <div class="reset-req" id="resetReqUp">
                            <i class="fa-regular fa-circle-xmark"></i> At least one uppercase letter
                        </div>
                        <div class="reset-req" id="resetReqNum">
                            <i class="fa-regular fa-circle-xmark"></i> At least one number
                        </div>
                        <div class="reset-req" id="resetReqSpec">
                            <i class="fa-regular fa-circle-xmark"></i> At least one special character
                        </div>
                        <div class="reset-req" id="resetReqMatch">
                            <i class="fa-regular fa-circle-xmark"></i> Passwords match
                        </div>
                    </div>
                    
                    <!-- UPDATING LOADER (HIDDEN BY DEFAULT) -->
                    <div id="updatingLoader" class="updating-loader hidden">
                        <i class="fa-regular fa-circle-notch fa-spin"></i>
                        <span>Updating your password...</span>
                    </div>
                    
                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="btn-reset" id="resetSubmitBtn">
                        <i class="fa-regular fa-lock-keyhole"></i>
                        Update Password
                    </button>
                    
                    <!-- MESSAGE CONTAINER -->
                    <div id="resetMessage" style="margin-top: 20px;"></div>
                </form>
            <?php else: ?>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="../index.html" class="btn-reset" style="display: inline-flex; width: auto; padding: 16px 40px; text-decoration: none;">
                        <i class="fa-regular fa-arrow-left"></i>
                        Return to Login
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- BACK TO SCALEMATICS - WITH ICONS -->
            <a href="../index.html" class="back-link">
                <i class="fa-regular fa-arrow-left"></i>
                Back to ScaleMatics
                <i class="fa-regular fa-calculator"></i>
                <i class="fa-regular fa-plus" style="font-size: 0.8rem;"></i>
            </a>
        </div>
    </div>
    
    <?php if ($valid): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Reset password page loaded - Token valid');
            
            // ===== 1. EYE TOGGLE FOR NEW PASSWORD =====
            const toggleNew = document.getElementById('toggleNewPassword');
            const newPass = document.getElementById('newPassword');
            
            if (toggleNew && newPass) {
                toggleNew.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = newPass.type === 'password' ? 'text' : 'password';
                    newPass.type = type;
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        if (type === 'text') {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                    console.log('👁️ Toggled new password');
                });
            }
            
            // ===== 2. EYE TOGGLE FOR CONFIRM PASSWORD =====
            const toggleConfirm = document.getElementById('toggleConfirmPassword');
            const confirmPass = document.getElementById('confirmPassword');
            
            if (toggleConfirm && confirmPass) {
                toggleConfirm.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = confirmPass.type === 'password' ? 'text' : 'password';
                    confirmPass.type = type;
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        if (type === 'text') {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                    console.log('👁️ Toggled confirm password');
                });
            }
            
            // ===== 3. PASSWORD VALIDATION =====
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const reqLen = document.getElementById('resetReqLen');
            const reqUp = document.getElementById('resetReqUp');
            const reqNum = document.getElementById('resetReqNum');
            const reqSpec = document.getElementById('resetReqSpec');
            const reqMatch = document.getElementById('resetReqMatch');
            const submitBtn = document.getElementById('resetSubmitBtn');
            const updatingLoader = document.getElementById('updatingLoader');
            const form = document.getElementById('resetPasswordForm');
            const resetMessage = document.getElementById('resetMessage');
            
            function validatePassword() {
                const val = newPassword ? newPassword.value : '';
                const confirmVal = confirmPassword ? confirmPassword.value : '';
                
                // Length
                if (reqLen) {
                    if (val.length >= 8) {
                        reqLen.innerHTML = '<i class="fa-regular fa-circle-check"></i> At least 8 characters';
                        reqLen.style.color = '#1f5420';
                    } else {
                        reqLen.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> At least 8 characters';
                        reqLen.style.color = '#1e3a1a';
                    }
                }
                
                // Uppercase
                if (reqUp) {
                    if (/[A-Z]/.test(val)) {
                        reqUp.innerHTML = '<i class="fa-regular fa-circle-check"></i> At least one uppercase letter';
                        reqUp.style.color = '#1f5420';
                    } else {
                        reqUp.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> At least one uppercase letter';
                        reqUp.style.color = '#1e3a1a';
                    }
                }
                
                // Number
                if (reqNum) {
                    if (/[0-9]/.test(val)) {
                        reqNum.innerHTML = '<i class="fa-regular fa-circle-check"></i> At least one number';
                        reqNum.style.color = '#1f5420';
                    } else {
                        reqNum.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> At least one number';
                        reqNum.style.color = '#1e3a1a';
                    }
                }
                
                // Special character
                if (reqSpec) {
                    if (/[!@#$%^&*(),.?":{}|<>]/.test(val)) {
                        reqSpec.innerHTML = '<i class="fa-regular fa-circle-check"></i> At least one special character';
                        reqSpec.style.color = '#1f5420';
                    } else {
                        reqSpec.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> At least one special character';
                        reqSpec.style.color = '#1e3a1a';
                    }
                }
                
                // Passwords match
                if (reqMatch) {
                    if (val && confirmVal && val === confirmVal) {
                        reqMatch.innerHTML = '<i class="fa-regular fa-circle-check"></i> Passwords match';
                        reqMatch.style.color = '#1f5420';
                    } else {
                        reqMatch.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> Passwords match';
                        reqMatch.style.color = '#1e3a1a';
                    }
                }
                
                // Enable/disable submit button
                const isValid = 
                    val.length >= 8 &&
                    /[A-Z]/.test(val) &&
                    /[0-9]/.test(val) &&
                    /[!@#$%^&*(),.?":{}|<>]/.test(val) &&
                    val === confirmVal &&
                    val.length > 0;
                
                if (submitBtn) {
                    submitBtn.disabled = !isValid;
                }
                
                return isValid;
            }
            
            if (newPassword) {
                newPassword.addEventListener('input', validatePassword);
                newPassword.addEventListener('keyup', validatePassword);
            }
            
            if (confirmPassword) {
                confirmPassword.addEventListener('input', validatePassword);
                confirmPassword.addEventListener('keyup', validatePassword);
            }
            
            // ===== 4. FORM SUBMIT - FIXED =====
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('📤 Form submitted');
                    
                    const password = newPassword.value;
                    const confirm = confirmPassword.value;
                    const token = form.querySelector('input[name="token"]').value;
                    
                    // Validate again
                    if (!validatePassword()) {
                        alert('Please meet all password requirements');
                        return;
                    }
                    
                    if (password !== confirm) {
                        alert('Passwords do not match');
                        return;
                    }
                    
                    // Disable button and show loader
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-regular fa-circle-notch fa-spin"></i> Updating...';
                    }
                    
                    if (updatingLoader) {
                        updatingLoader.classList.remove('hidden');
                    }
                    
                    if (resetMessage) {
                        resetMessage.innerHTML = '';
                    }
                    
                    // Create FormData
                    const formData = new FormData();
                    formData.append('password', password);
                    formData.append('confirm_password', confirm);
                    formData.append('token', token);
                    formData.append('ajax', '1'); // Flag to identify AJAX request
                    
                    // Send to the same file
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('📥 Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('📦 Response data:', data);
                        
                        if (updatingLoader) {
                            updatingLoader.classList.add('hidden');
                        }
                        
                        if (data.success) {
                            // Success
                            resetMessage.innerHTML = '<div class="success-message"><i class="fa-regular fa-circle-check"></i> ' + data.message + '</div>';
                            submitBtn.innerHTML = '<i class="fa-regular fa-check"></i> Password Updated!';
                            
                            // Redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = '../index.html';
                            }, 2000);
                        } else {
                            // Error
                            resetMessage.innerHTML = '<div class="error-message"><i class="fa-regular fa-circle-exclamation"></i> ' + data.message + '</div>';
                            
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fa-regular fa-lock-keyhole"></i> Update Password';
                        }
                    })
                    .catch(error => {
                        console.error('❌ Fetch error:', error);
                        
                        if (updatingLoader) {
                            updatingLoader.classList.add('hidden');
                        }
                        
                        resetMessage.innerHTML = '<div class="error-message"><i class="fa-regular fa-circle-exclamation"></i> Connection error. Please try again.</div>';
                        
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fa-regular fa-lock-keyhole"></i> Update Password';
                    });
                });
            }
            
            // Initial validation
            setTimeout(validatePassword, 100);
        });
    </script>
    <?php endif; ?>
</body>
</html>