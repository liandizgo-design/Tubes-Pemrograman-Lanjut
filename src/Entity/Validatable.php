<?php
declare(strict_types=1);

interface Validatable
{
    public function isValid(): bool;
}