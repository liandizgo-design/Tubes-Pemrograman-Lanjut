<?php
declare(strict_types=1);

$judul = 'Beranda';
require __DIR__ . '/../src/View/header.php';
?>

<h2>Selamat datang di Sistem UMKM DSM</h2>
<p>Penjualan Minyak Karo Sembiring, Minyak Tambar Malem, dan Minyak Kuda.</p>
<p>Berjalan di PHP <?= htmlspecialchars(PHP_VERSION) ?></p>

<?php require __DIR__ . '/../src/View/footer.php'; ?>`