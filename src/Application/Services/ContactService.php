<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\DTO\ContactDTO;
use App\Domain\DTO\PhoneDTO;
use App\Domain\Interfaces\ContactRepositoryInterface;
use App\Domain\Interfaces\ContactValidatorInterface;
use RuntimeException;

final class ContactService
{
    public function __construct(
        private readonly ContactRepositoryInterface  $repository,
        private readonly ContactValidatorInterface    $validator,
    ) {}

    public function getAll(): array
    {
        $rows = $this->repository->findAll();

        return array_map(fn(array $row) => $this->buildContactDTO($row), $rows);
    }

    public function getById(int $id): ContactDTO
    {
        $row = $this->repository->findById($id);

        if ($row === null) {
            throw new RuntimeException('Contacto no encontrado.', 404);
        }

        return $this->buildContactDTO($row);
    }

    public function create(array $data): ContactDTO
    {
        $validated = $this->validator->validate($data);

        if ($this->repository->existsWithEmail($validated['email'])) {
            throw new RuntimeException('Ya existe un contacto con ese email.', 409);
        }

        $contactData = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
        ];

        $newId = $this->repository->create($contactData, $validated['phones']);

        return $this->getById($newId);
    }

    public function update(int $id, array $data): ContactDTO
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new RuntimeException('Contacto no encontrado.', 404);
        }

        $validated = $this->validator->validate($data);

        if ($this->repository->existsWithEmail($validated['email'], $id)) {
            throw new RuntimeException('Ya existe un contacto con ese email.', 409);
        }

        $contactData = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
        ];

        $this->repository->update($id, $contactData, $validated['phones']);

        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        if (!$this->repository->delete($id)) {
            throw new RuntimeException('Contacto no encontrado.', 404);
        }
    }

    private function buildContactDTO(array $data): ContactDTO
    {
        $phones = array_map(
            fn(array $p) => new PhoneDTO(
                id:          isset($p['id']) ? (int) $p['id'] : null,
                phoneNumber: (string) ($p['phone_number'] ?? ''),
                label:       (string) ($p['label'] ?? 'mobile'),
            ),
            $data['phones'] ?? []
        );

        return new ContactDTO(
            id:        isset($data['id']) ? (int) $data['id'] : null,
            firstName: (string) ($data['first_name'] ?? ''),
            lastName:  (string) ($data['last_name'] ?? ''),
            email:     (string) ($data['email'] ?? ''),
            phones:    $phones,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
