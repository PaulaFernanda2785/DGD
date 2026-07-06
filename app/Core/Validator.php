<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function required(string $field, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function email(string $field, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function integerMin(string $field, int $min, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && (!ctype_digit((string) $value) || (int) $value < $min)) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function date(string $field, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || $value === '') {
            return $this;
        }

        $date = \DateTime::createFromFormat('Y-m-d', (string) $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
