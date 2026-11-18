<?php
declare(strict_types=1);

class Transaksi extends BaseEntity
{
    public const METODE_OFFLINE  = 'Offline';
    public const METODE_WHATSAPP = 'WhatsApp';
    public const METODE_SHOPEE   = 'Shopee';
    public const METODE_TIKTOK   = 'TikTok Shop';

    public const DAFTAR_METODE = [
        self::METODE_OFFLINE,
        self::METODE_WHATSAPP,
        self::METODE_SHOPEE,
        self::METODE_TIKTOK,
    ];

    public const MAKS_NAMA = 100;
    public const MAKS_TOTAL = 9999999999.99;
    public const FORMAT_TANGGAL = 'Y-m-d';

    private string $idTransaksi;
    private string $tanggalTransaksi;
    private string $namaPelanggan;
    private float $totalTransaksi = 0.0;
    private string $metodePenjualan;

    /** @var array<string, DetailTransaksi> dikunci dengan id_produk */
    private array $detail = [];

    /**
     * Bila $detail diisi, totalTransaksi dihitung ulang dari subtotal detail
     * (nilai $totalTransaksi dari parameter ditimpa).
     * Parameter $totalTransaksi berguna saat Repository memuat transaksi dari database.
     */
    public function __construct(
        string $idTransaksi,
        string $tanggalTransaksi,
        string $namaPelanggan,
        string $metodePenjualan = self::METODE_OFFLINE,
        float $totalTransaksi = 0.0,
        array $detail = []
    ) {
        $this->setIdTransaksi($idTransaksi);
        $this->setTanggalTransaksi($tanggalTransaksi);
        $this->setNamaPelanggan($namaPelanggan);
        $this->setMetodePenjualan($metodePenjualan);
        $this->setTotalTransaksi($totalTransaksi);

        foreach ($detail as $item) {
            $this->tambahDetail($item);
        }
    }

    public function getIdTransaksi(): string
    {
        return $this->idTransaksi;
    }

    public function getTanggalTransaksi(): string
    {
        return $this->tanggalTransaksi;
    }

    public function getNamaPelanggan(): string
    {
        return $this->namaPelanggan;
    }

    public function getTotalTransaksi(): float
    {
        return $this->totalTransaksi;
    }

    public function getMetodePenjualan(): string
    {
        return $this->metodePenjualan;
    }

    /** @return DetailTransaksi[] */
    public function getDetail(): array
    {
        return array_values($this->detail);
    }

    public function setIdTransaksi(string $idTransaksi): void
    {
        $id = strtoupper(trim($idTransaksi));

        if (preg_match(DetailTransaksi::POLA_ID_TRANSAKSI, $id) !== 1) {
            throw new ValidationException(
                'ID transaksi harus 2-10 karakter: diawali huruf, lalu huruf kapital atau angka.'
            );
        }
        if ($this->detail !== []) {
            throw new ValidationException(
                'ID transaksi tidak dapat diubah setelah ada detail.'
            );
        }

        $this->idTransaksi = $id;
    }

    public function setTanggalTransaksi(string $tanggalTransaksi): void
    {
        $tanggal = trim($tanggalTransaksi);
        $obj = DateTimeImmutable::createFromFormat('!' . self::FORMAT_TANGGAL, $tanggal);

        if ($obj === false || $obj->format(self::FORMAT_TANGGAL) !== $tanggal) {
            throw new ValidationException(
                'Tanggal transaksi harus berformat YYYY-MM-DD dan valid.'
            );
        }

        $this->tanggalTransaksi = $tanggal;
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

    public function setMetodePenjualan(string $metodePenjualan): void
    {
        $input = $this->bersihkanTeks($metodePenjualan);

        // Dicocokkan tanpa membedakan huruf besar-kecil, disimpan dalam bentuk baku
        foreach (self::DAFTAR_METODE as $metode) {
            if (strcasecmp($input, $metode) === 0) {
                $this->metodePenjualan = $metode;
                return;
            }
        }

        throw new ValidationException(
            'Metode penjualan harus salah satu dari: '
            . implode(', ', self::DAFTAR_METODE) . '.'
        );
    }

    public function setTotalTransaksi(float $totalTransaksi): void
    {
        if (is_nan($totalTransaksi) || is_infinite($totalTransaksi)) {
            throw new ValidationException('Total transaksi harus berupa angka yang valid.');
        }
        if ($totalTransaksi < 0) {
            throw new ValidationException('Total transaksi tidak boleh negatif.');
        }
        if ($totalTransaksi > self::MAKS_TOTAL) {
            throw new ValidationException('Total transaksi melebihi batas yang diizinkan.');
        }
        if ($this->detail !== [] && abs($totalTransaksi - $this->hitungTotal()) > 0.005) {
            throw new ValidationException(
                'Total harus sama dengan jumlah subtotal detail.'
            );
        }

        $this->totalTransaksi = round($totalTransaksi, 2);
    }

    /**
     * Menambah satu baris detail. Semua pemeriksaan dilakukan sebelum
     * data berubah, jadi bila gagal, detail dan total lama tetap utuh.
     * Mengembalikan $this agar bisa dirangkai (method chaining, Week 2).
     */
    public function tambahDetail(DetailTransaksi $detail): self
    {
        if ($detail->getIdTransaksi() !== $this->idTransaksi) {
            throw new ValidationException(
                "Detail milik transaksi {$detail->getIdTransaksi()}, bukan {$this->idTransaksi}."
            );
        }
        if (isset($this->detail[$detail->getIdProduk()])) {
            throw new ValidationException(
                "Produk {$detail->getIdProduk()} sudah ada pada transaksi ini."
            );
        }

        $totalBaru = $this->hitungTotal() + $detail->getSubtotal();
        if ($totalBaru > self::MAKS_TOTAL) {
            throw new ValidationException('Total transaksi melebihi batas yang diizinkan.');
        }

        $this->detail[$detail->getIdProduk()] = $detail;
        $this->totalTransaksi = round($totalBaru, 2);

        return $this;
    }

    /** Menjumlahkan subtotal semua detail. */
    public function hitungTotal(): float
    {
        $total = 0.0;
        foreach ($this->detail as $item) {
            $total += $item->getSubtotal();
        }

        return round($total, 2);
    }

    public function isValid(): bool
    {
        return preg_match(DetailTransaksi::POLA_ID_TRANSAKSI, $this->idTransaksi) === 1
            && $this->namaPelanggan !== ''
            && in_array($this->metodePenjualan, self::DAFTAR_METODE, true)
            && ($this->detail === []
                || abs($this->totalTransaksi - $this->hitungTotal()) < 0.005);
    }

    /** Hanya kolom tabel transaksi; detail ada di tabel sendiri. */
    public function toArray(): array
    {
        return [
            'id_transaksi'      => $this->idTransaksi,
            'tanggal_transaksi' => $this->tanggalTransaksi,
            'nama_pelanggan'    => $this->namaPelanggan,
            'total_transaksi'   => $this->totalTransaksi,
            'metode_penjualan'  => $this->metodePenjualan,
        ];
    }

    /** Baris-baris untuk tabel detail_transaksi (dipakai Repository). */
    public function toArrayDetail(): array
    {
        return array_map(
            fn(DetailTransaksi $item): array => $item->toArray(),
            $this->getDetail()
        );
    }
}