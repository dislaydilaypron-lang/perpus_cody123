<?php
require '../config/database.php';
require '../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id, username, password, role, anggota_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // cegah session fixation
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'anggota_id' => $user['anggota_id'],
        ];
        header("Location: " . BASE . "/dashboard.php");
        exit;
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Perpustakaan</title>
</head>

<body style="font-family:sans-serif;max-width:320px;margin:80px auto">
    <h2>Login Perpustakaan</h2>
    <?php if ($error): ?><p style="color:red"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <p><input name="username" placeholder="Username" required style="width:100%"></p>
        <p><input type="password" name="password" placeholder="Password" required style="width:100%"></p>
        <button>Masuk</button>
    </form>
</body>

</html>