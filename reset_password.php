<?php
include 'components/connect.php';
include 'components/reset_token.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$valid = false;
$email = '';

if($token) {
    $data = get_token_data($conn, $token);
    if($data) {
        $valid = true;
        $email = $data['email'];
    }
}

if(isset($_POST['submit']) && $valid) {
    $new_pass = sha1($_POST['new_pass']);
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
    if($new_pass == $cpass) {
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$new_pass, $email]);
        delete_token($conn, $token);
        $message[] = 'Password reset successful!';
    } else {
        $message[] = 'Passwords do not match!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<section class="form-container">
    <?php if($valid): ?>
    <form action="" method="post" class="login">
        <h3>Set New Password</h3>
        <input type="password" name="new_pass" placeholder="new password" maxlength="20" required class="box">
        <input type="password" name="cpass" placeholder="confirm password" maxlength="20" required class="box">
        <input type="submit" name="submit" value="Reset Password" class="btn">
    </form>
    <?php else: ?>
    <p>Invalid or expired token.</p>
    <?php endif; ?>
</section>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>