# Trackify

Fitness tracking single-page application. The current milestone includes the static frontend and draft ERD; backend features are planned for later milestones.

## Project structure

```text
frontend/
  index.html
  assets/
    css/
    js/
  views/
  services/
  utils/
backend/
  index.php
  .htaccess
  composer.json
  composer.lock
  config/
  rest/
    routes/
    services/
    dao/
  test.php
  middleware/
  logs/
database/
  trackify.sql
docs/
.gitignore
README.md
```

`backend/vendor/` is generated locally by Composer and is intentionally excluded from Git. Install the locked PHP dependencies from `backend/` with `composer install`.

## Database schema (Milestone 2)

`database/trackify.sql` creates the `trackify` MySQL database and its five tables:

- `users`: account name, unique email, bcrypt password hash, role and current weight.
- `workouts`: a user's workout date, total calories and notes.
- `exercises`: the exercise catalog, including description and muscle group.
- `workout_exercises`: a join table linking workouts and exercises, with duration, sets, reps and weight.
- `goals`: a user's starting and target weight, target date and completed-workout count.

All tables use an auto-increment integer primary key and generated creation/update timestamps. Foreign keys maintain the user, workout and exercise relationships. `CHECK` constraints enforce supported roles and non-negative or positive measurement values. Use MySQL 8.0.16 or later so the database enforces `CHECK` constraints.

The SQL file seeds several rows in every table. The seeded admin and member accounts use the sample-only password `TrackifyDemo!2026`; the stored values are bcrypt hashes. Do not reuse this password outside local development.

## Configure and test the database

1. Start MySQL. Import `database/trackify.sql` into the MySQL server using phpMyAdmin's **Import** tab, or run `mysql -u root < database/trackify.sql` in Command Prompt when the MySQL client is on your `PATH`.

2. Copy the example configuration. Set the database environment variables if your local MySQL credentials differ from the XAMPP defaults (`127.0.0.1`, port `3306`, database `trackify`, user `root`, empty password):

   ```powershell
   Copy-Item .\backend\config\config.example.php .\backend\config\config.php
   $env:DB_HOST = "127.0.0.1"
   $env:DB_PORT = "3306"
   $env:DB_NAME = "trackify"
   $env:DB_USER = "root"
   $env:DB_PASSWORD = ""
   ```

   `backend/config/config.php` contains local settings and is excluded from Git; commit only `config.example.php`.

3. Run the DAO smoke tests from the project root:

   ```powershell
   php .\backend\test.php
   ```

   The test exercises CRUD methods for all five DAOs, the user email lookup, the join and grouped queries, and transaction rollback/commit. Temporary test data is rolled back.

## Run the static frontend

From the project root, run `php -S 127.0.0.1:8000 -t frontend` and open <http://127.0.0.1:8000>.

The REST API, authentication, and external API integrations are planned for subsequent milestones.