<?php
include 'components/connect.php';
include 'components/reset_token.php';
require 'send_mail.php';
session_start();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_POST['code'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $success = 'If your email exists, a reset code has been sent.';
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $code = bin2hex(random_bytes(4));
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $hashed_code = password_hash($code, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("REPLACE INTO password_resets (email, code, expires_at, used) VALUES (?, ?, ?, 0)");
        $stmt2->execute([$email, $hashed_code, $expires]);
        // Send code via email
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom($_ENV['SMTP_USER'], 'ApexLearn');
            $mail->addAddress($email);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "Your password reset code is: $code\nThis code expires in 15 minutes.";
            $mail->send();
        } catch (Exception $e) {
            error_log('Mail sending failed: ' . $mail->ErrorInfo);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['code'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code = $_POST['code'];
    $stmt = $conn->prepare("SELECT code, expires_at, used FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !$row['used'] && strtotime($row['expires_at']) > time()) {
        if (password_verify($code, $row['code'])) {
            $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ?")->execute([$email]);
            $_SESSION['reset_email'] = $email;
            header('Location: new_password.php');
            exit;
        } else {
            $error = 'Invalid code.';
        }
    } else {
        $error = 'Invalid or expired code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<section class="form-container">
    <form action="" method="post" class="login">
        <h3>Reset Your Password</h3>
        <p>Enter your email address to receive a password reset code.</p>
        <input type="email" name="email" placeholder="enter your email" maxlength="50" required class="box">
        <button type="submit">Send Reset Code</button>
    </form>
    <?php if (!empty($success)) echo '<div class="message">' . $success . '</div>'; ?>

    <?php if (!empty($success)) : ?>
    <form action="" method="post" class="login" style="margin-top:2rem;">
        <h3>Enter Reset Code</h3>
        <p>A code was sent to your email. Enter it below to continue.</p>
        <input type="email" name="email" placeholder="enter your email" maxlength="50" required class="box">
        <input type="text" name="code" placeholder="Enter code from email" maxlength="8" required class="box">
        <button type="submit">Verify Code</button>
    </form>
    <?php endif; ?>
    <?php if (!empty($error)) echo '<div class="message">' . $error . '</div>'; ?>
</section>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>