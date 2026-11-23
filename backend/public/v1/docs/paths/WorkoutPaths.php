<?php

// OpenAPI documentation for rest/routes/WorkoutRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class WorkoutPaths
{
    #[OA\Get(
        path: '/workouts',
        operationId: 'getWorkoutList',
        summary: 'List all workouts',
        tags: ['Workouts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All workouts.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Workout'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getWorkoutList(): void
    {
    }

    #[OA\Get(
        path: '/workouts/{id}',
        operationId: 'getWorkout',
        summary: 'Get one workout',
        tags: ['Workouts'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The workout.',
                content: new OA\JsonContent(ref: '#/components/schemas/Workout')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getWorkout(): void
    {
    }

    #[OA\Post(
        path: '/workouts',
        operationId: 'createWorkout',
        summary: 'Create a workout',
        tags: ['Workouts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/WorkoutInput', example: ['user_id' => 2, 'date' => '2026-11-20', 'total_calories' => 350, 'notes' => 'Interval run'])
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created workout.',
                content: new OA\JsonContent(ref: '#/components/schemas/Workout')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function createWorkout(): void
    {
    }

    #[OA\Put(
        path: '/workouts/{id}',
        operationId: 'updateWorkout',
        summary: 'Replace a workout (send every required field)',
        tags: ['Workouts'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/WorkoutInput', example: ['user_id' => 2, 'date' => '2026-10-19', 'total_calories' => 340, 'notes' => 'Lower-body strength session'])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated workout.',
                content: new OA\JsonContent(ref: '#/components/schemas/Workout')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function updateWorkout(): void
    {
    }

    #[OA\Delete(
        path: '/workouts/{id}',
        operationId: 'deleteWorkout',
        summary: 'Delete a workout',
        tags: ['Workouts'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Workout id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The workout was deleted.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'Workout deleted.'])
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function deleteWorkout(): void
    {
    }

    #[OA\Get(
        path: '/users/{id}/workouts',
        operationId: 'getWorkoutsOfUser',
        summary: 'List one user\'s workouts (newest first)',
        tags: ['Workouts'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The user\'s workouts.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Workout'))
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getWorkoutsOfUser(): void
    {
    }
}
