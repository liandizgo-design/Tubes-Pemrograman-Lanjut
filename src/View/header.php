<?php
declare(strict_types=1);

/** @var string $judul */
$judul = $judul ?? 'UMKM DSM';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul) ?> | UMKM DSM</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <h1>UMKM DSM</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="produk.php">Produk</a>
            <a href="pelanggan.php">Pelanggan</a>
            <a href="penjualan.php">Penjualan</a>
        </nav>
    </header>
    <main class="container">