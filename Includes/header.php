<?php
// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL aplikasi
// Jika project berada di:
// http://localhost/perpustakaan/
if (!defined('BASE')) {
    define('BASE', '/perpustakaan');
}

// Fungsi untuk mengamankan output
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE . '/auth/login.php');
    exit;
}

// Ambil data user
$role = $_SESSION['user']['role'] ?? '';
$username = $_SESSION['user']['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Perpustakaan</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f5f5;
        }

        nav {
            background: #1e3a5f;
            padding: 14px 20px;
        }

        nav a {
            color: #fff;
            margin-right: 16px;
            text-decoration: none;
        }

        nav a:hover {
            text-decoration: underline;
        }

        nav .logout {
            float: right;
        }

        main {
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .ok {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .err {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .telat {
            background: #f8d7da;
        }
    </style>

</head>

<body>

    <nav>

        <a href="<?= e(BASE) ?>/dashboard.php">
            Dashboard
        </a>

        <a href="<?= e(BASE) ?>/buku/index.php">
            Katalog Buku
        </a>


        <?php if (in_array($role, ['admin', 'petugas'], true)): ?>

            <a href="<?= e(BASE) ?>/peminjaman/index.php">
                Peminjaman
            </a>

        <?php endif; ?>


        <?php if ($role === 'siswa'): ?>

            <a href="<?= e(BASE) ?>/peminjaman/riwayat.php">
                Riwayat Saya
            </a>

        <?php endif; ?>


        <?php if ($role === 'admin'): ?>

            <a href="<?= e(BASE) ?>/users/index.php">
                Kelola User
            </a>

        <?php endif; ?>


        <a
            href="<?= e(BASE) ?>/auth/logout.php"
            class="logout">
            Logout
            (<?= e($username) ?> - <?= e($role) ?>)
        </a>

    </nav>

    <main>

        <?php

        // Pesan sukses
        if (!empty($_SESSION['pesan'])) {

            echo '<p class="ok">'
                . e($_SESSION['pesan'])
                . '</p>';

            unset($_SESSION['pesan']);
        }


        // Pesan error
        if (!empty($_SESSION['error'])) {

            echo '<p class="err">'
                . e($_SESSION['error'])
                . '</p>';

            unset($_SESSION['error']);
        }
        ?>