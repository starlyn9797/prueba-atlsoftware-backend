<?php

declare(strict_types=1);

namespace App\Application\Validators;

use App\Domain\Interfaces\ContactValidatorInterface;
use InvalidArgumentException;

final class ContactValidator implements ContactValidatorInterface
{
    private const MAX_NAME_LENGTH  = 100;
    private const MAX_EMAIL_LENGTH = 255;
    private const MAX_PHONE_LENGTH = 20;
    private const MAX_LABEL_LENGTH = 50;

    public function validate(array $data): array
    {
        $errors = [];

        $firstName = $this->validateRequiredString($data, 'first_name', self::MAX_NAME_LENGTH, $errors);
        $lastName  = $this->validateRequiredString($data, 'last_name', self::MAX_NAME_LENGTH, $errors);
        $email     = $this->validateEmail($data, $errors);
        $phones    = $this->validatePhones($data, $errors);

        if (count($errors) > 0) {
            throw new InvalidArgumentException(json_encode($errors));
        }

        return [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phones'     => $phones,
        ];
    }

    private function validateRequiredString(array $data, string $field, int $maxLength, array &$errors): string
    {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            $errors[] = "El campo '{$field}' es requerido.";
            return '';
        }

        $value = trim((string) $data[$field]);

        if (mb_strlen($value) > $maxLength) {
            $errors[] = "El campo '{$field}' no puede exceder {$maxLength} caracteres.";
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function validateEmail(array $data, array &$errors): string
    {
        if (!isset($data['email']) || trim((string) $data['email']) === '') {
            $errors[] = "El campo 'email' es requerido.";
            return '';
        }

        $email = trim((string) $data['email']);

        if (mb_strlen($email) > self::MAX_EMAIL_LENGTH) {
            $errors[] = "El campo 'email' no puede exceder " . self::MAX_EMAIL_LENGTH . " caracteres.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El campo 'email' tiene un formato inválido.";
        }

        return strtolower($email);
    }

    private function validatePhones(array $data, array &$errors): array
    {
        if (!isset($data['phones'])) {
            return [];
        }

        if (!is_array($data['phones'])) {
            $errors[] = "El campo 'phones' debe ser un array.";
            return [];
        }

        $sanitized = [];

        foreach ($data['phones'] as $index => $phone) {
            if (!is_array($phone)) {
                $errors[] = "phones[{$index}]: debe ser un objeto con 'phone_number'.";
                continue;
            }

            $number = trim((string) ($phone['phone_number'] ?? ''));

            if ($number === '') {
                $errors[] = "phones[{$index}]: el campo 'phone_number' es requerido.";
                continue;
            }

            if (mb_strlen($number) > self::MAX_PHONE_LENGTH) {
                $errors[] = "phones[{$index}]: 'phone_number' no puede exceder " . self::MAX_PHONE_LENGTH . " caracteres.";
                continue;
            }

            $label = trim((string) ($phone['label'] ?? 'mobile'));

            if (mb_strlen($label) > self::MAX_LABEL_LENGTH) {
                $errors[] = "phones[{$index}]: 'label' no puede exceder " . self::MAX_LABEL_LENGTH . " caracteres.";
                continue;
            }

            $sanitized[] = [
                'phone_number' => htmlspecialchars($number, ENT_QUOTES, 'UTF-8'),
                'label'        => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $sanitized;
    }
}
