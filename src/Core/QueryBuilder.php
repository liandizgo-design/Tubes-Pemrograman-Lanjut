<?php
declare(strict_types=1);

class QueryBuilder

{
    private const COLUMNS_BY_TABLE = [
        'produk' => ['id_produk', 'tanggal_input', 'nama_produk', 'harga'],
        'pelanggan' => ['nama_pelanggan', 'no_telepon', 'alamat'],
        'transaksi' => [
            'id_transaksi', 'tanggal_transaksi', 'nama_pelanggan',
            'total_transaksi', 'metode_penjualan',
        ],
        'detail_transaksi' => ['id_transaksi', 'id_produk', 'jumlah', 'subtotal'],
        'stok' => ['id_stok', 'id_produk', 'jumlah_stok', 'tanggal_update'],
    ];

    private PDO $pdo;
    private ?string $table = null;
    private array $columns = [];

    private array $conditions = [];
    private array $ordering = [];
    private ?int $rowLimit = null;
    private bool $lockForUpdate = false;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function table(string $table): self
    {
        if (!isset(self::COLUMNS_BY_TABLE[$table])) {
            throw new ValidationException('Nama tabel tidak valid.');
        }

        $this->reset();
        $this->table = $table;

        return $this;
    }

    public function select(array $columns): self
    {
        $allowedColumns = $this->getAllowedColumns();

        if ($columns === []) {
            throw new ValidationException('Pilih minimal satu kolom.');
        }

        foreach ($columns as $column) {
            if (!in_array($column, $allowedColumns, true)) {
                throw new ValidationException('Kolom tidak valid untuk tabel ini.');
            }
        }

        $this->columns = $columns;

        return $this;
    }
    public function where(string $column, string $operator, string|int|float $value): self
    {
        $operator = strtoupper(trim($operator));

        if (!in_array($column, $this->getAllowedColumns(), true)) {
            throw new ValidationException('Kolom filter tidak valid.');
        }

        if (!in_array($operator, ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE'], true)) {
            throw new ValidationException('Operator filter tidak valid.');
        }

        $this->conditions[] = [$column, $operator, $value];

        return $this;
    }
        public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper(trim($direction));

        if (!in_array($column, $this->getAllowedColumns(), true)) {
            throw new ValidationException('Kolom pengurutan tidak valid.');
        }

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new ValidationException('Arah pengurutan harus ASC atau DESC.');
        }

        $this->ordering[] = $column . ' ' . $direction;

