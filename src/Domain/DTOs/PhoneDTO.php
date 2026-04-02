<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

final readonly class PhoneDTO
{
    public function __construct(
        public ?int   $id,
        public string $phoneNumber,
        public string $label = 'mobile',
    ) {}
}
