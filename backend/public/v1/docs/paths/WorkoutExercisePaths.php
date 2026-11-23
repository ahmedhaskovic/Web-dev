<?php

// OpenAPI documentation for rest/routes/WorkoutExerciseRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class WorkoutExercisePaths
{
    #[OA\Get(
        path: '/workout-exercises',
        operationId: 'getWorkoutExerciseList',
        summary: 'List all workout exercises',
        tags: ['Workout exercises'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All workout exercises.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutExercise'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getWorkoutExerciseList(): void
    {
    }

    #[OA\Get(
        path: '/workout-exercises/{id}',
        operationId: 'getWorkoutExercise',
        summary: 'Get one workout exercise',
        tags: ['Workout exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The workout exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getWorkoutExercise(): void
    {
    }

    #[OA\Post(
        path: '/workout-exercises',
        operationId: 'createWorkoutExercise',
        summary: 'Create a workout exercise',
        tags: ['Workout exercises'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExerciseInput', example: ['workout_id' => 1, 'exercise_id' => 2, 'duration' => 10, 'sets' => 3, 'reps' => 15, 'weight' => '0.00'])
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created workout exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function createWorkoutExercise(): void
    {
    }

    #[OA\Put(
        path: '/workout-exercises/{id}',
        operationId: 'updateWorkoutExercise',
        summary: 'Replace a workout exercise (send every required field)',
        tags: ['Workout exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExerciseInput', example: ['workout_id' => 1, 'exercise_id' => 1, 'duration' => 35, 'sets' => 5, 'reps' => 5, 'weight' => '55.00'])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated workout exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function updateWorkoutExercise(): void
    {
    }

    #[OA\Delete(
        path: '/workout-exercises/{id}',
        operationId: 'deleteWorkoutExercise',
        summary: 'Delete a workout exercise',
        tags: ['Workout exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The workout exercise was deleted.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'Workout exercise deleted.'])
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function deleteWorkoutExercise(): void
    {
    }

    #[OA\Get(
        path: '/workouts/{id}/exercises',
        operationId: 'getExercisesOfWorkout',
        summary: 'List the exercises of one workout',
        tags: ['Workout exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The workout entries with exercise name and muscle group.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutExerciseDetail'))
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getExercisesOfWorkout(): void
    {
    }
}
