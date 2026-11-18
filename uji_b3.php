<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

function coba(string $label, callable $aksi): void
{
    try {
        $aksi();
        echo "[OK]      $label\n";
    } catch (ValidationException $e) {
        echo "[DITOLAK] $label -> {$e->getMessage()}\n";
    }
}

$p = new Produk('mks01', '2026-11-18', '  Minyak   Karo Sembiring ', 45000);
print_r($p->toArray());
var_dump($p instanceof BaseEntity, $p->isValid());

coba('harga 0 (boleh)',        fn() => new Produk('MTM01', '2026-11-18', 'Minyak Tambar Malem', 0));
coba('ID diawali angka',       fn() => new Produk('1ABC', '2026-11-18', 'X', 100));
coba('ID 11 karakter',         fn() => new Produk('ABCDEFGHIJK', '2026-11-18', 'X', 100));
coba('tanggal mustahil',       fn() => new Produk('MK01', '2026-02-30', 'X', 100));
coba('tanggal format salah',   fn() => new Produk('MK01', '18/11/2026', 'X', 100));
coba('nama kosong',            fn() => new Produk('MK01', '2026-11-18', '   ', 100));
coba('harga negatif',          fn() => new Produk('MK01', '2026-11-18', 'X', -1));
coba('harga kebesaran',        fn() => new Produk('MK01', '2026-11-18', 'X', 1.0E+12));

try {
    $p->setHarga(-5);
} catch (ValidationException $e) {
    echo "Setter ditolak, harga lama tetap: " . $p->getHarga() . "\n";
}