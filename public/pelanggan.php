<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Repository/PelangganRepositoryInterface.php';
require_once __DIR__ . '/../src/Repository/PelangganRepository.php';

function h(?string $teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

$judul = 'Pelanggan';
$errors = [];
$daftar = [];
$old = ['nama_pelanggan' => '', 'no_telepon' => '', 'alamat' => ''];
$sukses = ($_GET['status'] ?? '') === 'tersimpan'
    ? 'Pelanggan berhasil ditambahkan.'
    : '';

try {
    $db   = new Database();
    $repo = new PelangganRepository(new QueryBuilder($db->getConnection()));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($old as $kolom => $nilai) {
            $old[$kolom] = trim((string) ($_POST[$kolom] ?? ''));
        }

        try {
            $repo->simpan(new Pelanggan(
                $old['nama_pelanggan'],
                $old['no_telepon'],
                $old['alamat']
            ));

            header('Location: pelanggan.php?status=tersimpan');
            exit;
        } catch (ValidationException | InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    }

    $daftar = $repo->semua();
} catch (DatabaseException $e) {
    $errors[] = $e->getMessage();
}

require __DIR__ . '/../src/View/header.php';
?>

<h2>Data Pelanggan</h2>

<?php if ($sukses !== ''): ?>
    <p class="alert alert-ok"><?= h($sukses) ?></p>
<?php endif; ?>

<?php foreach ($errors as $pesan): ?>
    <p class="alert alert-error"><?= h($pesan) ?></p>
<?php endforeach; ?>

<form method="post" action="pelanggan.php" class="form">
    <label for="nama_pelanggan">Nama pelanggan</label>
    <input type="text" id="nama_pelanggan" name="nama_pelanggan"
           value="<?= h($old['nama_pelanggan']) ?>" maxlength="100" required>

    <label for="no_telepon">No. telepon</label>
    <input type="text" id="no_telepon" name="no_telepon"
           value="<?= h($old['no_telepon']) ?>" maxlength="20">

    <label for="alamat">Alamat</label>
    <input type="text" id="alamat" name="alamat"
           value="<?= h($old['alamat']) ?>" maxlength="255">

    <button type="submit">Tambah Pelanggan</button>
</form>

<table class="tabel">
    <thead>
        <tr><th>Nama</th><th>No. Telepon</th><th>Alamat</th></tr>
    </thead>
    <tbody>
    <?php if ($daftar === []): ?>
        <tr><td colspan="3">Belum ada pelanggan.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $pelanggan): ?>
        <tr>
            <td><?= h($pelanggan->getNamaPelanggan()) ?></td>
            <td><?= h($pelanggan->getNoTelepon()) ?></td>
            <td><?= h($pelanggan->getAlamat()) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../src/View/footer.php'; ?>