<?php
include 'components/connect.php';
session_start();

if (empty($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];
    if (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
        $error = 'Password must be at least 8 characters, include a number and an uppercase letter.';
    } elseif ($new_pass !== $confirm_pass) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $_SESSION['reset_email']]);
        $conn->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$_SESSION['reset_email']]);
        unset($_SESSION['reset_email']);
    header('Location: login.php?reset=success');
    exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<section class="form-container">
    <form method="post" class="login">
        <h3>Set New Password</h3>
        <?php if (!empty($error)) echo '<div class="message">' . $error . '</div>'; ?>
        <input type="password" name="new_pass" required placeholder="New password" class="box">
        <input type="password" name="confirm_pass" required placeholder="Confirm password" class="box">
        <button type="submit">Reset Password</button>
    </form>
</section>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
