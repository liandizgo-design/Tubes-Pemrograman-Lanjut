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

$p = new Pelanggan('  Budi   Santoso ', '0812-3456 7890', ' Jl. Mawar   No. 1 ');
print_r($p->toArray());
var_dump($p instanceof BaseEntity, $p instanceof Validatable, $p->isValid());

coba('tanpa telepon dan alamat', fn() => new Pelanggan('Ani'));
coba('nama kosong',              fn() => new Pelanggan('   '));
coba('nama 101 karakter',        fn() => new Pelanggan(str_repeat('a', 101)));
coba('telepon huruf',            fn() => new Pelanggan('Ani', 'abc'));
coba('telepon terlalu pendek',   fn() => new Pelanggan('Ani', '0812'));
coba('alamat 256 karakter',      fn() => new Pelanggan('Ani', null, str_repeat('a', 256)));
coba('telepon +62 valid',        fn() => new Pelanggan('Ani', '+6281234567890'));

try {
    $p->setNoTelepon('xyz');
} catch (ValidationException $e) {
    echo "Setter ditolak, nilai lama tetap: " . $p->getNoTelepon() . "\n";
}