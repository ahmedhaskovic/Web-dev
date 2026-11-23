<?php

// External API (Open-Meteo): outdoor workout forecast for the user's city.
// Optional query parameter: ?days=1..7 (default 3).
Flight::route('GET /users/@id:[0-9]+/weather', function ($id) {
    Flight::json(Flight::weatherService()->get_forecast_for_user($id, Flight::request()->query['days']));
});
