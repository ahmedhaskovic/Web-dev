<?php

require_once __DIR__ . '/exceptions.php';

// Reusable validation rules. Each method throws a ValidationException (HTTP 400)
// with a message that names the field.
// Every method except required() ignores null, so optional fields can use them too:
// call required() first when a field is mandatory.

class Validator
{
    public static function required($value, string $field): void
    {
        if ($value === null || $value === '') {
            throw new ValidationException("{$field} is required.");
        }
    }

    public static function length($value, string $field, int $max, int $min = 1): void
    {
        if ($value === null) {
            return;
        }

        $length = mb_strlen((string) $value);
        if ($length < $min || $length > $max) {
            throw new ValidationException("{$field} must be between {$min} and {$max} characters long.");
        }
    }

    public static function integer($value, string $field, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }

        // true/false are not numbers, and "5.5", "abc" or "1e3" are not whole numbers.
        $isWholeNumber = is_int($value) || (is_string($value) && preg_match('/^-?[0-9]+$/', $value) === 1);
        if (is_bool($value) || !$isWholeNumber || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException("{$field} must be a whole number.");
        }

        self::range((int) $value, $field, $min, $max);
    }

    // Decimal numbers such as weights. $decimals matches the DECIMAL(x,2) columns.
    public static function number($value, string $field, ?float $min = null, ?float $max = null, int $decimals = 2): void
    {
        if ($value === null) {
            return;
        }

        $pattern = '/^-?[0-9]+(\.[0-9]{1,' . $decimals . '})?$/';
        if (is_bool($value) || !is_scalar($value) || preg_match($pattern, (string) $value) !== 1) {
            throw new ValidationException("{$field} must be a number with at most {$decimals} decimal places.");
        }

        self::range((float) $value, $field, $min, $max);
    }

    public static function one_of($value, string $field, array $allowed): void
    {
        if ($value === null) {
            return;
        }

        if (!in_array($value, $allowed, true)) {
            throw new ValidationException("{$field} must be one of: " . implode(', ', $allowed) . '.');
        }
    }

    public static function email($value, string $field): void
    {
        if ($value === null) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException("{$field} must be a valid email address.");
        }
    }

    // Strict Y-m-d: "2026-02-31" or "2026-2-3" are rejected.
    public static function date($value, string $field): void
    {
        self::date_format($value, $field, 'Y-m-d', 'YYYY-MM-DD');
    }

    // Strict Y-m-d H:i:s.
    public static function datetime($value, string $field): void
    {
        self::date_format($value, $field, 'Y-m-d H:i:s', 'YYYY-MM-DD HH:MM:SS');
    }

    // The date must be after today. Call date() first.
    public static function in_future($value, string $field): void
    {
        if ($value === null) {
            return;
        }

        if (substr((string) $value, 0, 10) <= date('Y-m-d')) {
            throw new ValidationException("{$field} must be in the future.");
        }
    }

    // The date must be before today, or today at the latest when $allowToday is true. Call date() first.
    public static function in_past($value, string $field, bool $allowToday = false): void
    {
        if ($value === null) {
            return;
        }

        $date = substr((string) $value, 0, 10);
        $today = date('Y-m-d');
        if ($date > $today || (!$allowToday && $date === $today)) {
            throw new ValidationException("{$field} cannot be in the future.");
        }
    }

    // 8 to 72 characters (72 bytes is the bcrypt limit) with at least one letter and one digit.
    public static function password($value, string $field): void
    {
        if ($value === null) {
            return;
        }

        $value = (string) $value;
        if (strlen($value) < 8 || strlen($value) > 72) {
            throw new ValidationException("{$field} must be between 8 and 72 characters long.");
        }
        if (preg_match('/[A-Za-z]/', $value) !== 1 || preg_match('/[0-9]/', $value) !== 1) {
            throw new ValidationException("{$field} must contain at least one letter and one digit.");
        }
    }

    // Names and text must not contain HTML tags.
    public static function no_html($value, string $field): void
    {
        if ($value === null) {
            return;
        }

        if (strpbrk((string) $value, '<>') !== false) {
            throw new ValidationException("{$field} must not contain < or > characters.");
        }
    }

    private static function range($number, string $field, $min, $max): void
    {
        if ($min !== null && $number < $min) {
            throw new ValidationException("{$field} must be at least {$min}.");
        }
        if ($max !== null && $number > $max) {
            throw new ValidationException("{$field} must be at most {$max}.");
        }
    }

    private static function date_format($value, string $field, string $format, string $label): void
    {
        if ($value === null) {
            return;
        }

        // The "!" resets unspecified parts, and comparing with format() rejects overflowing dates.
        $date = is_string($value) ? DateTime::createFromFormat('!' . $format, $value) : false;
        if ($date === false || $date->format($format) !== $value) {
            throw new ValidationException("{$field} must be a valid date in the format {$label}.");
        }
    }
}
