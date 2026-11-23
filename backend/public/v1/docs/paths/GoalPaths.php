<?php

// OpenAPI documentation for rest/routes/GoalRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class GoalPaths
{
    #[OA\Get(
        path: '/goals',
        operationId: 'getGoalList',
        summary: 'List all goals',
        tags: ['Goals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All goals.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Goal'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getGoalList(): void
    {
    }

    #[OA\Get(
        path: '/goals/{id}',
        operationId: 'getGoal',
        summary: 'Get one goal',
        tags: ['Goals'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Goal id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The goal.',
                content: new OA\JsonContent(ref: '#/components/schemas/Goal')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getGoal(): void
    {
    }

    #[OA\Post(
        path: '/goals',
        operationId: 'createGoal',
        summary: 'Create a goal',
        tags: ['Goals'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GoalInput', example: ['user_id' => 3, 'target_weight' => '59.00', 'start_weight' => '61.25', 'target_date' => '2027-03-01'])
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created goal.',
                content: new OA\JsonContent(ref: '#/components/schemas/Goal')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function createGoal(): void
    {
    }

    #[OA\Put(
        path: '/goals/{id}',
        operationId: 'updateGoal',
        summary: 'Replace a goal (send every required field)',
        tags: ['Goals'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Goal id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GoalInput', example: ['user_id' => 2, 'target_weight' => '77.50', 'start_weight' => '82.00', 'target_date' => '2027-01-31'])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated goal.',
                content: new OA\JsonContent(ref: '#/components/schemas/Goal')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function updateGoal(): void
    {
    }

    #[OA\Delete(
        path: '/goals/{id}',
        operationId: 'deleteGoal',
        summary: 'Delete a goal',
        tags: ['Goals'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Goal id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The goal was deleted.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'Goal deleted.'])
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function deleteGoal(): void
    {
    }

    #[OA\Get(
        path: '/users/{id}/goals',
        operationId: 'getGoalsOfUser',
        summary: 'List one user\'s goals (closest target date first)',
        tags: ['Goals'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The user\'s goals.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Goal'))
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getGoalsOfUser(): void
    {
    }
}
