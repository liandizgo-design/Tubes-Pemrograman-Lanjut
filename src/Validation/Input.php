<?php
declare(strict_types=1);

class Input
{
    /** @var array<int|string, string|array> */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $this->normalisasiArray($data);
    }

    public static function dariPost(): self
    {
        return new self($_POST);
    }

    public static function dariGet(): self
    {
        return new self($_GET);
    }

    public function ada(string $kunci): bool
    {
        return array_key_exists($kunci, $this->data);
    }

    public function get(string $kunci): ?string
    {
        $nilai = $this->data[$kunci] ?? null;

        return is_string($nilai) ? $nilai : null;
    }

    public function getArray(string $kunci): array
    {
        $nilai = $this->data[$kunci] ?? null;

        return is_array($nilai) ? $nilai : [];
    }

    public function semua(): array
    {
        return $this->data;
    }

    public function lama(string $kunci): string
    {
        return $this->get($kunci) ?? '';
    }

    public function lamaAman(string $kunci): string
    {
        return htmlspecialchars($this->lama($kunci), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function normalisasiArray(array $data): array
    {
        $hasil = [];

        foreach ($data as $kunci => $nilai) {
            if (is_array($nilai)) {
                $hasil[$kunci] = $this->normalisasiArray($nilai);
            } elseif (is_string($nilai)) {
                $hasil[$kunci] = $this->normalisasiTeks($nilai);
            } elseif (is_int($nilai) || is_float($nilai) || is_bool($nilai)) {
                $hasil[$kunci] = $this->normalisasiTeks((string) $nilai);
            } elseif ($nilai === null) {
                $hasil[$kunci] = '';
            }
            // object dan resource diabaikan
        }

        return $hasil;
    }

    private function normalisasiTeks(string $teks): string
    {
        $teks = mb_scrub($teks, 'UTF-8');
        $teks = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $teks);

        return trim($teks);
    }
}