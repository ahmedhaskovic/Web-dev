# AI Log

## Milestone 1

### Tools and tasks

- **Work already in place before this AI-assisted pass:** the Trackify project, its initial frontend screens and styling, static demo content, image assets, and draft ERD. These were the starting point for the milestone review, not generated from scratch in this session.
- **Student's role:** supplied the existing project and milestone scope. The student is responsible for reviewing, understanding, and deciding which proposed changes to keep before submission.
- **Tool:** Copilot AI assistant in VS Code; the model name was not recorded in this session.
- **Task:** Check the pre-existing Trackify project against the supplied milestone and lab PDFs and help close identified M1 gaps.
- **Main prompt excerpt:** “I already made the project, but need help making it fullfill all the milestone 1 requirements…”
- **AI assistance:** limited to this M1 pass: reviewed the rubric, reorganized the existing frontend into the required folders, repaired SPA navigation and dashboard interactions, added the missing static views, and scaffolded the required project and AI documentation. The existing project concept, initial screens, styling, assets, and ERD were already in place and were not created in this session.

### Where the AI was wrong or needed correction

1. The first local PDF-extraction script wrote extracted typographic characters using the Windows console's default encoding and failed with a `UnicodeEncodeError`. The failure was visible in the command output. The script was corrected to write UTF-8 output; the PDFs remained local.
2. An initial multi-file patch expected an HTML script block that did not match the actual `frontend/index.html` contents, so the patch failed and made no changes. The failure was checked, and the edits were split into smaller patches using the file's actual contents.

These are the two concrete issues encountered during this AI-assisted task. Add any further code-generation mistakes you find during your own review, including what was wrong, how you identified it, and how you corrected it.

## Milestone 3

### Tools and tasks

- **Tool:** Claude Code (Anthropic), run as a cloud session from claude.ai. The exact model name is not recorded in this file.
- **Student's role:** wrote the Milestone 3 task brief (scope, architecture rules, naming, verification checklist), asked the AI to inspect the project and present a plan before writing any code, and approved that plan. The student made these decisions: add a nullable `users.city` column for the weather feature; have the server calculate `goals.workouts_completed`; block deleting a user who still has workouts or goals, while deleting a workout also deletes its exercise entries; create a local, Git-ignored `config.php` for testing.
- **AI assistance:** the AI wrote all Milestone 3 code and documentation in this branch: `utils/` (exceptions, Validator, Logger), the DAO additions (`loadConfig`, `find_where`, `count_where`, small finders), `BaseService` and the six services, the route files, `index.php` (error, 404 and 405 handlers), the Open-Meteo `WeatherService`, the OpenAPI attribute files and Swagger UI page, `.htaccess`, the service test script, the schema change for `users.city`, and the updates to README.md and AGENTS.md. It also ran the tests listed below in its own environment (MySQL 8.0, Apache with mod_php, headless Chromium).
- **Most important prompts (excerpts):**
  - "Your job is to finish **Milestone 3 only**: services, FlightPHP routes, OpenAPI/Swagger documentation, and one external API."
  - "Phase A: Inspect (read-only, no edits) … Phase B: Plan, then stop for approval … **Do not write code until I approve the plan.**"
  - "Never fabricate. Do not claim something works unless you ran it."
  - "approve" (accepting the four recommended decisions above)

### Where the AI was wrong or produced poor code

1. **Wrong library version.** The first `composer require zircote/swagger-php` installed version 7.8.0. The AI had planned to use `OpenApi\Attributes` and `OpenApi\Generator`, which version 7 marks as deprecated and removes in 8.0. *Noticed:* by reading the installed package's source and migration guide before writing any documentation code. *Fixed:* pinned `zircote/swagger-php:^5.0` (5.8.3), where those classes are the stable API.
2. **A test that passed for the wrong reason.** In the first run of `rest/services/test.php`, the "password is required" check passed, but the error message was "A user with this email already exists." The test reused an email that was already taken, so a different rule threw the same `ValidationException`. *Noticed:* by reading the printed message next to the PASS. *Fixed:* gave the test its own email and extended `attempt()` with an optional expected-message check, also used for two other checks where several rules could fire.
3. **Wrong `Allow` header on 405 responses.** The first `methodNotFound` handler used only the route Flight passed in, so `PATCH /users/1` answered `Allow: DELETE, OPTIONS` even though GET and PUT also exist. *Noticed:* in the first HTTP smoke test. *Fixed:* the handler now collects the methods of every route whose URL matches (`GET, HEAD, OPTIONS, PUT, DELETE`).
4. **Broken OpenAPI references.** A helper script that wrote the `paths/*.php` files turned "Workout exercise" into the schema name `Workoutexercise` instead of `WorkoutExercise`, so six `$ref`s pointed to schemas that do not exist. *Noticed:* swagger-php printed warnings when the document was generated with `display_errors` turned on for the check. With display errors off, as in `swagger.php`, the docs page would have loaded with broken schemas and no visible error. *Fixed:* corrected the name conversion, regenerated the files and confirmed zero warnings; the document now also passes an OpenAPI 3.0 validator.

### Verification run by the AI

`php -l` on all PHP files; `backend/test.php` (DAO) and `backend/rest/services/test.php` (business rules, rolled back afterwards) both pass; an HTTP test of every entity (CRUD, 400/404/405, delete protection, ignored server fields, no leaked hashes, SQL, stack traces or paths) passes on both the PHP built-in server and Apache; a forced 500 (MySQL stopped) returns a generic message and is logged; the weather endpoint returns real data for "Bihać", and simulated timeouts, connection and DNS failures, HTTP 500 and invalid JSON all return 503 and are logged; the Swagger UI loads in a headless browser with all tags and working "Try it out"; route and OpenAPI operations match exactly (31/31); protected folders return 403.

### Student review

_To be completed by the student: what you reviewed or changed yourself, anything you rejected, and any further mistakes you found._
