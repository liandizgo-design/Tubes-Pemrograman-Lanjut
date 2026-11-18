<?php
declare(strict_types=1);

interface ProdukRepositoryInterface
{
    public function semua(): array;

    public function cariById(string $id): ?Produk;

    public function simpan(Produk $produk, Stok $stok): void;

    public function ubah(Produk $produk): void;

    public function hapus(string $id): void;
}