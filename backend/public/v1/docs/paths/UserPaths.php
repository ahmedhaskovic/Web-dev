<?php

// OpenAPI documentation for rest/routes/UserRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class UserPaths
{
    #[OA\Get(
        path: '/users',
        operationId: 'getUserList',
        summary: 'List all users',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All users. Password hashes are never returned.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getUserList(): void
    {
    }

    #[OA\Get(
        path: '/users/{id}',
        operationId: 'getUser',
        summary: 'Get one user',
        tags: ['Users'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The user.',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function getUser(): void
    {
    }

    #[OA\Post(
        path: '/users',
        operationId: 'createUser',
        summary: 'Create a user',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UserInput', example: ['name' => 'Ena Begić', 'email' => 'ena@trackify.local', 'password' => 'Secret123', 'role' => 'member', 'current_weight' => '64.50', 'city' => 'Tuzla'])
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created user.',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function createUser(): void
    {
    }

    #[OA\Put(
        path: '/users/{id}',
        operationId: 'updateUser',
        summary: 'Replace a user (send every required field)',
        tags: ['Users'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UserInput', example: ['name' => 'Marko Petrovic', 'email' => 'marko@trackify.local', 'role' => 'member', 'current_weight' => '81.20', 'city' => 'Mostar'])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated user.',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function updateUser(): void
    {
    }

    #[OA\Delete(
        path: '/users/{id}',
        operationId: 'deleteUser',
        summary: 'Delete a user',
        tags: ['Users'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The user was deleted.',
                content: new OA\JsonContent(ref: '#/components/schemas/Message', example: ['message' => 'User deleted.'])
            ),
            new OA\Response(
                response: 400,
                description: 'The user still has workouts or goals, or is the last admin.',
                content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'This user still has 3 workout(s) and 2 goal(s). Delete them first.'])
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    public function deleteUser(): void
    {
    }
}
