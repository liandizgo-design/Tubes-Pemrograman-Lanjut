CREATE DATABASE IF NOT EXISTS db_umkm_dsm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_umkm_dsm;

-- ==========================================================
-- 1. DDL (STRUKTUR TABEL & CONSTRAINT)
-- ==========================================================

CREATE TABLE produk (
    id_produk VARCHAR(10) PRIMARY KEY,
    tanggal_input DATE NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    CONSTRAINT chk_harga CHECK (harga >= 0)
);

CREATE TABLE pelanggan (
    nama_pelanggan VARCHAR(100) PRIMARY KEY,
    no_telepon VARCHAR(20),
    alamat VARCHAR(255)
);

CREATE TABLE transaksi (
    id_transaksi VARCHAR(10) PRIMARY KEY,
    tanggal_transaksi DATE NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    total_transaksi DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_penjualan VARCHAR(30) NOT NULL DEFAULT 'Offline',
    CONSTRAINT fk_transaksi_pelanggan FOREIGN KEY (nama_pelanggan)
        REFERENCES pelanggan (nama_pelanggan) ON UPDATE CASCADE,
    CONSTRAINT chk_total CHECK (total_transaksi >= 0)
);

CREATE TABLE detail_transaksi (
    id_transaksi VARCHAR(10) NOT NULL,
    id_produk VARCHAR(10) NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_transaksi, id_produk),
    CONSTRAINT fk_detail_transaksi FOREIGN KEY (id_transaksi)
        REFERENCES transaksi (id_transaksi),
    CONSTRAINT fk_detail_produk FOREIGN KEY (id_produk)
        REFERENCES produk (id_produk),
    CONSTRAINT chk_jumlah CHECK (jumlah > 0),
    CONSTRAINT chk_subtotal CHECK (subtotal >= 0)
);

CREATE TABLE stok (
    id_stok VARCHAR(10) PRIMARY KEY,
    id_produk VARCHAR(10) NOT NULL UNIQUE,
    jumlah_stok INT NOT NULL DEFAULT 0,
    tanggal_update DATE NOT NULL,
    CONSTRAINT fk_stok_produk FOREIGN KEY (id_produk)
        REFERENCES produk (id_produk),
    CONSTRAINT chk_stok CHECK (jumlah_stok >= 0)
);

-- ==========================================================
-- 2. DML (DATA DUMMY AWAL UMKM DSM)
-- ==========================================================

INSERT INTO produk (id_produk, tanggal_input, nama_produk, harga) VALUES
('MS',  '2026-10-01', 'Minyak Karo Sembiring', 35000.00),
('MTM', '2026-10-01', 'Minyak Tambar Malem',   40000.00),
('MK',  '2026-10-01', 'Minyak Kuda',           50000.00);

-- Catatan: MTM diset stok 8 (< 10) untuk menguji fitur alert stok menipis di Dashboard
INSERT INTO stok (id_stok, id_produk, jumlah_stok, tanggal_update) VALUES
('S001', 'MS',  25, '2026-11-10'),
('S002', 'MTM',  8, '2026-11-10'),
('S003', 'MK',  15, '2026-11-10');

INSERT INTO pelanggan (nama_pelanggan, no_telepon, alamat) VALUES
('Budi Santoso', '081234567890', 'Jl. Jamin Ginting No. 12, Medan'),
('Siti Aminah',  '082198765432', 'Jl. Setia Budi No. 45, Medan'),
('Andi Pratama', '085211223344', 'Jl. Iskandar Muda No. 8, Binjai');

INSERT INTO transaksi (id_transaksi, tanggal_transaksi, nama_pelanggan, total_transaksi, metode_penjualan) VALUES
('T001', '2026-11-01', 'Budi Santoso', 110000.00, 'Offline'),
('T002', '2026-11-05', 'Siti Aminah',  100000.00, 'WhatsApp'),
('T003', '2026-11-10', 'Andi Pratama', 115000.00, 'Shopee');

INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES
('T001', 'MS',  2,  70000.00),
('T001', 'MTM', 1,  40000.00),
('T002', 'MK',  2, 100000.00),
('T003', 'MS',  1,  35000.00),
('T003', 'MTM', 2,  80000.00);