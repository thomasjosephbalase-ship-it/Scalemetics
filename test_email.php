<?php
$to = "test@example.com";
$subject = "ScaleMatics MailHog Test";
$message = "<h1>✅ WORKING!</h1><p>Nakuha mo na! Congrats!</p>";
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: scalematics@localhost.com\r\n";

if(mail($to, $subject, $message, $headers)) {
    echo "✅ Email sent! Check MailHog at http://localhost:8025";
} else {
    echo "❌ Failed. Check XAMPP logs.";
}
?>