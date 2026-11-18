<?php
declare(strict_types=1);

class DetailTransaksi extends BaseEntity
{
    public const POLA_ID_TRANSAKSI = '/^[A-Z][A-Z0-9]{1,9}$/';
    public const MAKS_JUMLAH = 2147483647; // batas tipe INT di MySQL
    public const MAKS_SUBTOTAL = 9999999999.99;

    private string $idTransaksi;
    private string $idProduk;
    private int $jumlah;
    private float $subtotal;

    public function __construct(
        string $idTransaksi,
        string $idProduk,
        int $jumlah,
        float $subtotal
    ) {
        $this->setIdTransaksi($idTransaksi);
        $this->setIdProduk($idProduk);
        $this->setJumlah($jumlah);
        $this->setSubtotal($subtotal);
    }

    /**
     * Membuat detail dari object Produk; subtotal dihitung dari harga x jumlah.
     * (object sebagai parameter, Week 2)
     */
    public static function dariProduk(string $idTransaksi, Produk $produk, int $jumlah): self
    {
        return new self(
            $idTransaksi,
            $produk->getIdProduk(),
            $jumlah,
            $produk->getHarga() * $jumlah
        );
    }

    public function getIdTransaksi(): string
    {
        return $this->idTransaksi;
    }

    public function getIdProduk(): string
    {
        return $this->idProduk;
    }

    public function getJumlah(): int
    {
        return $this->jumlah;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function setIdTransaksi(string $idTransaksi): void
    {
        $id = strtoupper(trim($idTransaksi));

        if (preg_match(self::POLA_ID_TRANSAKSI, $id) !== 1) {
            throw new ValidationException(
                'ID transaksi harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }

        $this->idTransaksi = $id;
    }

    public function setIdProduk(string $idProduk): void
    {
        $id = strtoupper(trim($idProduk));

        if (preg_match(Produk::POLA_ID, $id) !== 1) {
            throw new ValidationException(
                'ID produk harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }

        $this->idProduk = $id;
    }

    public function setJumlah(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new ValidationException('Jumlah harus lebih dari 0.');
        }
        if ($jumlah > self::MAKS_JUMLAH) {
            throw new ValidationException('Jumlah melebihi batas yang diizinkan.');
        }

        $this->jumlah = $jumlah;
    }

    public function setSubtotal(float $subtotal): void
    {
        if (is_nan($subtotal) || is_infinite($subtotal)) {
            throw new ValidationException('Subtotal harus berupa angka yang valid.');
        }
        if ($subtotal < 0) {
            throw new ValidationException('Subtotal tidak boleh negatif.');
        }
        if ($subtotal > self::MAKS_SUBTOTAL) {
            throw new ValidationException('Subtotal melebihi batas yang diizinkan.');
        }

        $this->subtotal = round($subtotal, 2);
    }

    public function isValid(): bool
    {
        return preg_match(self::POLA_ID_TRANSAKSI, $this->idTransaksi) === 1
            && preg_match(Produk::POLA_ID, $this->idProduk) === 1
            && $this->jumlah > 0
            && $this->subtotal >= 0;
    }

    public function toArray(): array
    {
        return [
            'id_transaksi' => $this->idTransaksi,
            'id_produk'    => $this->idProduk,
            'jumlah'       => $this->jumlah,
            'subtotal'     => $this->subtotal,
        ];
    }
}