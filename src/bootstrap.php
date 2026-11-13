<?php
declare(strict_types=1);

/*
 * Titik muat pusat. Setiap halaman di public/ cukup memuat file ini.
 * Aturan: satu class = satu file, nama file sama persis dengan nama class.
 * Buka komentar (//) hanya setelah file-nya dibuat.
 * Urutan penting: class induk/interface harus dimuat sebelum class turunannya.
 */

// ===== Exception (Eplin) =====
// require_once __DIR__ . '/Exception/DatabaseException.php';
// require_once __DIR__ . '/Exception/ValidationException.php';
// require_once __DIR__ . '/Exception/StokTidakCukupException.php';

// ===== Core (Eplin) =====
// require_once __DIR__ . '/Core/Database.php';
// require_once __DIR__ . '/Core/QueryBuilder.php';

// ===== Entity (Pay): interface dan abstract dulu =====
// require_once __DIR__ . '/Entity/Validatable.php';
// require_once __DIR__ . '/Entity/BaseEntity.php';
// require_once __DIR__ . '/Entity/Pelanggan.php';
// require_once __DIR__ . '/Entity/Produk.php';
// require_once __DIR__ . '/Entity/Stok.php';
// require_once __DIR__ . '/Entity/DetailTransaksi.php';
// require_once __DIR__ . '/Entity/Transaksi.php';

// ===== Validation (Pay) =====
// require_once __DIR__ . '/Validation/Input.php';
// require_once __DIR__ . '/Validation/Validator.php';

// ===== Repository (Niph) =====
// require_once __DIR__ . '/Repository/ProdukRepositoryInterface.php';
// require_once __DIR__ . '/Repository/PelangganRepositoryInterface.php';
// require_once __DIR__ . '/Repository/TransaksiRepositoryInterface.php';
// require_once __DIR__ . '/Repository/ProdukRepository.php';
// require_once __DIR__ . '/Repository/PelangganRepository.php';
// require_once __DIR__ . '/Repository/TransaksiRepository.php';