<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Application\Services\ContactService;
use App\Domain\DTOs\ContactDTO;
use App\Domain\Interfaces\ContactRepositoryInterface;
use App\Domain\Interfaces\ContactValidatorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContactServiceTest extends TestCase
{
    private ContactRepositoryInterface&MockObject $repository;
    private ContactValidatorInterface&MockObject $validator;
    private ContactService $service;

    private const CONTACT_ROW = [
        'id'         => 1,
        'first_name' => 'Juan',
        'last_name'  => 'Pérez',
        'email'      => 'juan@test.com',
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
        'phones'     => [
            ['id' => 10, 'phone_number' => '809-555-0001', 'label' => 'mobile'],
        ],
    ];

    private const VALIDATED_DATA = [
        'first_name' => 'Juan',
        'last_name'  => 'Pérez',
        'email'      => 'juan@test.com',
        'phones'     => [
            ['phone_number' => '809-555-0001', 'label' => 'mobile'],
        ],
    ];

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ContactRepositoryInterface::class);
        $this->validator  = $this->createMock(ContactValidatorInterface::class);
        $this->service    = new ContactService($this->repository, $this->validator);
    }

    // --- getAll ---

    #[Test]
    public function getAllReturnsArrayOfContactDTOs(): void
    {
        $this->repository
            ->method('findAll')
            ->willReturn([self::CONTACT_ROW]);

        $result = $this->service->getAll();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ContactDTO::class, $result[0]);
        $this->assertSame('Juan', $result[0]->firstName);
    }

    #[Test]
    public function getAllReturnsEmptyArrayWhenNoContacts(): void
    {
        $this->repository
            ->method('findAll')
            ->willReturn([]);

        $result = $this->service->getAll();

        $this->assertSame([], $result);
    }

    // --- getById ---

    #[Test]
    public function getByIdReturnsContactDTO(): void
    {
        $this->repository
            ->method('findById')
            ->with(1)
            ->willReturn(self::CONTACT_ROW);

        $result = $this->service->getById(1);

        $this->assertInstanceOf(ContactDTO::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('juan@test.com', $result->email);
        $this->assertCount(1, $result->phones);
        $this->assertSame('809-555-0001', $result->phones[0]->phoneNumber);
    }

    #[Test]
    public function getByIdThrows404WhenNotFound(): void
    {
        $this->repository
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(404);

        $this->service->getById(999);
    }

    // --- create ---

    #[Test]
    public function createReturnsContactDTOOnSuccess(): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(self::VALIDATED_DATA);

        $this->repository
            ->method('existsWithEmail')
            ->with('juan@test.com')
            ->willReturn(false);

        $this->repository
            ->method('create')
            ->willReturn(1);

        $this->repository
            ->method('findById')
            ->with(1)
            ->willReturn(self::CONTACT_ROW);

        $result = $this->service->create(['any' => 'data']);

        $this->assertInstanceOf(ContactDTO::class, $result);
        $this->assertSame(1, $result->id);
    }

    #[Test]
    public function createThrows409WhenEmailAlreadyExists(): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(self::VALIDATED_DATA);

        $this->repository
            ->method('existsWithEmail')
            ->with('juan@test.com')
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(409);

        $this->service->create(['any' => 'data']);
    }

    #[Test]
    public function createPassesContactDataAndPhonesToRepository(): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(self::VALIDATED_DATA);

        $this->repository
            ->method('existsWithEmail')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'first_name' => 'Juan',
                    'last_name'  => 'Pérez',
                    'email'      => 'juan@test.com',
                ],
                [
                    ['phone_number' => '809-555-0001', 'label' => 'mobile'],
                ]
            )
            ->willReturn(1);

        $this->repository
            ->method('findById')
            ->willReturn(self::CONTACT_ROW);

        $this->service->create([]);
    }

    // --- update ---

    #[Test]
    public function updateReturnsContactDTOOnSuccess(): void
    {
        $this->repository
            ->method('findById')
            ->with(1)
            ->willReturn(self::CONTACT_ROW);

        $this->validator
            ->method('validate')
            ->willReturn(self::VALIDATED_DATA);

        $this->repository
            ->method('existsWithEmail')
            ->with('juan@test.com', 1)
            ->willReturn(false);

        $this->repository
            ->method('update')
            ->willReturn(true);

        $result = $this->service->update(1, ['any' => 'data']);

        $this->assertInstanceOf(ContactDTO::class, $result);
    }

    #[Test]
    public function updateThrows404WhenContactDoesNotExist(): void
    {
        $this->repository
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(404);

        $this->service->update(999, ['any' => 'data']);
    }

    #[Test]
    public function updateThrows409WhenEmailBelongsToAnotherContact(): void
    {
        $this->repository
            ->method('findById')
            ->with(1)
            ->willReturn(self::CONTACT_ROW);

        $this->validator
            ->method('validate')
            ->willReturn(self::VALIDATED_DATA);

        $this->repository
            ->method('existsWithEmail')
            ->with('juan@test.com', 1)
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(409);

        $this->service->update(1, ['any' => 'data']);
    }

    // --- delete ---

    #[Test]
    public function deleteSucceedsWhenContactExists(): void
    {
        $this->repository
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $this->service->delete(1);

        $this->assertTrue(true);
    }

    #[Test]
    public function deleteThrows404WhenContactDoesNotExist(): void
    {
        $this->repository
            ->method('delete')
            ->with(999)
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(404);

        $this->service->delete(999);
    }

    // --- buildContactDTO (tested indirectly) ---

    #[Test]
    public function getByIdBuildsDTOWithCorrectPhoneMapping(): void
    {
        $row = self::CONTACT_ROW;
        $row['phones'][] = ['id' => 11, 'phone_number' => '809-555-0002', 'label' => 'work'];

        $this->repository
            ->method('findById')
            ->willReturn($row);

        $result = $this->service->getById(1);

        $this->assertCount(2, $result->phones);
        $this->assertSame(10, $result->phones[0]->id);
        $this->assertSame(11, $result->phones[1]->id);
        $this->assertSame('work', $result->phones[1]->label);
    }

    #[Test]
    public function getByIdBuildsDTOWithEmptyPhonesArray(): void
    {
        $row = self::CONTACT_ROW;
        $row['phones'] = [];

        $this->repository
            ->method('findById')
            ->willReturn($row);

        $result = $this->service->getById(1);

        $this->assertSame([], $result->phones);
    }
}
