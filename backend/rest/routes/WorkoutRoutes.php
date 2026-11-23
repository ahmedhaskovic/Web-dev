<?php

// Workout endpoints. Routes only read the request, call one service method and return JSON.
// Errors are thrown by the service and turned into JSON by the error handler in index.php.

// All workouts of one user, newest first.
Flight::route('GET /users/@id:[0-9]+/workouts', function ($id) {
    Flight::json(Flight::workoutService()->get_by_user($id));
});

Flight::route('GET /workouts', function () {
    Flight::json(Flight::workoutService()->get_all());
});

Flight::route('GET /workouts/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workoutService()->get_by_id($id));
});

Flight::route('POST /workouts', function () {
    Flight::json(Flight::workoutService()->add(Flight::jsonBody()), 201);
});

// PUT replaces the whole record, so every required field must be sent.
Flight::route('PUT /workouts/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workoutService()->update($id, Flight::jsonBody()));
});

Flight::route('DELETE /workouts/@id:[0-9]+', function ($id) {
    Flight::workoutService()->delete($id);
    Flight::json(['message' => 'Workout deleted.']);
});
