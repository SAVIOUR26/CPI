<?php

namespace App\Core;

/**
 * Simple rule-based validator. Usage:
 *   $v = Validator::make($request->all(), [
 *       'email' => 'required|email',
 *       'password' => 'required|min:8',
 *   ]);
 *   if ($v->fails()) { ... $v->errors() ... }
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    private function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->run();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucwords(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
                    $this->fail($field, "$label is required.");
                }
                break;
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->fail($field, "$label must be a valid email address.");
                }
                break;
            case 'min':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) < (int) $params[0]) {
                    $this->fail($field, "$label must be at least {$params[0]} characters.");
                }
                break;
            case 'max':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) > (int) $params[0]) {
                    $this->fail($field, "$label must not exceed {$params[0]} characters.");
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->fail($field, "$label must be a number.");
                }
                break;
            case 'integer':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->fail($field, "$label must be a whole number.");
                }
                break;
            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
                    $this->fail($field, "$label is not valid.");
                }
                break;
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($this->data[$confirmField] ?? null) !== $value) {
                    $this->fail($field, "$label confirmation does not match.");
                }
                break;
            case 'date':
                if ($value !== null && $value !== '' && strtotime((string) $value) === false) {
                    $this->fail($field, "$label must be a valid date.");
                }
                break;
        }
    }

    private function fail(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            return $messages[0];
        }
        return null;
    }
}
