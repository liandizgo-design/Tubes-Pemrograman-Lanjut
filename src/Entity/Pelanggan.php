<?php
declare(strict_types=1);

class Pelanggan extends BaseEntity
{
    public const MAKS_NAMA = 100;
    public const MAKS_ALAMAT = 255;
    public const POLA_TELEPON = '/^\+?[0-9]{8,15}$/';

    private string $namaPelanggan;
    private ?string $noTelepon = null;
    private ?string $alamat = null;

    public function __construct(
        string $namaPelanggan,
        ?string $noTelepon = null,
        ?string $alamat = null
    ) {
        $this->setNamaPelanggan($namaPelanggan);
        $this->setNoTelepon($noTelepon);
        $this->setAlamat($alamat);
    }

    public function getNamaPelanggan(): string
    {
        return $this->namaPelanggan;
    }

    public function getNoTelepon(): ?string
    {
        return $this->noTelepon;
    }

    public function getAlamat(): ?string
    {
        return $this->alamat;
    }

    public function setNamaPelanggan(string $namaPelanggan): void
    {
        $nama = $this->bersihkanTeks($namaPelanggan);

        if ($nama === '') {
            throw new ValidationException('Nama pelanggan tidak boleh kosong.');
        }
        if (mb_strlen($nama) > self::MAKS_NAMA) {
            throw new ValidationException(
                'Nama pelanggan maksimal ' . self::MAKS_NAMA . ' karakter.'
            );
        }

        $this->namaPelanggan = $nama;
    }

    public function setNoTelepon(?string $noTelepon): void
    {
        if ($noTelepon === null || trim($noTelepon) === '') {
            $this->noTelepon = null;
            return;
        }

        $bersih = (string) preg_replace('/[\s\-]/', '', $noTelepon);

        if (preg_match(self::POLA_TELEPON, $bersih) !== 1) {
            throw new ValidationException(
                'No telepon harus 8-15 digit angka (boleh diawali +).'
            );
        }

        $this->noTelepon = $bersih;
    }

    public function setAlamat(?string $alamat): void
    {
        if ($alamat === null || trim($alamat) === '') {
            $this->alamat = null;
            return;
        }

        $bersih = $this->bersihkanTeks($alamat);

        if (mb_strlen($bersih) > self::MAKS_ALAMAT) {
            throw new ValidationException(
                'Alamat maksimal ' . self::MAKS_ALAMAT . ' karakter.'
            );
        }

        $this->alamat = $bersih;
    }

    public function isValid(): bool
    {
        return $this->namaPelanggan !== '';
    }

    public function toArray(): array
    {
        return [
            'nama_pelanggan' => $this->namaPelanggan,
            'no_telepon'     => $this->noTelepon,
            'alamat'         => $this->alamat,
        ];
    }
}