# Trackify

Trackify is a fitness tracking single-page application. Members log workouts and the exercises inside them, set weight goals, and (from Milestone 4) see an outdoor-workout weather forecast on the dashboard. Admins are planned to manage the shared exercise catalog and all users once role checks are added in Milestone 4.

| Milestone | Status |
|---|---|
| M1: static SPA frontend, ERD | done |
| M2: MySQL schema and DAO layer | done |
| M3: services, REST routes, OpenAPI/Swagger, external API | done (this branch) |
| M4: JWT authentication, role middleware, logging middleware, connected frontend | planned |
| M5: frontend MVC, validations, deployment | planned |

The frontend is still static; connecting it to the API is Milestone 4.

## Technology

- Frontend: HTML, CSS, JavaScript, Bootstrap 5, Chart.js
- Backend: PHP 8.2+, FlightPHP 3, PDO, swagger-php 5 (OpenAPI attributes)
- Database: MySQL 8.0.16+ (needed for `CHECK` constraints), utf8mb4
- External API: [Open-Meteo](https://open-meteo.com) geocoding and forecast (free, no API key)

## Entities and relationships

| Table | Main columns | Relationships |
|---|---|---|
| `users` | name, email (unique), password (bcrypt hash), role, current_weight, city | has many workouts and goals |
| `workouts` | user_id, date, total_calories, notes | belongs to a user; has many workout_exercises |
| `exercises` | name (unique), description, muscle_group | used by many workout_exercises |
| `workout_exercises` | workout_id, exercise_id, duration, sets, reps, weight | join table between workouts and exercises |
| `goals` | user_id, target_weight, start_weight, target_date, workouts_completed | belongs to a user |

`workouts` and `exercises` are many-to-many through `workout_exercises`. Every table has an auto-increment `id` and generated `created_at`/`updated_at` timestamps. See `ERD.png` (Milestone 3 added the `users.city` column).

## Roles and test accounts

Roles: `admin` and `member`. The sample data contains these accounts, all with the local-only password `TrackifyDemo!2026`:

| Role | Email | City |
|---|---|---|
| admin | `admin@trackify.local` | Sarajevo |
| member | `marko@trackify.local` | Mostar |
| member | `lejla@trackify.local` | Bihać |

Login and role checks are Milestone 4; in Milestone 3 every endpoint is open.

## External API: outdoor workout forecast

`GET /users/{id}/weather?days=1..7` reads the user's city, finds it with the Open-Meteo geocoding API, loads the daily forecast and returns, per day: description, min/max temperature, chance of rain, wind, `outdoor_ok` and a short advice text. In Milestone 4 this fills the "Outdoor workout conditions" card on the dashboard.

- No API key is needed; the URLs and the 5-second timeout are in `backend/config/config.php`.
- Timeouts, connection errors, non-2xx responses, invalid JSON and missing fields are written to `backend/logs/error.log` and return `503` with a friendly message. The rest of the API keeps working.
- A user without a city or `days` outside 1-7 returns `400`; a city Open-Meteo cannot find returns `404`.
- Each successful call writes one line to `backend/logs/info.log`.

## Run locally

1. **Database.** Start MySQL. If you imported an older version of `trackify.sql`, drop the old database first (`DROP DATABASE trackify;`), because Milestone 3 added a column. Then import the file as UTF-8:
   - phpMyAdmin: **Import** tab, choose `database/trackify.sql`, character set `utf-8`.
   - Command line: `mysql -u root --default-character-set=utf8mb4 < database/trackify.sql`

2. **Configuration.** Copy the template and adjust the database settings if they differ from the XAMPP defaults (`127.0.0.1:3306`, database `trackify`, user `root`, empty password). Every value can also be set as an environment variable of the same name.

   ```powershell
   Copy-Item .\backend\config\config.example.php .\backend\config\config.php
   ```

   `backend/config/config.php` is ignored by Git; only `config.example.php` is committed.

3. **Dependencies.** From `backend/` run `composer install` (installs FlightPHP and swagger-php into `backend/vendor/`, which is ignored by Git).

4. **Serve the API.**
   - XAMPP/Apache: put the project in `htdocs` (for example `htdocs/trackify`) with `mod_rewrite` enabled. The API is then at `http://localhost/trackify/backend/` and `backend/.htaccess` blocks direct access to `config`, `logs`, `vendor`, `utils`, `rest`, `middleware` and the test scripts.
   - Quick alternative for development only (no `.htaccess` protection): `php -S 127.0.0.1:8080 -t backend`, API at `http://127.0.0.1:8080/`.

5. **Swagger documentation:** `http://localhost/trackify/backend/public/v1/docs/` (or `http://127.0.0.1:8080/public/v1/docs/` with the PHP server). The server URL in the document is worked out automatically from the folder.

6. **Static frontend:** `php -S 127.0.0.1:8000 -t frontend`, then open <http://127.0.0.1:8000>.

Deployed application: pending (Milestone 5).

## Tests

Run from the project root:

```powershell
php .\backend\test.php
php .\backend\rest\services\test.php
```

- `backend/test.php` checks the DAO layer.
- `backend/rest/services/test.php` tries to break every business rule (validation, uniqueness, foreign keys, delete protection, calculated fields). It runs inside one transaction that is always rolled back, so it leaves no data behind, and it exits with code 1 if any check fails. It cannot be run from a browser.

## API endpoints

All responses are JSON. Errors look like `{"error": "..."}`.

| Method | Path | Success | Errors |
|---|---|---|---|
| GET | `/` | 200 health check | |
| GET | `/users`, `/workouts`, `/exercises`, `/workout-exercises`, `/goals` | 200 list | 500 |
| GET | `/{entity}/{id}` | 200 | 404 |
| POST | `/{entity}` | 201 created row | 400 |
| PUT | `/{entity}/{id}` (full replace) | 200 updated row | 400, 404 |
| DELETE | `/{entity}/{id}` | 200 `{"message": "... deleted."}` | 400, 404 |
| GET | `/users/{id}/workouts` | 200 the user's workouts, newest first | 404 |
| GET | `/users/{id}/goals` | 200 the user's goals | 404 |
| GET | `/workouts/{id}/exercises` | 200 the workout's exercises with names | 404 |
| GET | `/exercises/muscle-groups` | 200 workouts per muscle group | |
| GET | `/users/{id}/weather?days=3` | 200 forecast | 400, 404, 503 |

An unknown URL returns a JSON `404`; a known URL with the wrong method returns a JSON `405` with an `Allow` header. Unexpected errors return a generic `500` and are logged to `backend/logs/error.log` without exposing SQL, stack traces or file paths.

## Business rules

- **Users:** name 2-100 characters; email valid, unique and stored in lowercase; password 8-72 characters with a letter and a digit, stored only as a bcrypt hash and never returned (on update it is optional and kept when left out); role `admin` or `member` (default `member`); weight 20-500 kg; optional city. The last admin cannot be deleted or changed to a member. A user who still has workouts or goals cannot be deleted.
- **Workouts:** the user must exist and cannot change; the date is a real `YYYY-MM-DD` date, not in the future and not before 2000; calories 0-10000 (default 0); notes up to 1000 characters. Deleting a workout also deletes its exercise entries.
- **Exercises:** name unique (ignoring case), description and muscle group required. An exercise used in any workout cannot be deleted.
- **Workout exercises:** the workout and exercise must exist; an exercise appears at most once per workout; an entry cannot move to another workout; duration 1-600 minutes, sets 1-100, reps 1-1000, weight 0-1000 kg (default 0).
- **Goals:** the user must exist and cannot change; both weights 20-500 kg and different from each other; a new or changed target date must be in the future. `workouts_completed` is calculated by the server (the user's workouts from the goal's creation date to its target date) and recalculated whenever the user's workouts change; a client value is ignored.
- **All entities:** only known fields are accepted (ids, timestamps and calculated values are ignored), arrays or objects in place of a single value are rejected, names and notes cannot contain `<` or `>`.

## Project structure

```text
frontend/                 static SPA (connected in Milestone 4)
backend/
  index.php               Flight entry point: services, error handlers, routes
  .htaccess               rewrite to index.php, 403 for private folders
  composer.json / .lock   FlightPHP and swagger-php
  config/                 config.example.php (committed), config.php (private)
  utils/                  exceptions.php, Validator.php, Logger.php
  rest/
    routes/               one XxxRoutes.php per entity + WeatherRoutes.php
    services/             BaseService, one XxxService per entity, WeatherService, test.php
    dao/                  BaseDao and one DAO per entity
  middleware/             filled in Milestone 4
  public/v1/docs/         Swagger UI (index.html), swagger.php, doc_setup.php, paths/
  logs/                   info.log and error.log (ignored by Git)
  test.php                DAO tests
database/trackify.sql     schema and sample data
docs/AI_LOG.md            AI usage log
AGENTS.md                 rules for AI coding assistants
```
