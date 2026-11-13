USE db_umkm_dsm;

INSERT IGNORE INTO produk (id_produk, tanggal_input, nama_produk, harga) VALUES
('MS',  '2026-10-06', 'Minyak Karo Sembiring', 65000),
('MTM', '2026-10-06', 'Minyak Tambar Malem',   75000),
('MK',  '2026-10-06', 'Minyak Kuda',           35500);

INSERT IGNORE INTO pelanggan (nama_pelanggan, no_telepon, alamat) VALUES
('Andi Pratama',   '081234567890', 'Balikpapan'),
('Rina Maharani',  '082298761543', 'Balikpapan'),
('Siti Rahmawati', '085712348965', 'Samarinda');

INSERT IGNORE INTO transaksi (id_transaksi, tanggal_transaksi, nama_pelanggan, total_transaksi, metode_penjualan) VALUES
('T001', '2026-10-07', 'Andi Pratama',   130000, 'WhatsApp'),
('T002', '2026-10-08', 'Siti Rahmawati', 110500, 'Shopee'),
('T003', '2026-10-09', 'Andi Pratama',    75000, 'TikTok Shop'),
('T004', '2026-10-10', 'Rina Maharani',   65000, 'Offline');

INSERT IGNORE INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES
('T001', 'MS',  2, 130000),
('T002', 'MTM', 1,  75000),
('T002', 'MK',  1,  35500),
('T003', 'MTM', 1,  75000),
('T004', 'MS',  1,  65000);

INSERT IGNORE INTO stok (id_stok, id_produk, jumlah_stok, tanggal_update) VALUES
('S001', 'MS',  15, '2026-10-10'),
('S002', 'MTM',  8, '2026-10-10'),
('S003', 'MK',   5, '2026-10-10');