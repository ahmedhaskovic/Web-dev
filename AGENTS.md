# Trackify Project Rules

## Project

Trackify is a fitness tracking single-page application. It is currently at Milestone 1: static frontend and database planning.

## Technology and planned entities

- Frontend: HTML, CSS, JavaScript, Bootstrap 5, and Chart.js.
- Backend: PHP with FlightPHP and PDO; database: MySQL.
- Roles: `admin` and `member`.
- Planned entities: `users`, `workouts`, `exercises`, `workout_exercises`, and `goals`. See `ERD.png`.
- Planned external feature: Open-Meteo weather information on the dashboard for outdoor workout planning.

## Folder structure

- Frontend views are HTML fragments in `frontend/views/`; shared styles are in `frontend/css/`; shared behavior is in `frontend/js/`; images belong in `frontend/assets/`.
- Backend code belongs in `backend/`: routes, services, DAO, middleware, config, and logs.
- Project documentation belongs in `docs/`.

## Architecture rules

- Keep frontend and backend code separate. Frontend files contain only HTML, CSS, and JavaScript; backend files contain PHP.
- Keep frontend navigation within the SPA; use shared JavaScript rather than adding page-specific inline scripts.
- Backend request flow must be route -> service -> DAO -> database. Only services call DAO classes.
- Use PDO prepared statements for all database queries.
- Keep credentials and keys in `backend/config/config.php`; commit only `backend/config/config.example.php`.
- Never commit runtime logs or the `backend/vendor/` directory.
- Do not add dependencies, alter the planned data model, or claim later-milestone features are implemented without discussing the change.

## Conventions

- PHP classes use PascalCase; JavaScript variables and functions use camelCase.
- Database tables and columns use snake_case.
- Use semantic HTML and responsive Bootstrap layout. Keep shared styling and behavior in their frontend CSS and JavaScript files.
- Surface errors to users appropriately and log technical details on the backend without exposing secrets.

## AI assistant rules

- Read this file before making project changes and stay within the folder boundaries for the task.
- Make small, reviewable changes and explain the files changed.
- Do not invent AI prompts, mistakes, test accounts, API behavior, or completed milestone work in documentation.
- Update `docs/AI_LOG.md` truthfully when AI is used, and ask the student to verify entries that require personal confirmation.
