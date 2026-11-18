<?php
declare(strict_types=1);

class PelangganRepository implements PelangganRepositoryInterface
{
    public function __construct(private QueryBuilder $qb)
    {
    }

    public function semua(): array
    {
        $baris = $this->qb->table('pelanggan')
            ->orderBy('nama_pelanggan', 'ASC')
            ->get();

        return array_map(fn (array $row): Pelanggan => $this->keEntity($row), $baris);
    }

    public function cariByNama(string $nama): ?Pelanggan
    {
        $row = $this->qb->table('pelanggan')
            ->where('nama_pelanggan', '=', $nama)
            ->first();

        return $row === null ? null : $this->keEntity($row);
    }

    public function simpan(Pelanggan $pelanggan): void
    {
        if ($this->cariByNama($pelanggan->getNamaPelanggan()) !== null) {
            throw new ValidationException('Nama pelanggan sudah terdaftar.');
        }

        $this->qb->table('pelanggan')->insert($pelanggan->toArray());
    }

    public function ubah(string $namaLama, Pelanggan $pelanggan): void
    {
        if ($this->cariByNama($namaLama) === null) {
            throw new ValidationException('Pelanggan yang akan diubah tidak ditemukan.');
        }

        $namaBaru = $pelanggan->getNamaPelanggan();

        if ($namaBaru !== $namaLama && $this->cariByNama($namaBaru) !== null) {
            throw new ValidationException('Nama pelanggan baru sudah dipakai.');
        }

        $this->qb->table('pelanggan')
            ->where('nama_pelanggan', '=', $namaLama)
            ->update($pelanggan->toArray());
    }

    public function hapus(string $nama): void
    {
        if ($this->cariByNama($nama) === null) {
            throw new ValidationException('Pelanggan tidak ditemukan.');
        }

        $dipakai = $this->qb->table('transaksi')
            ->select(['id_transaksi'])
            ->where('nama_pelanggan', '=', $nama)
            ->first();

        if ($dipakai !== null) {
            throw new ValidationException(
                'Pelanggan tidak bisa dihapus karena sudah memiliki transaksi.'
            );
        }

        $this->qb->table('pelanggan')
            ->where('nama_pelanggan', '=', $nama)
            ->delete();
    }

    private function keEntity(array $row): Pelanggan
    {
        return new Pelanggan(
            $row['nama_pelanggan'],
            $row['no_telepon'],
            $row['alamat']
        );
    }
}