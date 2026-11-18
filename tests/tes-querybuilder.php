<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

function cek(string $nama, bool $lulus): void
{
    echo ($lulus ? 'PASS' : 'FAIL') . ' - ' . $nama . PHP_EOL;
}

$db = new Database();
$qb = new QueryBuilder($db->getConnection());

$qb->table('produk')->where('id_produk', '=', 'MS')->first();

try {
    $qb->get();
    cek('1. get() tanpa table() ditolak setelah query pertama', false);
} catch (ValidationException $e) {
    cek(
        '1. get() tanpa table() ditolak setelah query pertama',
        $e->getMessage() === 'Pilih tabel terlebih dahulu.'
    );
}
$qb->table('produk')->where('id_produk', '=', 'MS')->first();

$semuaProduk = $qb->table('produk')->get();
cek('2. Filter where tidak terbawa ke query berikutnya', count($semuaProduk) >= 3);
try {
    $qb->table('users');
    cek('3. Nama tabel di luar daftar ditolak', false);
} catch (ValidationException $e) {
    cek('3. Nama tabel di luar daftar ditolak', true);
}
try {
    $qb->table('produk')->select(['kolom_tidak_ada']);
    cek('4. Nama kolom di luar daftar ditolak', false);
} catch (ValidationException $e) {
    cek('4. Nama kolom di luar daftar ditolak', true);
}
try {
    $qb->table('produk')->where('harga', 'INVALID', 1000);
    cek('5. Operator where di luar daftar ditolak', false);
} catch (ValidationException $e) {
    cek('5. Operator where di luar daftar ditolak', true);
}
try {
    $qb->table('produk')->orderBy('harga', 'SIDEWAYS');
    cek('6. Arah orderBy di luar daftar ditolak', false);
} catch (ValidationException $e) {
    cek('6. Arah orderBy di luar daftar ditolak', true);
}
try {
    $qb->table('produk')->limit(-1);
    cek('7. Limit negatif ditolak', false);
} catch (ValidationException $e) {
    cek('7. Limit negatif ditolak', true);
}
$duaProduk = $qb->table('produk')->limit(2)->get();
cek('8. limit(2) mengembalikan dua baris', count($duaProduk) === 2);
$produkTidakAda = $qb->table('produk')->where('id_produk', '=', 'ZZZ')->first();
cek('9. first() mengembalikan null saat hasil kosong', $produkTidakAda === null);
$db->beginTransaction();

try {
    $qb->table('produk')->update(['harga' => 1]);
    cek('10. update tanpa where ditolak', false);
} catch (ValidationException $e) {
    cek(
        '10. update tanpa where ditolak',
        $e->getMessage() === 'Tambahkan WHERE sebelum mengubah data.'
    );
} finally {
    $db->rollBack();
}
$db->beginTransaction();

try {
    $qb->table('produk')->delete();
    cek('11. delete tanpa where ditolak', false);
} catch (ValidationException $e) {
    cek(
        '11. delete tanpa where ditolak',
        $e->getMessage() === 'Tambahkan WHERE sebelum menghapus data.'
    );
} finally {
    $db->rollBack();
}
try {
    $qb->table('stok')->forUpdate();
    cek('12. forUpdate di luar transaksi ditolak', false);
} catch (ValidationException $e) {
    cek(
        '12. forUpdate di luar transaksi ditolak',
        $e->getMessage() === 'forUpdate hanya boleh dipakai di dalam transaksi.'
    );
}
$jumlahSebelum = null;
$barisDiubah = 0;

$db->beginTransaction();

try {
    $stokSebelum = $qb->table('stok')->where('id_stok', '=', 'S001')->forUpdate()->first();

    if ($stokSebelum !== null) {
        $jumlahSebelum = (int) $stokSebelum['jumlah_stok'];
        $barisDiubah = $qb->table('stok')->where('id_stok', '=', 'S001')
            ->update(['jumlah_stok' => $jumlahSebelum + 1]);
    }
} finally {
    $db->rollBack();
}

$stokSesudah = $qb->table('stok')->where('id_stok', '=', 'S001')->first();

cek(
    '13. rollback membatalkan perubahan stok',
    $barisDiubah === 1
        && $stokSesudah !== null
        && (int) $stokSesudah['jumlah_stok'] === $jumlahSebelum
);
$db->beginTransaction();

try {
    $qb->table('produk')->where('id_produk', '=', 'MS')->delete();
    cek('14. Hapus produk yang masih dipakai menampilkan pesan ramah', false);
} catch (DatabaseException $e) {
    cek(
        '14. Hapus produk yang masih dipakai menampilkan pesan ramah',
        str_contains($e->getMessage(), 'Data masih dipakai oleh data lain')
    );
} finally {
    $db->rollBack();
}
$namaUji = 'UJI DUPLIKAT ' . bin2hex(random_bytes(4));
$db->beginTransaction();

try {
    $qb->table('pelanggan')->insert(['nama_pelanggan' => $namaUji]);

    try {
        $qb->table('pelanggan')->insert(['nama_pelanggan' => $namaUji]);
        cek('15. Insert duplikat menampilkan pesan ramah', false);
    } catch (DatabaseException $e) {
        cek(
            '15. Insert duplikat menampilkan pesan ramah',
            $e->getMessage() === 'Data tersebut sudah ada.'
        );
    }
} finally {
    $db->rollBack();
}
$daftarProduk = $qb->getDaftarProdukDenganStok();

cek(
    '16. Daftar produk memuat kolom jumlah_stok',
    $daftarProduk !== [] && array_key_exists('jumlah_stok', $daftarProduk[0])
);
$detailT002 = $qb->getRiwayatTransaksi('T002');

cek(
    '17. Riwayat transaksi T002 memuat dua detail',
    count($detailT002) === 2
        && array_column($detailT002, 'id_transaksi') === ['T002', 'T002']
);