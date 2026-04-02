<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\ContactService;
use App\Domain\DTOs\ContactDTO;
use App\Domain\DTOs\PhoneDTO;
use App\Presentation\Http\Responses\JsonResponse;
use RuntimeException;

final class ContactController
{
    public function __construct(
        private readonly ContactService $service
    ) {}

    public function index(): void
    {
        $contacts = $this->service->getAll();

        $data = array_map(
            fn(ContactDTO $dto) => $this->serializeContact($dto),
            $contacts
        );

        JsonResponse::success($data);
    }

    public function show(string $id): void
    {
        $contact = $this->service->getById((int) $id);
        JsonResponse::success($this->serializeContact($contact));
    }

    public function store(): void
    {
        $body = $this->getRequestBody();
        $contact = $this->service->create($body);
        JsonResponse::created($this->serializeContact($contact));
    }

    public function update(string $id): void
    {
        $body = $this->getRequestBody();
        $contact = $this->service->update((int) $id, $body);
        JsonResponse::success($this->serializeContact($contact));
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id);
        JsonResponse::success(['message' => 'Contacto eliminado exitosamente.']);
    }

    private function getRequestBody(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === '' || $raw === false) {
            throw new RuntimeException('El cuerpo de la solicitud está vacío.', 400);
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('JSON inválido en el cuerpo de la solicitud.', 400);
        }

        return $decoded;
    }

    private function serializeContact(ContactDTO $dto): array
    {
        $result = [
            'id'         => $dto->id,
            'first_name' => $dto->firstName,
            'last_name'  => $dto->lastName,
            'email'      => $dto->email,
            'phones'     => array_map(fn(PhoneDTO $p) => $this->serializePhone($p), $dto->phones),
        ];

        if ($dto->createdAt !== null) {
            $result['created_at'] = $dto->createdAt;
        }

        if ($dto->updatedAt !== null) {
            $result['updated_at'] = $dto->updatedAt;
        }

        return $result;
    }

    private function serializePhone(PhoneDTO $dto): array
    {
        $result = [
            'phone_number' => $dto->phoneNumber,
            'label'        => $dto->label,
        ];

        if ($dto->id !== null) {
            $result = ['id' => $dto->id] + $result;
        }

        return $result;
    }
}
