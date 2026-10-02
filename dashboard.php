<?php

require 'config/database.php';
require 'includes/auth.php';

wajibLogin();

$role = $_SESSION['user']['role'] ?? '';

function hitung($conn, $sql, $types = '', ...$params)
{
    $stmt = $conn->prepare($sql);

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt->get_result()->fetch_row()[0];
}

require 'includes/header.php';

echo "<h2>Selamat datang, " . ($_SESSION['user']['username'] ?? '') . "</h2>";

if ($role === 'siswa') {
    $aktif = hitung(
        $conn,
        "SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND status IN ('diajukan','dipinjam')",
        "i",
        $_SESSION['user']['anggota_id']
    );
    echo "<p>Pengajuan/pinjaman aktif Anda: <b>$aktif</b> dari maksimal 3 buku.</p>";
} else {
    $buku = hitung($conn, "SELECT COUNT(*) FROM buku WHERE deleted_at IS NULL");
    $agt = hitung($conn, "SELECT COUNT(*) FROM anggota");
    $tunggu = hitung($conn, "SELECT COUNT(*) FROM peminjaman WHERE status = 'diajukan'");
    $pinjam = hitung($conn, "SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'");
    echo "<ul>
<li>Total buku: <b>$buku</b></li>
<li>Total anggota: <b>$agt</b></li>
<li>Menunggu persetujuan: <b>$tunggu</b></li>
<li>Sedang dipinjam: <b>$pinjam</b></li>
</ul>";
}
require 'includes/footer.php';
