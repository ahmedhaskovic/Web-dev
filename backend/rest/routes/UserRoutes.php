<?php

// User endpoints. Routes only read the request, call one service method and return JSON.
// Errors are thrown by the service and turned into JSON by the error handler in index.php.

Flight::route('GET /users', function () {
    Flight::json(Flight::userService()->get_all());
});

Flight::route('GET /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->get_by_id($id));
});

Flight::route('POST /users', function () {
    Flight::json(Flight::userService()->add(Flight::jsonBody()), 201);
});

// PUT replaces the whole record, so every required field must be sent.
Flight::route('PUT /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->update($id, Flight::jsonBody()));
});

Flight::route('DELETE /users/@id:[0-9]+', function ($id) {
    Flight::userService()->delete($id);
    Flight::json(['message' => 'User deleted.']);
});
