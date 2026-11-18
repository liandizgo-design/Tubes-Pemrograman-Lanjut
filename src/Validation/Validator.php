<?php
declare(strict_types=1);

class Validator
{
    public const FORMAT_TANGGAL = 'Y-m-d';

    private const ATURAN_DIKENAL = [
        'required', 'integer', 'numeric', 'min', 'max',
        'between', 'in', 'regex', 'date',
    ];

    /** Aturan yang wajib punya parameter (setelah tanda ':'). */
    private const BUTUH_PARAMETER = ['min', 'max', 'between', 'in', 'regex'];

    /** Aturan dasar: bila gagal, aturan lain pada field yang sama tidak diperiksa lagi. */
    private const ATURAN_DASAR = ['required', 'integer', 'numeric'];

    /** @var array<string, string[]> pesan error per field */
    private array $errors = [];

    /**
     * Memeriksa semua field. State error direset di awal, jadi hasil
     * pemanggilan sebelumnya tidak bocor ke pemanggilan berikutnya.
     *
     * @param array<string, string[]> $rules  mis. ['nama' => ['required', 'min:3']]
     * @param array<string, string>   $labels nama tampilan field (opsional)
     */
    public function validate(Input $input, array $rules, array $labels = []): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $daftarAturan) {
            $field = (string) $field;
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            $this->periksaField($field, $input->get($field) ?? '', $daftarAturan, $label);
        }

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return string[] */
    public function errorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function periksaField(string $field, string $nilai, array $daftarAturan, string $label): void
    {
        $aturan = $this->urai($daftarAturan);
        $namaAturan = array_column($aturan, 0);

        // Field opsional yang dikosongkan tidak diperiksa lagi
        if ($nilai === '' && !in_array('required', $namaAturan, true)) {
            return;
        }

        $modeAngka = in_array('integer', $namaAturan, true) || in_array('numeric', $namaAturan, true);

        foreach ($aturan as [$nama, $parameter]) {
            $pesan = $this->periksaAturan($nama, $parameter, $nilai, $label, $modeAngka);

            if ($pesan === null) {
                continue;
            }

            $this->errors[$field][] = $pesan;

            if (in_array($nama, self::ATURAN_DASAR, true)) {
                break;
            }
        }
    }

    /**
     * Mengurai ['required', 'min:3'] menjadi [['required', null], ['min', '3']]
     * dan mengurutkan: required, lalu integer/numeric, lalu sisanya.
     * Kesalahan penulisan aturan adalah kesalahan programmer, bukan input pengguna,
     * jadi dilempar sebagai InvalidArgumentException.
     *
     * @return array<int, array{0: string, 1: ?string}>
     */
    private function urai(array $daftarAturan): array
    {
        $hasil = [];

        foreach ($daftarAturan as $aturan) {
            if (!is_string($aturan)) {
                throw new InvalidArgumentException('Setiap aturan harus berupa string.');
            }

            $bagian = explode(':', $aturan, 2);
            $nama = trim($bagian[0]);
            $parameter = $bagian[1] ?? null;

            if (!in_array($nama, self::ATURAN_DIKENAL, true)) {
                throw new InvalidArgumentException("Aturan '{$nama}' tidak dikenal.");
            }
            if ($parameter === null && in_array($nama, self::BUTUH_PARAMETER, true)) {
                throw new InvalidArgumentException("Aturan '{$nama}' membutuhkan parameter.");
            }
            if ($parameter !== null && $nama !== 'regex') {
                $parameter = trim($parameter);
            }

            $hasil[] = [$nama, $parameter];
        }

        usort($hasil, fn(array $a, array $b): int => $this->prioritas($a[0]) <=> $this->prioritas($b[0]));

        return $hasil;
    }

    private function prioritas(string $nama): int
    {
        return match ($nama) {
            'required'           => 0,
            'integer', 'numeric' => 1,
            default              => 2,
        };
    }

    private function periksaAturan(
        string $nama,
        ?string $parameter,
        string $nilai,
        string $label,
        bool $modeAngka
    ): ?string {
        switch ($nama) {
            case 'required':
                return $nilai === '' ? "{$label} wajib diisi." : null;

            case 'integer':
                return filter_var($nilai, FILTER_VALIDATE_INT) === false
                    ? "{$label} harus berupa bilangan bulat."
                    : null;

            case 'numeric':
                return $this->adalahAngka($nilai) ? null : "{$label} harus berupa angka.";

            case 'min':
                $batas = $this->angkaParameter($nama, (string) $parameter);
                if ($modeAngka) {
                    return (float) $nilai < $batas ? "{$label} minimal {$parameter}." : null;
                }
                return mb_strlen($nilai) < (int) $batas ? "{$label} minimal {$parameter} karakter." : null;

            case 'max':
                $batas = $this->angkaParameter($nama, (string) $parameter);
                if ($modeAngka) {
                    return (float) $nilai > $batas ? "{$label} maksimal {$parameter}." : null;
                }
                return mb_strlen($nilai) > (int) $batas ? "{$label} maksimal {$parameter} karakter." : null;

            case 'between':
                [$bawah, $atas] = $this->batasBetween((string) $parameter);
                $dalamRentang = $this->adalahAngka($nilai)
                    && (float) $nilai >= $bawah
                    && (float) $nilai <= $atas;

                return $dalamRentang
                    ? null
                    : "{$label} harus antara " . $this->tampil($bawah) . ' sampai ' . $this->tampil($atas) . '.';

            case 'in':
                $pilihan = array_map('trim', explode(',', (string) $parameter));
                return in_array($nilai, $pilihan, true)
                    ? null
                    : "{$label} harus salah satu dari: " . implode(', ', $pilihan) . '.';

            case 'regex':
                $hasil = preg_match((string) $parameter, $nilai);
                if ($hasil === false) {
                    throw new InvalidArgumentException("Pola regex tidak valid: {$parameter}");
                }
                return $hasil === 1 ? null : "Format {$label} tidak valid.";

            case 'date':
                $tanggal = DateTimeImmutable::createFromFormat('!' . self::FORMAT_TANGGAL, $nilai);
                $valid = $tanggal !== false && $tanggal->format(self::FORMAT_TANGGAL) === $nilai;

                return $valid ? null : "{$label} harus berupa tanggal valid berformat YYYY-MM-DD.";
        }

        return null;
    }

    private function adalahAngka(string $nilai): bool
    {
        return preg_match('/^-?\d+(\.\d+)?$/', $nilai) === 1;
    }

    private function angkaParameter(string $aturan, string $parameter): float
    {
        if (!$this->adalahAngka($parameter)) {
            throw new InvalidArgumentException("Parameter aturan '{$aturan}' harus berupa angka.");
        }

        return (float) $parameter;
    }

    /** @return array{0: float, 1: float} */
    private function batasBetween(string $parameter): array
    {
        $bagian = array_map('trim', explode(',', $parameter));

        if (count($bagian) !== 2
            || !$this->adalahAngka($bagian[0])
            || !$this->adalahAngka($bagian[1])
            || (float) $bagian[0] > (float) $bagian[1]
        ) {
            throw new InvalidArgumentException(
                "Aturan 'between' membutuhkan dua angka berurutan, mis. between:1,4."
            );
        }

        return [(float) $bagian[0], (float) $bagian[1]];
    }

    private function tampil(float $angka): string
    {
        return $angka == (int) $angka ? (string) (int) $angka : (string) $angka;
    }
}