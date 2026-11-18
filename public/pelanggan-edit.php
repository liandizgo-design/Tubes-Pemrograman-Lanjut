<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/View/helpers.php';
require_once __DIR__ . '/../src/Repository/PelangganRepositoryInterface.php';
require_once __DIR__ . '/../src/Repository/PelangganRepository.php';

$judul = 'Edit Pelanggan';
$errors = [];
$namaLama = '';
$old = ['nama_pelanggan' => '', 'no_telepon' => '', 'alamat' => ''];

try {
    $db   = new Database();
    $repo = new PelangganRepository(new QueryBuilder($db->getConnection()));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $namaLama = trim((string) ($_POST['nama_lama'] ?? ''));

        foreach ($old as $kolom => $nilai) {
            $old[$kolom] = trim((string) ($_POST[$kolom] ?? ''));
        }

        try {
            $repo->ubah($namaLama, new Pelanggan(
                $old['nama_pelanggan'],
                $old['no_telepon'],
                $old['alamat']
            ));

            setFlash('ok', 'Data pelanggan berhasil diubah.');
            header('Location: pelanggan.php');
            exit;
        } catch (ValidationException | InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    } else {
        $namaLama = trim((string) ($_GET['nama'] ?? ''));
        $pelanggan = $repo->cariByNama($namaLama);

        if ($pelanggan === null) {
            setFlash('error', 'Pelanggan tidak ditemukan.');
            header('Location: pelanggan.php');
            exit;
        }

        $old = [
            'nama_pelanggan' => $pelanggan->getNamaPelanggan(),
            'no_telepon'     => (string) $pelanggan->getNoTelepon(),
            'alamat'         => (string) $pelanggan->getAlamat(),
        ];
    }
} catch (DatabaseException $e) {
    $errors[] = $e->getMessage();
}

require __DIR__ . '/../src/View/header.php';
?>

<h2>Edit Pelanggan</h2>

<?php foreach ($errors as $pesan): ?>
    <p class="alert alert-error"><?= h($pesan) ?></p>
<?php endforeach; ?>

<form method="post" action="pelanggan-edit.php" class="form">
    <input type="hidden" name="nama_lama" value="<?= h($namaLama) ?>">

    <label for="nama_pelanggan">Nama pelanggan</label>
    <input type="text" id="nama_pelanggan" name="nama_pelanggan"
           value="<?= h($old['nama_pelanggan']) ?>" maxlength="100" required>

    <label for="no_telepon">No. telepon</label>
    <input type="text" id="no_telepon" name="no_telepon"
           value="<?= h($old['no_telepon']) ?>" maxlength="20">

    <label for="alamat">Alamat</label>
    <input type="text" id="alamat" name="alamat"
           value="<?= h($old['alamat']) ?>" maxlength="255">

    <button type="submit">Simpan Perubahan</button>
    <a href="pelanggan.php">Batal</a>
</form>

<?php require __DIR__ . '/../src/View/footer.php'; ?>