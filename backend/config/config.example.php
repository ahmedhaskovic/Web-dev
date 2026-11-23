<?php

// Copy this file to config.php and adjust the values for your machine.
// config.php is ignored by Git; never commit real credentials.
// Every value can also be overridden with an environment variable of the same name.

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

    // Open-Meteo (https://open-meteo.com) is free for non-commercial use and needs no API key.
    public static function WEATHER_GEOCODING_URL(): string
    {
        return self::environment('WEATHER_GEOCODING_URL', 'https://geocoding-api.open-meteo.com/v1/search');
    }

    public static function WEATHER_FORECAST_URL(): string
    {
        return self::environment('WEATHER_FORECAST_URL', 'https://api.open-meteo.com/v1/forecast');
    }

    // Seconds to wait for the weather API before giving up.
    public static function WEATHER_TIMEOUT(): int
    {
        $timeout = filter_var(self::environment('WEATHER_TIMEOUT', '5'), FILTER_VALIDATE_INT);

        return ($timeout === false || $timeout < 1) ? 5 : $timeout;
    }
}
