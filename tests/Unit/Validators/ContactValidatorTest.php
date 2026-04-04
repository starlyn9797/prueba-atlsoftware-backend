<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use App\Application\Validators\ContactValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContactValidatorTest extends TestCase
{
    private ContactValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ContactValidator();
    }

    #[Test]
    public function validateReturnsValidatedDataWithAllFields(): void
    {
        $input = [
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'Juan@Example.COM',
            'phones'     => [
                ['phone_number' => '809-555-0001', 'label' => 'mobile'],
                ['phone_number' => '809-555-0002', 'label' => 'work'],
            ],
        ];

        $result = $this->validator->validate($input);

        $this->assertSame('Juan', $result['first_name']);
        $this->assertSame('Pérez', $result['last_name']);
        $this->assertSame('juan@example.com', $result['email']);
        $this->assertCount(2, $result['phones']);
        $this->assertSame('809-555-0001', $result['phones'][0]['phone_number']);
        $this->assertSame('work', $result['phones'][1]['label']);
    }

    #[Test]
    public function validateReturnsEmptyPhonesWhenNotProvided(): void
    {
        $input = [
            'first_name' => 'María',
            'last_name'  => 'López',
            'email'      => 'maria@test.com',
        ];

        $result = $this->validator->validate($input);

        $this->assertSame([], $result['phones']);
    }

    #[Test]
    public function validateAssignsDefaultLabelWhenOmitted(): void
    {
        $input = [
            'first_name' => 'Carlos',
            'last_name'  => 'Ruiz',
            'email'      => 'carlos@test.com',
            'phones'     => [
                ['phone_number' => '809-555-0001'],
            ],
        ];

        $result = $this->validator->validate($input);

        $this->assertSame('mobile', $result['phones'][0]['label']);
    }

    #[Test]
    public function validateNormalizesEmailToLowercase(): void
    {
        $input = [
            'first_name' => 'Ana',
            'last_name'  => 'Martínez',
            'email'      => 'ANA@DOMINIO.COM',
        ];

        $result = $this->validator->validate($input);

        $this->assertSame('ana@dominio.com', $result['email']);
    }

    #[Test]
    public function validateTrimsWhitespace(): void
    {
        $input = [
            'first_name' => '  Pedro  ',
            'last_name'  => '  García  ',
            'email'      => '  pedro@test.com  ',
        ];

        $result = $this->validator->validate($input);

        $this->assertSame('Pedro', $result['first_name']);
        $this->assertSame('García', $result['last_name']);
        $this->assertSame('pedro@test.com', $result['email']);
    }

    #[Test]
    public function validateThrowsWhenFirstNameIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'last_name' => 'Pérez',
            'email'     => 'test@test.com',
        ]);
    }

    #[Test]
    public function validateThrowsWhenLastNameIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'email'      => 'test@test.com',
        ]);
    }

    #[Test]
    public function validateThrowsWhenEmailIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
        ]);
    }

    #[Test]
    public function validateThrowsWhenEmailFormatIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'no-es-un-email',
        ]);
    }

    #[Test]
    public function validateThrowsWhenFirstNameExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => str_repeat('A', 101),
            'last_name'  => 'Pérez',
            'email'      => 'test@test.com',
        ]);
    }

    #[Test]
    public function validateThrowsWhenPhoneNumberIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'test@test.com',
            'phones'     => [
                ['label' => 'mobile'],
            ],
        ]);
    }

    #[Test]
    public function validateThrowsWhenPhoneEntryIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'test@test.com',
            'phones'     => ['not-an-array'],
        ]);
    }

    #[Test]
    public function validateThrowsWhenPhonesFieldIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'test@test.com',
            'phones'     => 'invalid',
        ]);
    }

    #[Test]
    public function validateAccumulatesMultipleErrors(): void
    {
        try {
            $this->validator->validate([]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);

            $this->assertIsArray($errors);
            $this->assertGreaterThanOrEqual(3, count($errors));
        }
    }

    #[Test]
    public function validateSanitizesHtmlInFields(): void
    {
        $input = [
            'first_name' => '<script>alert("xss")</script>',
            'last_name'  => 'Pérez',
            'email'      => 'test@test.com',
        ];

        $result = $this->validator->validate($input);

        $this->assertStringNotContainsString('<script>', $result['first_name']);
    }
}
