
<?php
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

$role = $_SESSION['user']['role'] ?? '';

require '../config/database.php';
require '../includes/auth.php';
wajibLogin();
$cari = trim($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['hal'] ?? 1));
$perHal = 5;
$offset = ($halaman - 1) * $perHal;
$like = "%$cari%";
$stmt = $conn->prepare("SELECT COUNT(*) FROM buku
WHERE deleted_at IS NULL AND (judul LIKE ? OR kode LIKE ?)");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$totalHal = max(1, ceil($stmt->get_result()->fetch_row()[0] / $perHal));
$stmt = $conn->prepare("SELECT b.*, k.nama AS kategori
FROM buku b JOIN kategori k ON k.id = b.kategori_id
WHERE b.deleted_at IS NULL AND (b.judul LIKE ? OR b.kode LIKE ?)
ORDER BY b.judul LIMIT ? OFFSET ?");
$stmt->bind_param("ssii", $like, $like, $perHal, $offset);
$stmt->execute();
$dataBuku = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
require '../includes/header.php';
?>
<h2>Katalog Buku</h2>
<form method="get">
    <input name="cari" value="<?= e($cari) ?>" placeholder="Cari judul/kode">
    <button>Cari</button>
    <?php if (in_array($role, ['admin', 'petugas'])): ?>
        <a href="tambah.php">+ Tambah Buku</a>
    <?php endif; ?>
</form><br>
<table>
    <tr>
        <th>Kode</th>
        <th>Judul</th>
        <th>Pengarang</th>
        <th>Kategori</th>
        <th>Stok</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($dataBuku as $b): ?>
        <tr>
            <td><?= e($b['kode']) ?></td>
            <td><?= e($b['judul']) ?></td>
            <td><?= e($b['pengarang']) ?></td>
            <td><?= e($b['kategori']) ?></td>
            <td><?= (int)$b['stok'] ?></td>
            <td>
                <?php if ($role === 'siswa'): ?>
                    <form method="post" action="../peminjaman/ajukan.php" style="display:inline">
                        <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
                        <button <?= $b['stok'] < 1 ? 'disabled' : '' ?>>Ajukan Pinjam</button>
                    </form>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <form method="post" action="hapus.php" style="display:inline"
                        onsubmit="return confirm('Yakin hapus buku ini?')">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button>Hapus</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<p>Halaman:
    <?php for ($i = 1; $i <= $totalHal; $i++): ?>
        <a href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>"><?= $i == $halaman ? "<b>[$i]</b>" : $i ?></a>
    <?php endfor; ?>
</p>
<?php require '../includes/footer.php';
