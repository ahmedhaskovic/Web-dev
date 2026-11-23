<?php

// OpenAPI documentation for rest/routes/WeatherRoutes.php: one operation per route.

use OpenApi\Attributes as OA;

class WeatherPaths
{
    #[OA\Get(
        path: '/users/{id}/weather',
        operationId: 'getWeatherForUser',
        summary: 'Outdoor workout forecast for the user\'s city',
        description: 'Looks up the user\'s city with the Open-Meteo geocoding API, then loads the daily forecast. No API key is needed.',
        tags: ['Weather'],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User id', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 3),
            new OA\QueryParameter(name: 'days', description: 'Number of forecast days, 1 to 7.', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 7, default: 3), example: 3),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daily forecast with an outdoor-workout recommendation.',
                content: new OA\JsonContent(ref: '#/components/schemas/WeatherForecast')
            ),
            new OA\Response(
                response: 400,
                description: 'days is not 1-7, or the user has no city.',
                content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'days must be at most 7.'])
            ),
            new OA\Response(
                response: 404,
                description: 'The user does not exist, or Open-Meteo knows no place with the user\'s city name.',
                content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'User not found.'])
            ),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
            new OA\Response(
                response: 503,
                description: 'Open-Meteo timed out, failed or returned unusable data. The details are in the error log.',
                content: new OA\JsonContent(ref: '#/components/schemas/Error', example: ['error' => 'Weather information is temporarily unavailable. Please try again later.'])
            ),
        ]
    )]
    public function getWeatherForUser(): void
    {
    }
}
