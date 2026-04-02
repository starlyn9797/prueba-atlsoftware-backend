<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

final readonly class ContactDTO
{
    public function __construct(
        public ?int    $id,
        public string  $firstName,
        public string  $lastName,
        public string  $email,
        public array   $phones    = [],
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
