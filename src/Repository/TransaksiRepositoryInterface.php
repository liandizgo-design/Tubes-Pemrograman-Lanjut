<?php
declare(strict_types=1);

interface TransaksiRepositoryInterface
{
    public function semua(): array;

    public function cariById(string $id): ?Transaksi;

    public function simpan(Transaksi $transaksi): void;

    public function idBerikutnya(): string;
}