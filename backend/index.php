<?php

// Trackify REST API entry point (FlightPHP).
// Request flow: route (rest/routes) -> service (rest/services) -> DAO (rest/dao) -> MySQL.

// Never print PHP warnings into a JSON response. Flight turns every warning into an
// exception, and the error handler below logs it and answers with a JSON 500.
ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Sarajevo');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/utils/exceptions.php';
require_once __DIR__ . '/utils/Validator.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/rest/services/UserService.php';
require_once __DIR__ . '/rest/services/WorkoutService.php';
require_once __DIR__ . '/rest/services/ExerciseService.php';
require_once __DIR__ . '/rest/services/WorkoutExerciseService.php';
require_once __DIR__ . '/rest/services/GoalService.php';
require_once __DIR__ . '/rest/services/WeatherService.php';

// One shared instance per service, available as Flight::userService() and so on.
Flight::register('userService', UserService::class);
Flight::register('workoutService', WorkoutService::class);
Flight::register('exerciseService', ExerciseService::class);
Flight::register('workoutExerciseService', WorkoutExerciseService::class);
Flight::register('goalService', GoalService::class);
Flight::register('weatherService', WeatherService::class);

// Reads the request body as a JSON object. Malformed JSON, a list or a plain value gives 400.
Flight::map('jsonBody', function (): array {
    $data = json_decode(Flight::request()->getBody(), true);

    if (!is_array($data) || ($data !== [] && array_is_list($data))) {
        throw new ValidationException('The request body must be a valid JSON object.');
    }

    return $data;
});

// Sends {"error": "..."} with the given status and stops the request.
Flight::map('jsonError', function (string $message, int $status): void {
    // Throw away anything already printed so the response is pure JSON.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    Flight::jsonHalt(['error' => $message], $status);
});

// Global error handler: every exception thrown by a route or service ends here.
Flight::map('error', function (Throwable $error): void {
    if ($error instanceof HttpException) {
        // Expected errors (400, 404, 503, ...) are safe to show to the client.
        Flight::jsonError($error->getMessage(), $error->get_status());
    }

    // Unexpected errors: log the details, but never show SQL, stack traces or file paths.
    $request = Flight::request();
    Logger::error(sprintf(
        '%s %s 500 %s: %s (%s:%d)',
        $request->method,
        $request->url,
        get_class($error),
        $error->getMessage(),
        basename($error->getFile()),
        $error->getLine()
    ));

    Flight::jsonError('Something went wrong on the server. Please try again later.', 500);
});

Flight::map('notFound', function (): void {
    Flight::jsonError('Endpoint not found.', 404);
});

// The URL exists, but not with this HTTP method (for example PATCH /users/1).
// Flight only passes the last matching route, so the methods of every route with this URL are collected.
Flight::map('methodNotFound', function ($route): void {
    $methods = [];
    foreach (Flight::router()->getRoutes() as $candidate) {
        if ($candidate->matchUrl(Flight::request()->url)) {
            $methods = array_merge($methods, $candidate->methods);
        }
    }
    $allowed = implode(', ', array_unique($methods));

    Flight::response()->header('Allow', $allowed);
    Flight::jsonError("Method not allowed. Allowed methods: {$allowed}.", 405);
});

// Health check.
Flight::route('GET /', function () {
    Flight::json(['message' => 'Trackify API is running.']);
});

require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/WorkoutRoutes.php';
require_once __DIR__ . '/rest/routes/ExerciseRoutes.php';
require_once __DIR__ . '/rest/routes/WorkoutExerciseRoutes.php';
require_once __DIR__ . '/rest/routes/GoalRoutes.php';
require_once __DIR__ . '/rest/routes/WeatherRoutes.php';

Flight::start();
