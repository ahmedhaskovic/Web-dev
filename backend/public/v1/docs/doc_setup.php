<?php

// Shared OpenAPI definitions: API info, server, tags, reusable responses and the
// input/output schema of every entity. The endpoints themselves are in paths/*.php.
// API_BASE_URL is defined in swagger.php before this file is loaded.

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Trackify API',
    description: 'REST API of Trackify, a fitness tracking application. Errors are returned as {"error": "..."}. '
        . 'Authentication (JWT) and role checks are added in Milestone 4.'
)]
#[OA\Server(url: API_BASE_URL, description: 'This server')]
#[OA\Tag(name: 'Health', description: 'API status')]
#[OA\Tag(name: 'Users', description: 'User accounts (admin or member)')]
#[OA\Tag(name: 'Workouts', description: 'Workouts logged by a user')]
#[OA\Tag(name: 'Exercises', description: 'Exercise catalog')]
#[OA\Tag(name: 'Workout exercises', description: 'Exercises performed inside a workout')]
#[OA\Tag(name: 'Goals', description: 'Weight goals of a user')]
#[OA\Tag(name: 'Weather', description: 'Outdoor workout forecast from the Open-Meteo API')]

// ---------- Shared schemas and responses ----------
#[OA\Schema(
    schema: 'Error',
    type: 'object',
    properties: [new OA\Property(property: 'error', type: 'string', example: 'User not found.')]
)]
#[OA\Schema(
    schema: 'Message',
    type: 'object',
    properties: [new OA\Property(property: 'message', type: 'string', example: 'User deleted.')]
)]
#[OA\Schema(
    schema: 'Timestamps',
    type: 'object',
    properties: [
        new OA\Property(property: 'created_at', type: 'string', readOnly: true, example: '2026-11-23 10:15:00'),
        new OA\Property(property: 'updated_at', type: 'string', readOnly: true, example: '2026-11-23 10:15:00'),
    ]
)]
#[OA\Response(
    response: 'BadRequest',
    description: 'Invalid JSON, invalid data or a broken business rule.',
    content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'name is required.'])
)]
#[OA\Response(
    response: 'NotFound',
    description: 'The record does not exist.',
    content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'User not found.'])
)]
#[OA\Response(
    response: 'ServerError',
    description: 'Unexpected server error. Details are written to the error log, never to the response.',
    content: new OA\JsonContent(
        ref: '#/components/schemas/Error',
        example: ['error' => 'Something went wrong on the server. Please try again later.']
    )
)]

// ---------- Users ----------
// UserFields is shared by the input (adds password) and the output (adds id and timestamps, never the password).
#[OA\Schema(
    schema: 'UserFields',
    type: 'object',
    required: ['name', 'email', 'current_weight'],
    properties: [
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 100, example: 'Ena Begić'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 254, description: 'Unique, stored in lowercase.', example: 'ena@trackify.local'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'member'], default: 'member', example: 'member'),
        new OA\Property(property: 'current_weight', type: 'string', format: 'decimal', description: 'Kilograms, 20 to 500, at most 2 decimals. A JSON number is also accepted.', example: '64.50'),
        new OA\Property(property: 'city', type: 'string', maxLength: 100, nullable: true, description: 'Used for the weather forecast.', example: 'Tuzla'),
    ]
)]
#[OA\Schema(
    schema: 'UserInput',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/UserFields'),
        new OA\Schema(properties: [
            new OA\Property(
                property: 'password',
                type: 'string',
                format: 'password',
                writeOnly: true,
                minLength: 8,
                maxLength: 72,
                description: 'Required when creating. At least one letter and one digit. Leave it out on update to keep the current password.',
                example: 'Secret123'
            ),
        ]),
    ]
)]
#[OA\Schema(
    schema: 'User',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 4)]),
        new OA\Schema(ref: '#/components/schemas/UserFields'),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]

// ---------- Workouts ----------
#[OA\Schema(
    schema: 'WorkoutInput',
    type: 'object',
    required: ['user_id', 'date'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', description: 'Existing user. Cannot change on update.', example: 2),
        new OA\Property(property: 'date', type: 'string', format: 'date', description: 'YYYY-MM-DD, not in the future, not before 2000-01-01.', example: '2026-11-20'),
        new OA\Property(property: 'total_calories', type: 'integer', minimum: 0, maximum: 10000, default: 0, example: 350),
        new OA\Property(property: 'notes', type: 'string', maxLength: 1000, nullable: true, example: 'Interval run'),
    ]
)]
#[OA\Schema(
    schema: 'Workout',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 6)]),
        new OA\Schema(ref: '#/components/schemas/WorkoutInput'),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]

// ---------- Exercises ----------
#[OA\Schema(
    schema: 'ExerciseInput',
    type: 'object',
    required: ['name', 'description', 'muscle_group'],
    properties: [
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 100, description: 'Unique (ignoring upper/lower case).', example: 'Plank'),
        new OA\Property(property: 'description', type: 'string', maxLength: 1000, example: 'Hold a straight body on forearms and toes.'),
        new OA\Property(property: 'muscle_group', type: 'string', minLength: 2, maxLength: 60, example: 'Core'),
    ]
)]
#[OA\Schema(
    schema: 'Exercise',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 5)]),
        new OA\Schema(ref: '#/components/schemas/ExerciseInput'),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]
