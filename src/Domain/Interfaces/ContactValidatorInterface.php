<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface ContactValidatorInterface
{
    public function validate(array $data): array;
}
