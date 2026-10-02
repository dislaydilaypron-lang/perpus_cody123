<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['admin']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin'] ?? '');
    $password = $_POST['Admin123'] ?? '';
    $roleBaru = $_POST['role'] ?? '';
    $anggota = ($_POST['anggota_id'] ?? '') !== '' ? (int)$_POST['anggota_id'] : null;
    if ($username === '' || strlen($password) < 6) {
        $error = "Username wajib diisi dan password minimal 6 karakter.";
    } elseif (!in_array($roleBaru, ['admin', 'petugas', 'siswa'], true)) {
        $error = "Role tidak valid.";
    } elseif ($roleBaru === 'siswa' && $anggota === null) {
        $error = "User siswa harus dihubungkan ke data anggota.";
    } else {
        if ($roleBaru !== 'siswa') $anggota = null;
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, anggota_id)
VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $hash, $roleBaru, $anggota);
            $stmt->execute();
            $_SESSION['pesan'] = "User berhasil ditambahkan.";
            header("Location: index.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            $error = ($ex->getCode() == 1062)
                ? "Username atau anggota tersebut sudah punya akun."
                : "Gagal menyimpan user.";
        }
    }
}
$users = $conn->query("SELECT u.id, u.username, u.role, a.nama
FROM users u LEFT JOIN anggota a ON a.id = u.anggota_id
ORDER BY u.role, u.username")->fetch_all(MYSQLI_ASSOC);
$anggotaList = $conn->query("SELECT id, nis, nama FROM anggota ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
require '../includes/header.php';
?>
<h2>Kelola User</h2>
<?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
<form method="post">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password (min 6)" required>
    <select name="role">
        <option>admin</option>
        <option>petugas</option>
        <option>siswa</option>
    </select>
    <select name="anggota_id">
        <option value="">-- anggota (khusus siswa) --</option>
        <?php foreach ($anggotaList as $a): ?>
            <option value="<?= $a['id'] ?>"><?= e($a['nis']) ?> - <?= e($a['nama']) ?></option>
        <?php endforeach; ?>
    </select>
    <button>Tambah User</button>
</form><br>
<table>
    <tr>
        <th>Username</th>
        <th>Role</th>
        <th>Anggota</th>
    </tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= e($u['nama'] ?? '-')
                ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require '../includes/footer.php';
