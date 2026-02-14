<?php
// This file contains the HTML email template for password reset
// It's included by forgot_password.php and used to send beautiful emails

$email_html = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScaleMatics - Reset Your Password</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #e2f0d9;
            padding: 30px 20px;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 550px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 40px;
            padding: 45px 40px;
            box-shadow: 0 25px 50px rgba(26, 46, 22, 0.15);
            border: 1px solid rgba(197, 224, 180, 0.5);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo-img {
            width: 90px;
            height: 90px;
            margin-bottom: 10px;
        }
        
        .logo-text {
            font-size: 2rem;
            font-weight: 900;
            color: #1a2e16;
            letter-spacing: -0.02em;
            margin: 0;
        }
        
        .logo-sub {
            font-size: 0.85rem;
            font-weight: 600;
            color: #355b2e;
            font-style: italic;
            margin-top: 5px;
        }
        
        h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a2e16;
            margin: 30px 0 15px 0;
            letter-spacing: -0.02em;
        }
        
        .greeting {
            font-size: 1.2rem;
            font-weight: 700;
            color: #355b2e;
            margin-bottom: 20px;
        }
        
        .message {
            color: #1e3a1a;
            font-size: 1rem;
            margin-bottom: 25px;
        }
        
        .reset-button {
            display: inline-block;
            background: #355b2e;
            color: white !important;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 18px 45px;
            border-radius: 50px;
            text-decoration: none;
            margin: 30px 0 25px;
            box-shadow: 0 10px 20px rgba(53, 91, 46, 0.25);
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
        }
        
        .reset-button:hover {
            background: #2d5026;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(53, 91, 46, 0.35);
        }
        
        .link-fallback {
            background: #e2f0d9;
            padding: 20px;
            border-radius: 20px;
            margin: 25px 0;
            word-break: break-all;
            font-size: 0.85rem;
            color: #1a2e16;
            border: 1px solid #c5e0b4;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 5px solid #e6c22e;
            padding: 18px 20px;
            border-radius: 20px;
            margin: 25px 0;
            color: #1a2e16;
            font-size: 0.9rem;
        }
        
        .warning i {
            color: #b38f1a;
            margin-right: 8px;
        }
        
        .expiry {
            font-size: 0.85rem;
            color: #4a6a3a;
            margin: 15px 0;
            padding: 12px;
            background: rgba(53, 91, 46, 0.05);
            border-radius: 30px;
            display: inline-block;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #c5e0b4, transparent);
            margin: 35px 0 25px;
        }
        
        .footer {
            color: #6a7a64;
            font-size: 0.8rem;
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-links {
            margin-top: 20px;
        }
        
        .footer-links a {
            color: #355b2e;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .funny-note {
            font-style: italic;
            color: #2a4b22;
            margin-top: 25px;
            font-size: 0.9rem;
            background: rgba(197, 224, 180, 0.3);
            padding: 15px;
            border-radius: 30px;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 30px 25px;
                border-radius: 30px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .reset-button {
                padding: 15px 35px;
                font-size: 1rem;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Logo Section -->
        <div class="logo">
            <!-- Fallback logo if image doesn't load -->
            <div style="width: 90px; height: 90px; background: #c5e0b4; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 3rem; font-weight: 900; color: #355b2e;">S</span>
            </div>
            <div class="logo-text">ScaleMatics</div>
            <div class="logo-sub">For people who still count with their fingers.</div>
        </div>
        
        <!-- Main Content -->
        <h1>🔐 Reset your password</h1>
        
        <div class="greeting">
            Hey {username}! 👋
        </div>
        
        <div class="message">
            <p style="margin-bottom: 15px;">We received a request to reset your ScaleMatics password. Don't worry — it happens to the best of us! (Even Einstein had off days.)</p>
            
            <p style="margin-bottom: 10px;"><strong>Click the big green button below to create a new password:</strong></p>
        </div>
        
        <!-- Reset Button -->
        <div style="text-align: center;">
            <a href="{reset_link}" class="reset-button">
                🔑 Reset Password
            </a>
        </div>
        
        <!-- Expiry Notice -->
        <div style="text-align: center;">
            <span class="expiry">
                <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> 
                This link expires in <strong>1 hour</strong>
            </span>
        </div>
        
        <!-- Manual Link Fallback -->
        <div class="link-fallback">
            <div style="font-weight: 700; margin-bottom: 8px;">📎 Can't click the button?</div>
            <div style="font-size: 0.8rem;">Copy and paste this link into your browser:</div>
            <div style="margin-top: 10px; font-family: monospace; background: white; padding: 12px; border-radius: 12px; border: 1px solid #c5e0b4;">
                {reset_link}
            </div>
        </div>
        
        <!-- Warning Section -->
        <div class="warning">
            <i>⚠️</i> 
            <strong>Didn't request this?</strong> 
            If you didn't ask to reset your password, you can safely ignore this email. Your account is still secure.
        </div>
        
        <!-- Security Tips -->
        <div style="margin: 25px 0;">
            <h3 style="color: #1a2e16; font-size: 1.1rem; margin-bottom: 15px;">💪 Pro tips for your new password:</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #2c7a47; color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
                    At least 8 characters
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #2c7a47; color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
                    At least one uppercase letter
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #2c7a47; color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
                    At least one number
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #2c7a47; color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
                    At least one special character
                </li>
            </ul>
        </div>
        
        <!-- Funny Note -->
        <div class="funny-note">
            🧮 "For people who still count with their fingers." — We made resetting passwords almost as easy as 1, 2, 3.
        </div>
        
        <div class="divider"></div>
        
        <!-- Footer -->
        <div class="footer">
            <div style="font-weight: 700; color: #1a2e16; margin-bottom: 10px;">ScaleMatics Learning Platform</div>
            <div style="margin-bottom: 15px;">Making math less 'ugh' and more 'ohhh' since 2026.</div>
            
            <div class="footer-links">
                <a href="http://localhost/scalematics/index.html">Home</a> • 
                <a href="#">About</a> • 
                <a href="#">Privacy</a> • 
                <a href="#">Terms</a>
            </div>
            
            <div style="margin-top: 25px; color: #8a9a84;">
                © 2026 ScaleMatics. All rights reserved.
                <br>
                Built with 🧠 + ☕ in XAMPP.
            </div>
            
            <div style="margin-top: 20px; font-size: 0.7rem; color: #a0b89a;">
                This is an automated message. Please do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>
EOT;

// Function to send the email with dynamic data
function sendResetEmail($to, $username, $reset_link) {
    global $email_html;
    
    // Replace placeholders with actual values
    $html = str_replace('{username}', htmlspecialchars($username), $email_html);
    $html = str_replace('{reset_link}', $reset_link, $html);
    
    $subject = "🔐 ScaleMatics - Reset Your Password";
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: ScaleMatics <noreply@scalematics.local>\r\n";
    $headers .= "Reply-To: support@scalematics.local\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send email
    return mail($to, $subject, $html, $headers);
}
// ✅ WALANG closing ?> dito - TAMA NA!