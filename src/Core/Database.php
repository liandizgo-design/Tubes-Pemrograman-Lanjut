<?php
declare(strict_types=1);

class Database
{
    private PDO $connection;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = 'mysql:host=' . $config['host']
            . ';port=' . $config['port']
            . ';dbname=' . $config['dbname']
            . ';charset=' . $config['charset'];

        try {
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            error_log('Gagal membuka koneksi database: ' . $exception->getMessage());

            throw new DatabaseException(
                'Koneksi database belum tersedia. Silakan coba lagi.'
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
        public function beginTransaction(): void
    {
        try {
            $this->connection->beginTransaction();
        } catch (PDOException $exception) {
            error_log('Gagal memulai transaksi: ' . $exception->getMessage());
            throw new DatabaseException('Transaksi belum bisa dimulai. Silakan coba lagi.');
        }
    }
        public function commit(): void
    {
        try {
            $this->connection->commit();
        } catch (PDOException $exception) {
            error_log('Gagal commit: ' . $exception->getMessage());
            throw new DatabaseException('Transaksi belum bisa disimpan. Silakan coba lagi.');
        }
    }
        public function rollBack(): void
    {
        try {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
        } catch (PDOException $exception) {
            error_log('Gagal rollback: ' . $exception->getMessage());
            throw new DatabaseException('Transaksi belum bisa dibatalkan. Silakan coba lagi.');
        }
    }
        public function jalankanTransaksi(callable $aksi): mixed
    {
        $this->beginTransaction();

        try {
            $hasil = $aksi();
            $this->commit();

            return $hasil;
        } catch (Throwable $exception) {
            $this->rollBack();
            throw $exception;
        }
    }
}