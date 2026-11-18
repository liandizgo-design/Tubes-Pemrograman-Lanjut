<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/View/helpers.php';
require_once __DIR__ . '/../src/Repository/PelangganRepositoryInterface.php';
require_once __DIR__ . '/../src/Repository/PelangganRepository.php';

$judul = 'Pelanggan';
$errors = [];
$daftar = [];
$old = ['nama_pelanggan' => '', 'no_telepon' => '', 'alamat' => ''];

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

            setFlash('ok', 'Pelanggan berhasil ditambahkan.');
            header('Location: pelanggan.php');
            exit;
        } catch (ValidationException | InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    }

    $daftar = $repo->semua();
} catch (DatabaseException $e) {
    $errors[] = $e->getMessage();
}

$flash = ambilFlash();

require __DIR__ . '/../src/View/header.php';
?>

<h2>Data Pelanggan</h2>

<?php if ($flash !== null): ?>
    <p class="alert alert-<?= $flash['tipe'] === 'ok' ? 'ok' : 'error' ?>"><?= h($flash['pesan']) ?></p>
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
        <tr><th>Nama</th><th>No. Telepon</th><th>Alamat</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if ($daftar === []): ?>
        <tr><td colspan="4">Belum ada pelanggan.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $pelanggan): ?>
        <tr>
            <td><?= h($pelanggan->getNamaPelanggan()) ?></td>
            <td><?= h($pelanggan->getNoTelepon()) ?></td>
            <td><?= h($pelanggan->getAlamat()) ?></td>
            <td class="aksi">
                <a href="pelanggan-edit.php?nama=<?= urlencode($pelanggan->getNamaPelanggan()) ?>">Edit</a>
                <form method="post" action="pelanggan-hapus.php" class="inline"
                      onsubmit="return confirm('Hapus pelanggan ini?');">
                    <input type="hidden" name="nama_pelanggan"
                           value="<?= h($pelanggan->getNamaPelanggan()) ?>">
                    <button type="submit" class="btn-hapus">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../src/View/footer.php'; ?>