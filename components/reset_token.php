<?php
function generate_reset_code() {
    return bin2hex(random_bytes(4)); // 8-char hex code
}

function store_reset_code($conn, $email, $code) {
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $hashed_code = password_hash($code, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("REPLACE INTO password_resets (email, code, expires_at, used) VALUES (?, ?, ?, 0)");
    $stmt->execute([$email, $hashed_code, $expires]);
}

function verify_code($conn, $email, $code) {
    $stmt = $conn->prepare("SELECT code, expires_at, used FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !$row['used'] && strtotime($row['expires_at']) > time()) {
        return password_verify($code, $row['code']);
    }
    return false;
}

function mark_code_used($conn, $email) {
    $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ?")->execute([$email]);
}
?>