
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If installed via Composer
require 'vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function send_reset_email($to, $token) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; // Your Gmail address
        $mail->Password   = $_ENV['SMTP_PASS']; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email settings
        $mail->setFrom($_ENV['SMTP_USER'], 'ApexLearn');
        $mail->addAddress($to);
        $mail->Subject = "Password Reset Request";
        $reset_link = "http://localhost/Edu_Platform/reset_password.php?token=" . urlencode($token);
        $mail->Body = "Click the following link to reset your password: " . $reset_link;

        $mail->send();
        echo '<div class="message">Password reset link has been sent to your email.</div>';
    } catch (Exception $e) {
        error_log('Mail sending failed: ' . $mail->ErrorInfo);
        echo '<div class="message">Unable to send email. Please contact support or try again later.</div>';
    }
}
?>