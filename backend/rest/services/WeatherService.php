<?php

require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/UserService.php';

// Outdoor workout forecast for a user's city, from the free Open-Meteo API (no API key).
// This is the only class that calls the third-party API.
//   1. Geocoding API: city name -> latitude/longitude
//   2. Forecast API: latitude/longitude -> daily weather
// Any failure is written to error.log and becomes a 503 with a friendly message,
// so the rest of the application keeps working when Open-Meteo is down.

class WeatherService
{
    private const FRIENDLY_ERROR = 'Weather information is temporarily unavailable. Please try again later.';

    // Limits used to decide whether a day is good for an outdoor workout.
    private const MAX_RAIN_PROBABILITY = 50; // %
    private const MAX_WIND_SPEED = 40;       // km/h
    private const MIN_TEMPERATURE = 0;       // °C, daily maximum
    private const MAX_TEMPERATURE = 32;      // °C, daily maximum

    // WMO weather codes used by Open-Meteo.
    private const WEATHER_CODES = [
        0 => 'Clear sky', 1 => 'Mainly clear', 2 => 'Partly cloudy', 3 => 'Overcast',
        45 => 'Fog', 48 => 'Freezing fog',
        51 => 'Light drizzle', 53 => 'Drizzle', 55 => 'Heavy drizzle',
        56 => 'Freezing drizzle', 57 => 'Heavy freezing drizzle',
        61 => 'Light rain', 63 => 'Rain', 65 => 'Heavy rain',
        66 => 'Freezing rain', 67 => 'Heavy freezing rain',
        71 => 'Light snow', 73 => 'Snow', 75 => 'Heavy snow', 77 => 'Snow grains',
        80 => 'Light rain showers', 81 => 'Rain showers', 82 => 'Violent rain showers',
        85 => 'Snow showers', 86 => 'Heavy snow showers',
        95 => 'Thunderstorm', 96 => 'Thunderstorm with hail', 99 => 'Thunderstorm with heavy hail',
    ];

    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        BaseDao::loadConfig();
        $this->userService = $userService ?? new UserService();
    }

    public function get_forecast_for_user($userId, $days = null): array
    {
        // Open-Meteo can forecast up to 16 days; the dashboard needs at most a week.
        $days = $days ?? '3';
        Validator::integer($days, 'days', 1, 7);
        $days = (int) $days;

        $user = $this->userService->get_by_id($userId);
        if ($user['city'] === null) {
            throw new ValidationException('This user has no city set. Add a city to see the weather forecast.');
        }

        $location = $this->find_location($user['city']);
        $daily = $this->fetch_daily_forecast($location['latitude'], $location['longitude'], $days);

        $forecast = [];
        for ($i = 0; $i < $days; $i++) {
            $forecast[] = $this->build_day(
                $daily['time'][$i],
                $daily['weather_code'][$i],
                $daily['temperature_2m_min'][$i],
                $daily['temperature_2m_max'][$i],
                $daily['precipitation_probability_max'][$i],
                $daily['wind_speed_10m_max'][$i]
            );
        }

        Logger::info("Weather API success: city={$user['city']} days={$days}");

        return [
            'user_id' => (int) $user['id'],
            'city' => $user['city'],
            'location' => $location,
            'days' => $forecast,
            'source' => 'Open-Meteo',
        ];
    }

    private function find_location(string $city): array
    {
        $response = $this->get_json(Config::WEATHER_GEOCODING_URL(), [
            'name' => $city,
            'count' => 1,
            'language' => 'en',
            'format' => 'json',
        ]);

        // Open-Meteo leaves out "results" when nothing matches the name.
        if (!isset($response['results']) || $response['results'] === []) {
            Logger::error("Weather API: no location found for city={$city}");
            throw new NotFoundException("No weather location was found for the city \"{$city}\".");
        }

        $place = $response['results'][0];
        if (!is_array($place) || !is_numeric($place['latitude'] ?? null) || !is_numeric($place['longitude'] ?? null)) {
            $this->fail("geocoding response has no coordinates for city={$city}");
        }

        return [
            'name' => (string) ($place['name'] ?? $city),
            'country' => isset($place['country']) ? (string) $place['country'] : null,
            'latitude' => (float) $place['latitude'],
            'longitude' => (float) $place['longitude'],
        ];
    }

    private function fetch_daily_forecast(float $latitude, float $longitude, int $days): array
    {
        $response = $this->get_json(Config::WEATHER_FORECAST_URL(), [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max',
            'timezone' => 'auto',
            'forecast_days' => $days,
        ]);

        // Every daily list must exist and contain one value per requested day.
        $daily = $response['daily'] ?? null;
        $keys = ['time', 'weather_code', 'temperature_2m_min', 'temperature_2m_max',
            'precipitation_probability_max', 'wind_speed_10m_max'];
        foreach ($keys as $key) {
            if (!is_array($daily) || !isset($daily[$key]) || !is_array($daily[$key]) || count($daily[$key]) < $days) {
                $this->fail("forecast response is missing daily.{$key}");
            }
        }

        return $daily;
    }

    private function build_day($date, $code, $minTemperature, $maxTemperature, $rainProbability, $windSpeed): array
    {
        $code = $code === null ? null : (int) $code;
        $warnings = [];
        if ($rainProbability !== null && $rainProbability > self::MAX_RAIN_PROBABILITY) {
            $warnings[] = 'High chance of rain or snow.';
        }
        if ($windSpeed !== null && $windSpeed > self::MAX_WIND_SPEED) {
            $warnings[] = 'Strong wind.';
        }
        if ($maxTemperature !== null && $maxTemperature < self::MIN_TEMPERATURE) {
            $warnings[] = 'Freezing temperatures.';
        }
        if ($maxTemperature !== null && $maxTemperature > self::MAX_TEMPERATURE) {
            $warnings[] = 'Very hot, avoid training at midday.';
        }
        if ($code !== null && $code >= 95) {
            $warnings[] = 'Thunderstorms expected.';
        }

        return [
            'date' => (string) $date,
            'description' => self::WEATHER_CODES[$code] ?? 'Unknown',
            'temperature_min' => $minTemperature === null ? null : (float) $minTemperature,
            'temperature_max' => $maxTemperature === null ? null : (float) $maxTemperature,
            'precipitation_probability' => $rainProbability === null ? null : (int) $rainProbability,
            'wind_speed_max' => $windSpeed === null ? null : (float) $windSpeed,
            'outdoor_ok' => $warnings === [],
            'advice' => $warnings === []
                ? 'Good conditions for an outdoor workout.'
                : implode(' ', $warnings) . ' Consider an indoor workout.',
        ];
    }

    // Sends a GET request and returns the decoded JSON object, or throws a 503.
    private function get_json(string $url, array $parameters): array
    {
        // http_build_query URL-encodes every value, including non-ASCII city names such as "Bihać".
        $fullUrl = $url . '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        $timeout = Config::WEATHER_TIMEOUT();

        $curl = curl_init($fullUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout, // seconds to connect
            CURLOPT_TIMEOUT => $timeout,        // seconds for the whole request
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'Trackify/1.0',
        ]);

        $body = curl_exec($curl);
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        // Only the host and path are logged, never the full query string.
        $target = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        if ($body === false) {
            if ($errorNumber === CURLE_OPERATION_TIMEDOUT) {
                $this->fail("timeout after {$timeout}s calling {$target}");
            }
            $this->fail("connection failed calling {$target}: cURL error {$errorNumber} {$errorMessage}");
        }

        if ($status < 200 || $status >= 300) {
            $this->fail("HTTP {$status} from {$target}");
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            $this->fail("invalid JSON from {$target}");
        }

        return $data;
    }

    // "never" means this method always throws and never returns.
    private function fail(string $details): never
    {
        Logger::error('Weather API failure: ' . $details);

        throw new ExternalServiceException(self::FRIENDLY_ERROR);
    }
}
