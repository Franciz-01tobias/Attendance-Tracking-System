<?php

declare(strict_types=1);

namespace App\Validators;

final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    public function required(array $data, string $field, string $message = 'Required'): self
    {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function in(array $data, string $field, array $allowed, string $message = 'Invalid value'): self
    {
        $value = $data[$field] ?? null;
        if ($value === null || !in_array($value, $allowed, true)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function maxLength(array $data, string $field, int $max, string $message = 'Too long'): self
    {
        $value = (string) ($data[$field] ?? '');
        if (strlen($value) > $max) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function email(array $data, string $field, string $message = 'Invalid email'): self
    {
        $value = (string) ($data[$field] ?? '');
        if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