        return $this;
    }
        public function limit(int $rows): self
    {
        if ($rows < 1) {
            throw new ValidationException('Batas baris minimal satu.');
        }

        $this->rowLimit = $rows;

        return $this;
    }
     public function forUpdate(): self
    {
        if (!$this->pdo->inTransaction()) {
            throw new ValidationException('forUpdate hanya boleh dipakai di dalam transaksi.');
        }

        $this->lockForUpdate = true; 

        return $this;
    }

    public function get(): array
    {
        return $this->executeSelect(false)->fetchAll();
    }

    public function first(): ?array
    {
        $row = $this->executeSelect(true)->fetch();

        return $row === false ? null : $row;
    }
        public function insert(array $data): int
    {
        try {
            $allowedColumns = $this->getAllowedColumns();

            if ($data === []) {
                throw new ValidationException('Data yang akan disimpan masih kosong.');
            }

            $columns = array_keys($data);

            foreach ($columns as $column) {
                if (!in_array($column, $allowedColumns, true)) {
                    throw new ValidationException('Kolom insert tidak valid.');
                }
            }

            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $sql = 'INSERT INTO ' . $this->table
                . ' (' . implode(', ', $columns) . ')'
                . ' VALUES (' . $placeholders . ')';

            $statement = $this->pdo->prepare($sql);
            $statement->execute(array_values($data));

            return $statement->rowCount();
        } catch (PDOException $exception) {
            throw $this->buatDatabaseException($exception, 'Data belum bisa disimpan. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }
        public function update(array $data): int
    {
        try {
            $allowedColumns = $this->getAllowedColumns();

            if ($this->conditions === []) {
                throw new ValidationException('Tambahkan WHERE sebelum mengubah data.');
            }

            if ($data === []) {
                throw new ValidationException('Data perubahan masih kosong.');
            }

            $assignments = [];

            foreach (array_keys($data) as $column) {
                if (!in_array($column, $allowedColumns, true)) {
                    throw new ValidationException('Kolom update tidak valid.');
                }

                $assignments[] = $column . ' = ?';
            }

            $clauses = [];
            $params = array_values($data);

            foreach ($this->conditions as [$column, $operator, $value]) {
                $clauses[] = $column . ' ' . $operator . ' ?';
                $params[] = $value;
            }
     
            $sql = 'UPDATE ' . $this->table
                . ' SET ' . implode(', ', $assignments)
                . ' WHERE ' . implode(' AND ', $clauses);

            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $exception) {
            throw $this->buatDatabaseException($exception, 'Data belum bisa diubah. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }
        public function delete(): int
    {
        try {
            $this->getAllowedColumns();

            if ($this->conditions === []) {
                throw new ValidationException('Tambahkan WHERE sebelum menghapus data.');
            }

            $clauses = [];
            $params = [];

            foreach ($this->conditions as [$column, $operator, $value]) {
                $clauses[] = $column . ' ' . $operator . ' ?';
                $params[] = $value;
            }

            $sql = 'DELETE FROM ' . $this->table
                . ' WHERE ' . implode(' AND ', $clauses);

            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement->rowCount();
        } catch (PDOException $exception) {
            error_log('Gagal menjalankan DELETE: ' . $exception->getMessage());
            throw $this->buatDatabaseException($exception, 'Data belum bisa dihapus. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }
        public function getProdukDenganStok(string $idProduk): ?array
    {
        try {
            $sql = 'SELECT p.id_produk, p.nama_produk, s.jumlah_stok'
                . ' FROM produk AS p'
                . ' LEFT JOIN stok AS s ON s.id_produk = p.id_produk'
                . ' WHERE p.id_produk = ?';

            $statement = $this->pdo->prepare($sql);
            $statement->execute([$idProduk]);
            $row = $statement->fetch();

            return $row === false ? null : $row;
        } catch (PDOException $exception) {
            error_log('Gagal membaca stok produk: ' . $exception->getMessage());
            throw new DatabaseException('Stok produk belum bisa dimuat. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }
            public function getDaftarProdukDenganStok(): array
    {
        try {
            $sql = 'SELECT p.id_produk, p.tanggal_input, p.nama_produk, p.harga,'
                . ' s.id_stok, s.jumlah_stok, s.tanggal_update'
                . ' FROM produk AS p'
                . ' LEFT JOIN stok AS s ON s.id_produk = p.id_produk'
                . ' ORDER BY p.id_produk ASC';

            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            error_log('Gagal membaca daftar produk: ' . $exception->getMessage());
            throw new DatabaseException('Daftar produk belum bisa dimuat. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }
    
        public function getRiwayatTransaksi(?string $idTransaksi = null): array
    {
        try {
            $sql = 'SELECT t.id_transaksi, t.tanggal_transaksi, t.nama_pelanggan,'
                . ' t.metode_penjualan, t.total_transaksi,'
                . ' d.id_produk, p.nama_produk, d.jumlah, d.subtotal'
                . ' FROM transaksi AS t'
                . ' INNER JOIN detail_transaksi AS d ON d.id_transaksi = t.id_transaksi'
                . ' INNER JOIN produk AS p ON p.id_produk = d.id_produk';

            $params = [];

            if ($idTransaksi !== null) {
                $sql .= ' WHERE t.id_transaksi = ?';
                $params[] = $idTransaksi;
            }

            $sql .= ' ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC, d.id_produk ASC';

            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            error_log('Gagal membaca riwayat transaksi: ' . $exception->getMessage());
            throw new DatabaseException('Riwayat transaksi belum bisa dimuat. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    }

    private function getAllowedColumns(): array
    {
        if ($this->table === null) {
            throw new ValidationException('Pilih tabel terlebih dahulu.');
        }

        return self::COLUMNS_BY_TABLE[$this->table];
    }
        private function buatDatabaseException(
        PDOException $exception,
        string $pesanUmum
    ): DatabaseException {
        error_log('Kesalahan database: ' . $exception->getMessage());

        $kode = (int) ($exception->errorInfo[1] ?? 0);

        return match ($kode) {
            1451 => new DatabaseException('Data masih dipakai oleh data lain, jadi tidak bisa diubah atau dihapus.'),
            1452 => new DatabaseException('Data rujukan tidak ditemukan. Periksa kembali pilihan Anda.'),
            1062 => new DatabaseException('Data tersebut sudah ada.'),
            default => new DatabaseException($pesanUmum),
        };
    }

    private function reset(): void
    {
        $this->table = null;
        $this->columns = [];
        $this->conditions = [];
        $this->ordering = [];
        $this->rowLimit = null;
        $this->lockForUpdate = false;
    }

        private function executeSelect(bool $firstOnly): PDOStatement
    {
        try {
            $allowedColumns = $this->getAllowedColumns();
            $columns = $this->columns ?: $allowedColumns;
            $sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $this->table;
            $params = [];

            if ($this->conditions !== []) {
                $clauses = [];

                foreach ($this->conditions as [$column, $operator, $value]) {
                    $clauses[] = $column . ' ' . $operator . ' ?';
                    $params[] = $value;
                }

                $sql .= ' WHERE ' . implode(' AND ', $clauses);
            }

            if ($this->ordering !== []) {
                $sql .= ' ORDER BY ' . implode(', ', $this->ordering);
            }

            $limit = $firstOnly ? 1 : $this->rowLimit;

            if ($limit !== null) {
                $sql .= ' LIMIT ' . $limit;
            }

            if ($this->lockForUpdate) {
                if (!$this->pdo->inTransaction()) {
                    throw new ValidationException('forUpdate hanya boleh dipakai di dalam transaksi.');
                }

                $sql .= ' FOR UPDATE';
            }
            
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement;
        } catch (PDOException $exception) {
            error_log('Gagal menjalankan SELECT: ' . $exception->getMessage());
            throw new DatabaseException('Data belum bisa dimuat. Silakan coba lagi.');
        } finally {
            $this->reset();
        }
    
    }
}