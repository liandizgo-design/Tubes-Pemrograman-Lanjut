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