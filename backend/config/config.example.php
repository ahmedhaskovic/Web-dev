<?php

class Config
{
    private static function environment(string $key, string $default): string
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    public static function DB_HOST(): string
    {
        return self::environment('DB_HOST', '127.0.0.1');
    }

    public static function DB_PORT(): int
    {
        $port = self::environment('DB_PORT', '3306');
        $validatedPort = filter_var($port, FILTER_VALIDATE_INT);

        if ($validatedPort === false || $validatedPort < 1 || $validatedPort > 65535) {
            throw new InvalidArgumentException('DB_PORT must be an integer between 1 and 65535.');
        }

        return $validatedPort;
    }

    public static function DB_NAME(): string
    {
        return self::environment('DB_NAME', 'trackify');
    }

    public static function DB_USER(): string
    {
        return self::environment('DB_USER', 'root');
    }

    public static function DB_PASSWORD(): string
    {
        return self::environment('DB_PASSWORD', '');
    }
}
