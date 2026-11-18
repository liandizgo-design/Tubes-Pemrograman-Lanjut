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
}