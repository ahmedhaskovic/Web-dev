<?php

// Exercise endpoints. Routes only read the request, call one service method and return JSON.
// Errors are thrown by the service and turned into JSON by the error handler in index.php.

// Registered before /exercises/@id; the [0-9]+ pattern also keeps the two apart.
Flight::route('GET /exercises/muscle-groups', function () {
    Flight::json(Flight::exerciseService()->get_muscle_group_stats());
});

Flight::route('GET /exercises', function () {
    Flight::json(Flight::exerciseService()->get_all());
});

Flight::route('GET /exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::exerciseService()->get_by_id($id));
});

Flight::route('POST /exercises', function () {
    Flight::json(Flight::exerciseService()->add(Flight::jsonBody()), 201);
});

// PUT replaces the whole record, so every required field must be sent.
Flight::route('PUT /exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::exerciseService()->update($id, Flight::jsonBody()));
});

Flight::route('DELETE /exercises/@id:[0-9]+', function ($id) {
    Flight::exerciseService()->delete($id);
    Flight::json(['message' => 'Exercise deleted.']);
});
