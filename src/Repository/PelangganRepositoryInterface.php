<?php
declare(strict_types=1);

interface PelangganRepositoryInterface
{
    /** @return Pelanggan[] */
    public function semua(): array;

    public function cariByNama(string $nama): ?Pelanggan;

    public function simpan(Pelanggan $pelanggan): void;

    public function ubah(string $namaLama, Pelanggan $pelanggan): void;

    public function hapus(string $nama): void;
}