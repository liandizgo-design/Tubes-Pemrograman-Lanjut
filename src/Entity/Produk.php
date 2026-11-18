<?php
declare(strict_types=1);

class Produk extends BaseEntity
{
    public const MAKS_NAMA = 100;
    public const MAKS_HARGA = 9999999999.99;
    public const FORMAT_TANGGAL = 'Y-m-d';
    // Diawali huruf, huruf kapital/angka, total 2-10 karakter (sesuai VARCHAR(10))
    public const POLA_ID = '/^[A-Z][A-Z0-9]{1,9}$/';

    private string $idProduk;
    private string $tanggalInput;
    private string $namaProduk;
    private float $harga;

    public function __construct(
        string $idProduk,
        string $tanggalInput,
        string $namaProduk,
        float $harga
    ) {
        $this->setIdProduk($idProduk);
        $this->setTanggalInput($tanggalInput);
        $this->setNamaProduk($namaProduk);
        $this->setHarga($harga);
    }

    public function getIdProduk(): string
    {
        return $this->idProduk;
    }

    public function getTanggalInput(): string
    {
        return $this->tanggalInput;
    }

    public function getNamaProduk(): string
    {
        return $this->namaProduk;
    }

    public function getHarga(): float
    {
        return $this->harga;
    }

    public function setIdProduk(string $idProduk): void
    {
        $id = strtoupper(trim($idProduk));

        if (preg_match(self::POLA_ID, $id) !== 1) {
            throw new ValidationException(
                'ID produk harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }

        $this->idProduk = $id;
    }

    public function setTanggalInput(string $tanggalInput): void
    {
        $tanggal = trim($tanggalInput);
        $obj = DateTimeImmutable::createFromFormat('!' . self::FORMAT_TANGGAL, $tanggal);

        // Format dicocokkan ulang agar tanggal mustahil (mis. 2026-02-30) ditolak
        if ($obj === false || $obj->format(self::FORMAT_TANGGAL) !== $tanggal) {
            throw new ValidationException('Tanggal input harus berformat YYYY-MM-DD dan valid.');
        }

        $this->tanggalInput = $tanggal;
    }

    public function setNamaProduk(string $namaProduk): void
    {
        $nama = $this->bersihkanTeks($namaProduk);

        if ($nama === '') {
            throw new ValidationException('Nama produk tidak boleh kosong.');
        }
        if (mb_strlen($nama) > self::MAKS_NAMA) {
            throw new ValidationException(
                'Nama produk maksimal ' . self::MAKS_NAMA . ' karakter.'
            );
        }

        $this->namaProduk = $nama;
    }

    public function setHarga(float $harga): void
    {
        if (is_nan($harga) || is_infinite($harga)) {
            throw new ValidationException('Harga harus berupa angka yang valid.');
        }
        if ($harga < 0) {
            throw new ValidationException('Harga tidak boleh negatif.');
        }
        if ($harga > self::MAKS_HARGA) {
            throw new ValidationException('Harga melebihi batas yang diizinkan.');
        }

        $this->harga = round($harga, 2);
    }

    public function isValid(): bool
    {
        return preg_match(self::POLA_ID, $this->idProduk) === 1
            && $this->namaProduk !== ''
            && $this->harga >= 0;
    }

    public function toArray(): array
    {
        return [
            'id_produk'     => $this->idProduk,
            'tanggal_input' => $this->tanggalInput,
            'nama_produk'   => $this->namaProduk,
            'harga'         => $this->harga,
        ];
    }
}