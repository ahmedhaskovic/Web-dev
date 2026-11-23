<?php

// Workout exercise (exercises performed in a workout) endpoints. Routes only read the request, call one service method and return JSON.
// Errors are thrown by the service and turned into JSON by the error handler in index.php.

// The exercises of one workout, including each exercise's name and muscle group.
Flight::route('GET /workouts/@id:[0-9]+/exercises', function ($id) {
    Flight::json(Flight::workoutExerciseService()->get_by_workout($id));
});

Flight::route('GET /workout-exercises', function () {
    Flight::json(Flight::workoutExerciseService()->get_all());
});

Flight::route('GET /workout-exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workoutExerciseService()->get_by_id($id));
});

Flight::route('POST /workout-exercises', function () {
    Flight::json(Flight::workoutExerciseService()->add(Flight::jsonBody()), 201);
});

// PUT replaces the whole record, so every required field must be sent.
Flight::route('PUT /workout-exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workoutExerciseService()->update($id, Flight::jsonBody()));
});

Flight::route('DELETE /workout-exercises/@id:[0-9]+', function ($id) {
    Flight::workoutExerciseService()->delete($id);
    Flight::json(['message' => 'Workout exercise deleted.']);
});
