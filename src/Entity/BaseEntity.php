<?php
declare(strict_types=1);

abstract class BaseEntity implements Validatable
{
    abstract public function toArray(): array;

    public function getNamaEntity(): string
    {
        return static::class;
    }

    protected function bersihkanTeks(string $teks): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $teks));
    }
}
