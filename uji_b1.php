<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$obj = new class extends BaseEntity {
    public function isValid(): bool { return true; }
    public function toArray(): array { return ['a' => 1]; }
    public function tesBersih(string $s): string { return $this->bersihkanTeks($s); }
};

var_dump($obj instanceof Validatable);
var_dump($obj instanceof BaseEntity);
print_r($obj->toArray());
echo $obj->tesBersih("  Kopi   Susu  "), "\n";