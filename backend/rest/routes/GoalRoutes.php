<?php

// Goal endpoints. Routes only read the request, call one service method and return JSON.
// Errors are thrown by the service and turned into JSON by the error handler in index.php.

// All goals of one user, closest target date first.
Flight::route('GET /users/@id:[0-9]+/goals', function ($id) {
    Flight::json(Flight::goalService()->get_by_user($id));
});

Flight::route('GET /goals', function () {
    Flight::json(Flight::goalService()->get_all());
});

Flight::route('GET /goals/@id:[0-9]+', function ($id) {
    Flight::json(Flight::goalService()->get_by_id($id));
});

Flight::route('POST /goals', function () {
    Flight::json(Flight::goalService()->add(Flight::jsonBody()), 201);
});

// PUT replaces the whole record, so every required field must be sent.
Flight::route('PUT /goals/@id:[0-9]+', function ($id) {
    Flight::json(Flight::goalService()->update($id, Flight::jsonBody()));
});

Flight::route('DELETE /goals/@id:[0-9]+', function ($id) {
    Flight::goalService()->delete($id);
    Flight::json(['message' => 'Goal deleted.']);
});
