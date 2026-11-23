<?php

// OpenAPI documentation for rest/routes/ExerciseRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class ExercisePaths
{
    #[OA\Get(
        path: '/exercises/muscle-groups',
        operationId: 'getMuscleGroupStats',
        summary: 'Count workouts per muscle group',
        tags: ['Exercises'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Number of workouts that trained each muscle group.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/MuscleGroupStat'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getMuscleGroupStats(): void
    {
    }

    #[OA\Get(
        path: '/exercises',
        operationId: 'getExerciseList',
        summary: 'List all exercises',
        tags: ['Exercises'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All exercises.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Exercise'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getExerciseList(): void
    {
    }

    #[OA\Get(
        path: '/exercises/{id}',
        operationId: 'getExercise',
        summary: 'Get one exercise',
        tags: ['Exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/Exercise')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getExercise(): void
    {
    }

    #[OA\Post(
        path: '/exercises',
        operationId: 'createExercise',
        summary: 'Create a exercise',
        tags: ['Exercises'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ExerciseInput', example: ['name' => 'Plank', 'description' => 'Hold a straight body on forearms and toes.', 'muscle_group' => 'Core'])
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/Exercise')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function createExercise(): void
    {
    }

    #[OA\Put(
        path: '/exercises/{id}',
        operationId: 'updateExercise',
        summary: 'Replace a exercise (send every required field)',
        tags: ['Exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ExerciseInput', example: ['name' => 'Push-up', 'description' => 'Bodyweight press for chest, shoulders and triceps.', 'muscle_group' => 'Chest'])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated exercise.',
                content: new OA\JsonContent(ref: '#/components/schemas/Exercise')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function updateExercise(): void
    {
    }

    #[OA\Delete(
        path: '/exercises/{id}',
        operationId: 'deleteExercise',
        summary: 'Delete a exercise',
        tags: ['Exercises'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Exercise id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The exercise was deleted.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'Exercise deleted.'])
            ),
            new OA\Response(
                response: 400,
                description: 'The exercise is still used in a workout.',
                content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'This exercise is used in 2 workout entries and cannot be deleted.'])
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function deleteExercise(): void
    {
    }
}