#[OA\Schema(
    schema: 'MuscleGroupStat',
    type: 'object',
    properties: [
        new OA\Property(property: 'muscle_group', type: 'string', example: 'Cardio'),
        new OA\Property(property: 'workout_count', type: 'integer', description: 'Number of workouts that trained this muscle group.', example: 3),
    ]
)]

// ---------- Workout exercises ----------
#[OA\Schema(
    schema: 'WorkoutExerciseInput',
    type: 'object',
    required: ['workout_id', 'exercise_id', 'duration', 'sets', 'reps'],
    properties: [
        new OA\Property(property: 'workout_id', type: 'integer', description: 'Existing workout. Cannot change on update.', example: 1),
        new OA\Property(property: 'exercise_id', type: 'integer', description: 'Existing exercise, at most once per workout.', example: 2),
        new OA\Property(property: 'duration', type: 'integer', minimum: 1, maximum: 600, description: 'Minutes.', example: 10),
        new OA\Property(property: 'sets', type: 'integer', minimum: 1, maximum: 100, example: 3),
        new OA\Property(property: 'reps', type: 'integer', minimum: 1, maximum: 1000, example: 15),
        new OA\Property(property: 'weight', type: 'string', format: 'decimal', default: '0', description: 'Kilograms, 0 to 1000. A JSON number is also accepted.', example: '0.00'),
    ]
)]
#[OA\Schema(
    schema: 'WorkoutExercise',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 8)]),
        new OA\Schema(ref: '#/components/schemas/WorkoutExerciseInput'),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]
#[OA\Schema(
    schema: 'WorkoutExerciseDetail',
    description: 'A workout entry joined with its exercise.',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', example: 1)]),
        new OA\Schema(ref: '#/components/schemas/WorkoutExerciseInput'),
        new OA\Schema(properties: [
            new OA\Property(property: 'exercise_name', type: 'string', example: 'Barbell Squat'),
            new OA\Property(property: 'muscle_group', type: 'string', example: 'Legs'),
        ]),
    ]
)]

// ---------- Goals ----------
#[OA\Schema(
    schema: 'GoalInput',
    type: 'object',
    required: ['user_id', 'target_weight', 'start_weight', 'target_date'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', description: 'Existing user. Cannot change on update.', example: 3),
        new OA\Property(property: 'target_weight', type: 'string', format: 'decimal', description: 'Kilograms, 20 to 500, different from start_weight.', example: '59.00'),
        new OA\Property(property: 'start_weight', type: 'string', format: 'decimal', description: 'Kilograms, 20 to 500.', example: '61.25'),
        new OA\Property(property: 'target_date', type: 'string', format: 'date', description: 'Must be in the future (an unchanged date may stay as it is on update).', example: '2027-03-01'),
    ]
)]
#[OA\Schema(
    schema: 'Goal',
    allOf: [
        new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 4)]),
        new OA\Schema(ref: '#/components/schemas/GoalInput'),
        new OA\Schema(properties: [
            new OA\Property(
                property: 'workouts_completed',
                type: 'integer',
                readOnly: true,
                description: 'Calculated by the server: the user\'s workouts from the goal\'s creation date to its target date. A value sent by the client is ignored.',
                example: 3
            ),
        ]),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]

// ---------- Weather ----------
#[OA\Schema(
    schema: 'WeatherDay',
    type: 'object',
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-11-24'),
        new OA\Property(property: 'description', type: 'string', example: 'Overcast'),
        new OA\Property(property: 'temperature_min', type: 'number', nullable: true, description: '°C', example: 1.0),
        new OA\Property(property: 'temperature_max', type: 'number', nullable: true, description: '°C', example: 5.6),
        new OA\Property(property: 'precipitation_probability', type: 'integer', nullable: true, description: '%', example: 8),
        new OA\Property(property: 'wind_speed_max', type: 'number', nullable: true, description: 'km/h', example: 4.7),
        new OA\Property(property: 'outdoor_ok', type: 'boolean', description: 'True when rain chance <= 50%, wind <= 40 km/h, max temperature 0-32 °C and no thunderstorm.', example: true),
        new OA\Property(property: 'advice', type: 'string', example: 'Good conditions for an outdoor workout.'),
    ]
)]
#[OA\Schema(
    schema: 'WeatherForecast',
    type: 'object',
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 3),
        new OA\Property(property: 'city', type: 'string', example: 'Bihać'),
        new OA\Property(property: 'location', type: 'object', properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Bihać'),
            new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Bosnia and Herzegovina'),
            new OA\Property(property: 'latitude', type: 'number', example: 44.81694),
            new OA\Property(property: 'longitude', type: 'number', example: 15.87083),
        ]),
        new OA\Property(property: 'days', type: 'array', items: new OA\Items(ref: '#/components/schemas/WeatherDay')),
        new OA\Property(property: 'source', type: 'string', example: 'Open-Meteo'),
    ]
)]
class DocSetup
{
    #[OA\Get(
        path: '/',
        operationId: 'healthCheck',
        summary: 'Check that the API is running',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The API is running.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'Trackify API is running.'])
            ),
        ]
    )]
    public function health(): void
    {
    }
}
