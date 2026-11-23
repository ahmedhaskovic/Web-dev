<?php

// Writes one line per event to backend/logs/info.log or backend/logs/error.log.
// Example line: [2026-11-23 14:05:12] ERROR GET /users/9 500 PDOException: ... (UserDao.php:42)

class Logger
{
    public static function info(string $message): void
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write(string $fileName, string $level, string $message): void
    {
        // Replace line breaks so one event is always one line (prevents fake log lines).
        $message = str_replace(["\r", "\n"], ' ', $message);
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $level . ' ' . $message . PHP_EOL;

        $directory = __DIR__ . '/../logs';

        // A logging problem must never break the request, so errors are suppressed with @.
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }
        @file_put_contents($directory . '/' . $fileName, $line, FILE_APPEND | LOCK_EX);
    }
}
