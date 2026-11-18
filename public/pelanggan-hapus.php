<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/View/helpers.php';
require_once __DIR__ . '/../src/Repository/PelangganRepositoryInterface.php';
require_once __DIR__ . '/../src/Repository/PelangganRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pelanggan.php');
    exit;
}

$nama = trim((string) ($_POST['nama_pelanggan'] ?? ''));

try {
    $db   = new Database();
    $repo = new PelangganRepository(new QueryBuilder($db->getConnection()));

    $repo->hapus($nama);
    setFlash('ok', 'Pelanggan berhasil dihapus.');
} catch (ValidationException | DatabaseException $e) {
    setFlash('error', $e->getMessage());
}

header('Location: pelanggan.php');
exit;