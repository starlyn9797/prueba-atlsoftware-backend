<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface ContactRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?array;

    public function create(array $contact, array $phones = []): int;

    public function delete(int $id): bool;

    public function existsWithEmail(string $email): bool;
}
