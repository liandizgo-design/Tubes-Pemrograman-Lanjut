<?php
declare(strict_types=1);

class Stok extends BaseEntity
{
    public const POLA_ID = '/^[A-Z][A-Z0-9]{1,9}$/';
    public const FORMAT_TANGGAL = 'Y-m-d';
    public const MAKS_STOK = 2147483647; // batas tipe INT di MySQL

    private string $idStok;
    private string $idProduk;
    private int $jumlahStok;
    private string $tanggalUpdate;

    public function __construct(
        string $idStok,
        string $idProduk,
        int $jumlahStok,
        string $tanggalUpdate
    ) {
        $this->setIdStok($idStok);
        $this->setIdProduk($idProduk);
        $this->setJumlahStok($jumlahStok);
        $this->setTanggalUpdate($tanggalUpdate);
    }

    public function getIdStok(): string
    {
        return $this->idStok;
    }

    public function getIdProduk(): string
    {
        return $this->idProduk;
    }

    public function getJumlahStok(): int
    {
        return $this->jumlahStok;
    }

    public function getTanggalUpdate(): string
    {
        return $this->tanggalUpdate;
    }

    public function setIdStok(string $idStok): void
    {
        $id = strtoupper(trim($idStok));

        if (preg_match(self::POLA_ID, $id) !== 1) {
            throw new ValidationException(
                'ID stok harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }

        $this->idStok = $id;
    }

    public function setIdProduk(string $idProduk): void
    {
        $id = strtoupper(trim($idProduk));

        // Aturan ID produk dipakai bersama dengan Produk agar tidak ganda
        if (preg_match(Produk::POLA_ID, $id) !== 1) {
            throw new ValidationException(
                'ID produk harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }

        $this->idProduk = $id;
    }

    public function setJumlahStok(int $jumlahStok): void
    {
        if ($jumlahStok < 0) {
            throw new ValidationException('Jumlah stok tidak boleh negatif.');
        }
        if ($jumlahStok > self::MAKS_STOK) {
            throw new ValidationException('Jumlah stok melebihi batas yang diizinkan.');
        }

        $this->jumlahStok = $jumlahStok;
    }

    public function setTanggalUpdate(string $tanggalUpdate): void
    {
        $tanggal = trim($tanggalUpdate);
        $obj = DateTimeImmutable::createFromFormat('!' . self::FORMAT_TANGGAL, $tanggal);

        if ($obj === false || $obj->format(self::FORMAT_TANGGAL) !== $tanggal) {
            throw new ValidationException('Tanggal update harus berformat YYYY-MM-DD dan valid.');
        }

        $this->tanggalUpdate = $tanggal;
    }

    /**
     * Mengurangi stok. Dipakai saat penjualan.
     * Semua pemeriksaan dilakukan SEBELUM stok berubah, jadi bila gagal
     * nilai lama tetap utuh.
     */
    public function kurangi(int $jumlah, ?string $tanggalUpdate = null): void
    {
        if ($jumlah <= 0) {
            throw new ValidationException('Jumlah pengurangan harus lebih dari 0.');
        }
        if ($jumlah > $this->jumlahStok) {
            throw new StokTidakCukupException(
                "Stok tidak cukup. Tersedia {$this->jumlahStok}, diminta {$jumlah}."
            );
        }

        $this->setTanggalUpdate($tanggalUpdate ?? date(self::FORMAT_TANGGAL));
        $this->jumlahStok -= $jumlah;
    }

    /** Menambah stok. Dipakai saat barang masuk. */
    public function tambah(int $jumlah, ?string $tanggalUpdate = null): void
    {
        if ($jumlah <= 0) {
            throw new ValidationException('Jumlah penambahan harus lebih dari 0.');
        }
        if ($jumlah > self::MAKS_STOK - $this->jumlahStok) {
            throw new ValidationException('Jumlah stok melebihi batas yang diizinkan.');
        }

        $this->setTanggalUpdate($tanggalUpdate ?? date(self::FORMAT_TANGGAL));
        $this->jumlahStok += $jumlah;
    }

    public function isValid(): bool
    {
        return preg_match(self::POLA_ID, $this->idStok) === 1
            && preg_match(Produk::POLA_ID, $this->idProduk) === 1
            && $this->jumlahStok >= 0;
    }

    public function toArray(): array
    {
        return [
            'id_stok'        => $this->idStok,
            'id_produk'      => $this->idProduk,
            'jumlah_stok'    => $this->jumlahStok,
            'tanggal_update' => $this->tanggalUpdate,
        ];
    }
}