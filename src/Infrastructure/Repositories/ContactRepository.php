<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ContactRepositoryInterface;
use PDO;

final class ContactRepository implements ContactRepositoryInterface
{
    private const BASE_SELECT = '
        SELECT c.id, c.first_name, c.last_name, c.email,
               c.created_at, c.updated_at,
               p.id AS phone_id, p.phone_number, p.label
        FROM contacts c
        LEFT JOIN phones p ON p.contact_id = c.id
    ';

    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function findAll(): array
    {
        $sql = self::BASE_SELECT . ' ORDER BY c.id ASC, p.id ASC';

        $rows = $this->pdo->query($sql)->fetchAll();

        return $this->groupContactsWithPhones($rows);
    }

    public function findById(int $id): ?array
    {
        $sql = self::BASE_SELECT . ' WHERE c.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();

        if (count($rows) === 0) {
            return null;
        }

        return $this->groupContactsWithPhones($rows)[0];
    }

    public function create(array $contact, array $phones = []): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO contacts (first_name, last_name, email)
                VALUES (:first_name, :last_name, :email)
            ');
            $stmt->execute([
                'first_name' => $contact['first_name'],
                'last_name'  => $contact['last_name'],
                'email'      => $contact['email'],
            ]);

            $contactId = (int) $this->pdo->lastInsertId();

            $this->insertPhones($contactId, $phones);

            $this->pdo->commit();
            return $contactId;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $contact, array $phones = []): bool
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('
                UPDATE contacts SET first_name = :first_name, last_name = :last_name, email = :email
                WHERE id = :id
            ');
            $stmt->execute([
                'id'         => $id,
                'first_name' => $contact['first_name'],
                'last_name'  => $contact['last_name'],
                'email'      => $contact['email'],
            ]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->prepare('DELETE FROM phones WHERE contact_id = :contact_id')
                ->execute(['contact_id' => $id]);

            $this->insertPhones($id, $phones);

            $this->pdo->commit();
            return true;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function insertPhones(int $contactId, array $phones): void
    {
        if (count($phones) === 0) {
            return;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO phones (contact_id, phone_number, label)
            VALUES (:contact_id, :phone_number, :label)
        ');

        foreach ($phones as $phone) {
            $stmt->execute([
                'contact_id'   => $contactId,
                'phone_number' => $phone['phone_number'],
                'label'        => $phone['label'] ?? 'mobile',
            ]);
        }
    }

    public function existsWithEmail(string $email, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT 1 FROM contacts WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql              .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    private function groupContactsWithPhones(array $rows): array
    {
        $contacts = [];

        foreach ($rows as $row) {
            $contactId = (int) $row['id'];

            if (!isset($contacts[$contactId])) {
                $contacts[$contactId] = [
                    'id'         => $contactId,
                    'first_name' => $row['first_name'],
                    'last_name'  => $row['last_name'],
                    'email'      => $row['email'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'phones'     => [],
                ];
            }

            if ($row['phone_id'] !== null) {
                $contacts[$contactId]['phones'][] = [
                    'id'           => (int) $row['phone_id'],
                    'phone_number' => $row['phone_number'],
                    'label'        => $row['label'],
                ];
            }
        }

        return array_values($contacts);
    }
}
