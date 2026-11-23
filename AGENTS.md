# Trackify Project Rules

## Project

Trackify is a fitness tracking single-page application. Milestone 1 established the static frontend and database plan. Milestone 2 added the MySQL schema and DAO layer. Milestone 3 adds the service layer, FlightPHP REST routes, OpenAPI/Swagger documentation and the Open-Meteo weather endpoint. Authentication (JWT), role middleware, request-logging middleware and the connected frontend are Milestone 4; frontend MVC, validation and deployment are Milestone 5.

## Technology and planned entities

- Frontend: HTML, CSS, JavaScript, Bootstrap 5, and Chart.js.
- Backend: PHP 8.2+ with FlightPHP 3 and PDO; API documentation with swagger-php 5 (PHP 8 attributes, `OpenApi\Attributes`); database: MySQL 8.0.16+ with utf8mb4.
- Schema: `database/trackify.sql`.
- DAOs: `backend/rest/dao/`; shared PDO behavior lives in `BaseDao.php`.
- Services: `backend/rest/services/`; shared CRUD flow lives in `BaseService.php`.
- Routes: `backend/rest/routes/`, one `XxxRoutes.php` per entity plus `WeatherRoutes.php`.
- Test scripts (CLI only): `backend/test.php` (DAOs) and `backend/rest/services/test.php` (business rules).
- Roles: `admin` and `member`.
- Entities: `users`, `workouts`, `exercises`, `workout_exercises`, and `goals`. See `ERD.png`. Milestone 3 added the nullable `users.city` column for the weather feature.
- External API: Open-Meteo geocoding and forecast (no API key), used only by `WeatherService` for `GET /users/{id}/weather`; shown on the dashboard in Milestone 4.

## Folder structure

- Frontend views are HTML fragments in `frontend/views/`; styles and JavaScript are in `frontend/assets/css/` and `frontend/assets/js/`; images belong in `frontend/assets/`.
- Frontend API services and utilities belong in `frontend/services/` and `frontend/utils/`.
- Backend entry point is `backend/index.php` (service registration, error handlers, route files, `Flight::start()`); REST routes, services, and DAO belong in `backend/rest/`; middleware, config, and logs are in `backend/` (`backend/middleware/` stays where it is and is filled in Milestone 4).
- Shared backend helpers are in `backend/utils/`: `exceptions.php`, `Validator.php`, `Logger.php`.
- OpenAPI files are in `backend/public/v1/docs/`: `index.html` (Swagger UI, pinned CDN version), `swagger.php`, `doc_setup.php` (info, server, shared responses, schemas) and `paths/XxxPaths.php` (one per routes file).
- Database schema belongs in `database/`.
- Project documentation belongs in `docs/`.

## Architecture rules

- Keep frontend and backend code separate. Frontend files contain only HTML, CSS, and JavaScript; backend files contain PHP.
- Keep frontend navigation within the SPA; use shared JavaScript rather than adding page-specific inline scripts.
- Backend request flow must be route -> service -> DAO -> database. Each layer calls only the layer directly below it. Only services call DAO classes.
- Routes read the request, call one service method and return JSON with `Flight::json()`. Routes contain no SQL, DAO calls, business rules or try/catch. Request bodies are read with `Flight::jsonBody()`.
- Services contain all validation and business rules (using `Validator`), never write SQL and never use `Flight::request()`. A broken rule throws an `HttpException` subclass (`ValidationException` 400, `NotFoundException` 404, `ExternalServiceException` 503; `UnauthorizedException` 401 and `ForbiddenException` 403 are reserved for Milestone 4). Services never return `false` or error arrays.
- The global error handler in `index.php` turns every exception into `{"error": "..."}`. Unexpected errors return a generic 500 and are logged to `backend/logs/error.log` with class, message and `file:line`; responses never contain SQL, stack traces or file paths.
- Each service lists its writable `$fields`; `BaseService::prepare()` drops everything else. Server-calculated values (password hashes, `goals.workouts_completed`, ids, timestamps) are never accepted from the client.
- A missing foreign key returns 400, and a parent that still has children cannot be deleted (400); never let a raw database error reach the client.
- The third-party API is called only from `WeatherService`, with connect and total timeouts. Failures are logged and become a 503 with a friendly message.
- Every route has exactly one OpenAPI operation and vice versa. Input schemas exclude server-calculated fields; output schemas exclude secrets.
- Use PDO prepared statements for all database queries.
- Keep credentials and keys in `backend/config/config.php`; commit only `backend/config/config.example.php`.
- Never commit runtime logs or the `backend/vendor/` directory.
- Keep DAO SQL in DAO classes, not services, routes, or frontend files.
- DAO writable-column allowlists must exclude primary keys and database-generated timestamps.
- The user DAO's list and get-by-id queries must never return password hashes, and no endpoint may return one.
- Do not add dependencies beyond FlightPHP and swagger-php, alter the data model, or claim later-milestone features are implemented without discussing the change.

## Conventions

- PHP classes use PascalCase and each file is named after its class; JavaScript variables and functions use camelCase.
- PHP CRUD and finder methods in DAOs and services use snake_case as in the course labs (`get_all`, `get_by_id`, `get_by_user`, `count_by_role`); internal helpers use camelCase (`getConnection`, `loadConfig`). Existing Milestone 2 helpers (`only_columns`, `query_unique`, `tableName`) keep their names.
- REST paths are lowercase plural nouns, with hyphens for two words (`/workout-exercises`); ids use `@id:[0-9]+`.
- Database tables and columns use snake_case.
- Use semantic HTML and responsive Bootstrap layout. Keep shared styling and behavior in their frontend CSS and JavaScript files.
- Surface errors to users appropriately and log technical details on the backend without exposing secrets.

## AI assistant rules

- Read this file before making project changes and stay within the folder boundaries for the task.
- Make small, reviewable changes and explain the files changed.
- Do not invent AI prompts, mistakes, test accounts, API behavior, or completed milestone work in documentation.
- Update `docs/AI_LOG.md` truthfully when AI is used, and ask the student to verify entries that require personal confirmation.
