<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['admin', 'petugas']);
$status = $_GET['status'] ?? 'diajukan';
if (!in_array($status, ['diajukan', 'dipinjam', 'dikembalikan', 'ditolak'], true)) $status = 'diajukan';
$stmt = $conn->prepare(
    "SELECT p.*, a.nama, a.kelas, b.judul,
(p.status = 'dipinjam' AND p.tgl_jatuh_tempo < CURDATE()) AS terlambat
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku b ON b.id = p.buku_id
WHERE p.status = ? ORDER BY p.id DESC"
);
$stmt->bind_param("s", $status);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
require '../includes/header.php';
?>
<h2>Data Peminjaman</h2>
<p>
    <?php foreach (['diajukan', 'dipinjam', 'dikembalikan', 'ditolak'] as $s): ?>
        <a href="?status=<?= $s ?>"><?= $s === $status ? "<b>[" . ucfirst($s) . "]</b>" : ucfirst($s) ?></a> |
    <?php endforeach; ?>
</p>
<table>
    <tr>
        <th>Anggota</th>
        <th>Buku</th>
        <th>Jatuh Tempo</th>
        <th>Denda</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($data as $d): ?>
        <tr class="<?= $d['terlambat'] ? 'telat' : '' ?>">
            <td><?= e($d['nama']) ?> (<?= e($d['kelas']) ?>)</td>
            <td><?= e($d['judul']) ?></td>
            <td><?= e($d['tgl_jatuh_tempo'] ?? '-') ?> <?= $d['terlambat'] ? '<b>(TERLAMBAT)</b>' : '' ?></td>
            <td>Rp<?= number_format($d['denda'], 0, ',', '.') ?></td>
            <td>
                <form method="post" action="proses.php" style="display:inline">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <?php if ($d['status'] === 'diajukan'): ?>
                        <button name="aksi" value="setujui">Setujui</button>
                        <button name="aksi" value="tolak">Tolak</button>
                    <?php elseif ($d['status'] === 'dipinjam'): ?>
                        <button name="aksi" value="kembali"
                            onclick="return confirm('Proses pengembalian?')">Kembalikan</button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require '../includes/footer.php';
