<?php

// Service-layer tests: tries to break every business rule.
// Run from the project root:   php backend/rest/services/test.php
// Everything runs inside one database transaction that is always rolled back,
// so no test data is left behind. Exit code 0 = all passed, 1 = something failed.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Europe/Sarajevo');

require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/ExerciseService.php';
require_once __DIR__ . '/WorkoutService.php';
require_once __DIR__ . '/WorkoutExerciseService.php';
require_once __DIR__ . '/GoalService.php';
require_once __DIR__ . '/WeatherService.php';

$passed = 0;
$failed = 0;

// Runs $action. With $expected = null the action must succeed; otherwise it must throw $expected.
// $messagePart (optional) must appear in the error message, so a test cannot pass
// because a different rule happened to throw the same exception class.
function attempt(string $label, callable $action, ?string $expected = null, ?string $messagePart = null)
{
    global $passed, $failed;

    try {
        $result = $action();
        if ($expected === null) {
            $passed++;
            echo "PASS  {$label}\n";
            return $result;
        }
        $failed++;
        echo "FAIL  {$label}: expected {$expected}, but it succeeded\n";
    } catch (Throwable $error) {
        $messageMatches = $messagePart === null || str_contains($error->getMessage(), $messagePart);
        if ($expected !== null && $error instanceof $expected && $messageMatches) {
            $passed++;
            $status = $error instanceof HttpException ? $error->get_status() : '-';
            echo "PASS  {$label} [{$status} " . get_class($error) . ': ' . $error->getMessage() . "]\n";
        } else {
            $failed++;
            echo "FAIL  {$label}: " . get_class($error) . ': ' . $error->getMessage() . "\n";
        }
    }

    return null;
}

function check(string $label, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "PASS  {$label}\n";
    } else {
        $failed++;
        echo "FAIL  {$label}\n";
    }
}

$userDao = new UserDao();
$users = new UserService();
$exercises = new ExerciseService();
$workouts = new WorkoutService();
$workoutExercises = new WorkoutExerciseService();
$goals = new GoalService();
$weather = new WeatherService();

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$yesterday = date('Y-m-d', strtotime('-1 day'));
$nextYear = date('Y-m-d', strtotime('+1 year'));
$unique = bin2hex(random_bytes(4));

$countsBefore = [
    count($users->get_all()), count($exercises->get_all()), count($workouts->get_all()),
    count($workoutExercises->get_all()), count($goals->get_all()),
];

$userDao->begin_transaction();
try {
    echo "\n== Users ==\n";
    $validUser = [
        'name' => 'Test Član',
        'email' => "Service.Test.{$unique}@Trackify.LOCAL",
        'password' => 'Secret123',
        'current_weight' => 74.5,
        'city' => 'Tuzla',
    ];
    $user = attempt('create a valid user', fn () => $users->add($validUser + [
        'id' => 999999,
        'created_at' => '2000-01-01 00:00:00',
        'unknown_field' => 'ignored',
    ]));
    $userId = (int) $user['id'];
    check('server-owned id is ignored', $userId !== 999999);
    check('email is stored in lowercase', $user['email'] === "service.test.{$unique}@trackify.local");
    check('role defaults to member', $user['role'] === 'member');
    check('non-ASCII name is stored correctly', $user['name'] === 'Test Član');
    check('password hash is never returned', !array_key_exists('password', $user));
    $stored = $userDao->get_by_email($user['email']);
    check('only a bcrypt hash is stored', str_starts_with($stored['password'], '$2y$') && password_verify('Secret123', $stored['password']));

    attempt('name is required', fn () => $users->add(['name' => '  '] + $validUser), ValidationException::class);
    attempt('name cannot contain HTML', fn () => $users->add(['name' => '<b>Hi</b>'] + $validUser), ValidationException::class);
    attempt('name longer than 100 characters', fn () => $users->add(['name' => str_repeat('a', 101)] + $validUser), ValidationException::class);
    attempt('array instead of a single value', fn () => $users->add(['name' => ['a', 'b']] + $validUser), ValidationException::class);
    attempt('invalid email', fn () => $users->add(['email' => 'not-an-email'] + $validUser), ValidationException::class);
    attempt('duplicate email (different case)', fn () => $users->add(['email' => 'ADMIN@trackify.local'] + $validUser), ValidationException::class);
    attempt('password is required', fn () => $users->add(['email' => "p{$unique}@x.ba", 'password' => null] + $validUser), ValidationException::class, 'password is required');
    attempt('password shorter than 8', fn () => $users->add(['email' => "a{$unique}@x.ba", 'password' => 'Ab1'] + $validUser), ValidationException::class);
    attempt('password without a digit', fn () => $users->add(['email' => "b{$unique}@x.ba", 'password' => 'OnlyLetters'] + $validUser), ValidationException::class);
    attempt('password longer than 72', fn () => $users->add(['email' => "c{$unique}@x.ba", 'password' => str_repeat('a1', 37)] + $validUser), ValidationException::class);
    attempt('unknown role', fn () => $users->add(['email' => "d{$unique}@x.ba", 'role' => 'superadmin'] + $validUser), ValidationException::class);
    attempt('weight is not a number', fn () => $users->add(['email' => "e{$unique}@x.ba", 'current_weight' => 'heavy'] + $validUser), ValidationException::class);
    attempt('weight below 20', fn () => $users->add(['email' => "f{$unique}@x.ba", 'current_weight' => 10] + $validUser), ValidationException::class);
    attempt('weight with 3 decimals', fn () => $users->add(['email' => "g{$unique}@x.ba", 'current_weight' => 70.125] + $validUser), ValidationException::class);
    attempt('boolean weight (true becomes 1)', fn () => $users->add(['email' => "h{$unique}@x.ba", 'current_weight' => true] + $validUser), ValidationException::class, 'at least 20');

    attempt('get a missing user', fn () => $users->get_by_id(999999), NotFoundException::class);
    attempt('get with an invalid id', fn () => $users->get_by_id('abc'), ValidationException::class);

    $updated = attempt('update without password keeps the old one', fn () => $users->update($userId, ['name' => 'Renamed', 'password' => null] + $validUser));
    check('name was updated', $updated['name'] === 'Renamed');
    check('old password still works', password_verify('Secret123', $userDao->get_by_email($user['email'])['password']));
    attempt('update with a new password', fn () => $users->update($userId, ['password' => 'NewSecret456'] + $validUser));
    check('new password works', password_verify('NewSecret456', $userDao->get_by_email($user['email'])['password']));
    attempt('update to another user\'s email', fn () => $users->update($userId, ['email' => 'marko@trackify.local'] + $validUser), ValidationException::class);
    attempt('PUT without a required field', fn () => $users->update($userId, ['name' => 'Only name']), ValidationException::class, 'email is required');
    attempt('update a missing user', fn () => $users->update(999999, $validUser), NotFoundException::class);
    attempt('change the last admin to member', fn () => $users->update(1, [
        'name' => 'Amina Hadzic', 'email' => 'admin@trackify.local', 'role' => 'member', 'current_weight' => 68.5,
    ]), ValidationException::class);
    attempt('delete the last admin', fn () => $users->delete(1), ValidationException::class);
    attempt('delete a user who has workouts and goals', fn () => $users->delete(2), ValidationException::class);

    echo "\n== Exercises ==\n";
    $validExercise = ['name' => "Plank {$unique}", 'description' => 'Core hold.', 'muscle_group' => 'Core'];
    $exercise = attempt('create a valid exercise', fn () => $exercises->add($validExercise));
    $exerciseId = (int) $exercise['id'];
    attempt('duplicate name (different case)', fn () => $exercises->add(['name' => 'push-UP'] + $validExercise), ValidationException::class);
    attempt('description is required', fn () => $exercises->add(['name' => "X {$unique}", 'description' => ''] + $validExercise), ValidationException::class);
    attempt('muscle_group longer than 60', fn () => $exercises->add(['name' => "Y {$unique}", 'muscle_group' => str_repeat('m', 61)] + $validExercise), ValidationException::class);
    attempt('update keeping its own name', fn () => $exercises->update($exerciseId, ['description' => 'Updated.'] + $validExercise));
    attempt('rename to an existing name', fn () => $exercises->update($exerciseId, ['name' => 'Running'] + $validExercise), ValidationException::class);
    attempt('delete an exercise used in workouts', fn () => $exercises->delete(1), ValidationException::class);
    $stats = attempt('muscle group statistics', fn () => $exercises->get_muscle_group_stats());
    check('statistics include workout counts', isset($stats[0]['muscle_group'], $stats[0]['workout_count']));

    echo "\n== Workouts ==\n";
    $validWorkout = ['user_id' => $userId, 'date' => $yesterday, 'total_calories' => 300, 'notes' => 'Šetnja'];
    $workout = attempt('create a valid workout', fn () => $workouts->add($validWorkout));
    $workoutId = (int) $workout['id'];
    $noCalories = attempt('total_calories defaults to 0', fn () => $workouts->add(['total_calories' => null] + $validWorkout));
    check('default calories saved as 0', (int) $noCalories['total_calories'] === 0);
    attempt('today is allowed', fn () => $workouts->add(['date' => $today] + $validWorkout));
    attempt('user_id is required', fn () => $workouts->add(['user_id' => null] + $validWorkout), ValidationException::class);
    attempt('user does not exist', fn () => $workouts->add(['user_id' => 999999] + $validWorkout), ValidationException::class);
    attempt('date in the future', fn () => $workouts->add(['date' => $tomorrow] + $validWorkout), ValidationException::class);
    attempt('impossible date 2026-02-31', fn () => $workouts->add(['date' => '2026-02-31'] + $validWorkout), ValidationException::class);
    attempt('wrong date format', fn () => $workouts->add(['date' => '23.11.2026'] + $validWorkout), ValidationException::class);
    attempt('date before 2000', fn () => $workouts->add(['date' => '1999-12-31'] + $validWorkout), ValidationException::class);
    attempt('negative calories', fn () => $workouts->add(['total_calories' => -1] + $validWorkout), ValidationException::class);
    attempt('decimal calories', fn () => $workouts->add(['total_calories' => '12.5'] + $validWorkout), ValidationException::class);
    attempt('notes with HTML', fn () => $workouts->add(['notes' => '<script>x</script>'] + $validWorkout), ValidationException::class);
    attempt('move a workout to another user', fn () => $workouts->update($workoutId, ['user_id' => 2] + $validWorkout), ValidationException::class);
    attempt('workouts of a missing user', fn () => $workouts->get_by_user(999999), NotFoundException::class);
    check('workouts of a user, newest first', $workouts->get_by_user($userId)[0]['date'] === $today);

    echo "\n== Workout exercises ==\n";
    $validEntry = ['workout_id' => $workoutId, 'exercise_id' => $exerciseId, 'duration' => 10, 'sets' => 3, 'reps' => 12, 'weight' => 2.5];
    $entry = attempt('add an exercise to a workout', fn () => $workoutExercises->add($validEntry));
    $entryId = (int) $entry['id'];
    attempt('same exercise twice in one workout', fn () => $workoutExercises->add($validEntry), ValidationException::class);
    attempt('exercise does not exist', fn () => $workoutExercises->add(['exercise_id' => 999999] + $validEntry), ValidationException::class);
    attempt('workout does not exist', fn () => $workoutExercises->add(['workout_id' => 999999] + $validEntry), ValidationException::class);
    attempt('sets must be at least 1', fn () => $workoutExercises->add(['exercise_id' => 2, 'sets' => 0] + $validEntry), ValidationException::class);
    attempt('reps must be a number', fn () => $workoutExercises->add(['exercise_id' => 2, 'reps' => 'ten'] + $validEntry), ValidationException::class);
    attempt('duration above 600 minutes', fn () => $workoutExercises->add(['exercise_id' => 2, 'duration' => 601] + $validEntry), ValidationException::class);
    attempt('negative weight', fn () => $workoutExercises->add(['exercise_id' => 2, 'weight' => -1] + $validEntry), ValidationException::class);
    $bodyweight = attempt('weight defaults to 0', fn () => $workoutExercises->add(['exercise_id' => 2, 'weight' => null] + $validEntry));
    check('default weight saved as 0', (float) $bodyweight['weight'] === 0.0);
    attempt('update sets and reps', fn () => $workoutExercises->update($entryId, ['sets' => 4, 'reps' => 10] + $validEntry));
    attempt('move an entry to another workout', fn () => $workoutExercises->update($entryId, ['workout_id' => 1] + $validEntry), ValidationException::class);
    $details = attempt('exercises of a workout', fn () => $workoutExercises->get_by_workout($workoutId));
    check('entries include the exercise name', count($details) === 2 && isset($details[0]['exercise_name']));
    attempt('exercises of a missing workout', fn () => $workoutExercises->get_by_workout(999999), NotFoundException::class);
    attempt('delete an entry', fn () => $workoutExercises->delete($entryId));
    attempt('deleted entry is gone', fn () => $workoutExercises->get_by_id($entryId), NotFoundException::class);

    echo "\n== Goals ==\n";
    $validGoal = ['user_id' => $userId, 'start_weight' => 74.5, 'target_weight' => 70, 'target_date' => $nextYear];
    $goal = attempt('create a valid goal', fn () => $goals->add($validGoal + ['workouts_completed' => 99]));
    $goalId = (int) $goal['id'];
    check('client value for workouts_completed is ignored and calculated (1 workout dated today)', (int) $goal['workouts_completed'] === 1);
    attempt('target equal to start weight', fn () => $goals->add(['target_weight' => 74.5] + $validGoal), ValidationException::class);
    attempt('target date in the past', fn () => $goals->add(['target_date' => $yesterday] + $validGoal), ValidationException::class);
    attempt('target date today', fn () => $goals->add(['target_date' => $today] + $validGoal), ValidationException::class);
    attempt('invalid target date', fn () => $goals->add(['target_date' => '2027-13-01'] + $validGoal), ValidationException::class);
    attempt('weight of 0', fn () => $goals->add(['start_weight' => 0] + $validGoal), ValidationException::class);
    attempt('user does not exist', fn () => $goals->add(['user_id' => 999999] + $validGoal), ValidationException::class);
    attempt('move a goal to another user', fn () => $goals->update($goalId, ['user_id' => 2] + $validGoal), ValidationException::class);

    $before = (int) $goals->get_by_id(1)['workouts_completed'];
    $extra = $workouts->add(['user_id' => 2, 'date' => $today, 'total_calories' => 100, 'notes' => null]);
    check('adding a workout recounts the user\'s goals', (int) $goals->get_by_id(1)['workouts_completed'] === $before + 1);
    $workouts->update($extra['id'], ['user_id' => 2, 'date' => '2026-01-01', 'total_calories' => 100, 'notes' => null]);
    check('moving a workout before the goal start recounts', (int) $goals->get_by_id(1)['workouts_completed'] === $before);
    $workouts->update($extra['id'], ['user_id' => 2, 'date' => $today, 'total_calories' => 100, 'notes' => null]);
    $workouts->delete($extra['id']);
    check('deleting a workout recounts the user\'s goals', (int) $goals->get_by_id(1)['workouts_completed'] === $before);
    attempt('update a goal and keep its target date', fn () => $goals->update($goalId, ['target_weight' => 71] + $validGoal));
    attempt('goals of a missing user', fn () => $goals->get_by_user(999999), NotFoundException::class);

    echo "\n== Weather (rules checked before calling the API) ==\n";
    attempt('days must be at least 1', fn () => $weather->get_forecast_for_user(1, '0'), ValidationException::class);
    attempt('days must be at most 7', fn () => $weather->get_forecast_for_user(1, '8'), ValidationException::class);
    attempt('weather for a missing user', fn () => $weather->get_forecast_for_user(999999), NotFoundException::class);
    $users->update($userId, ['city' => null] + $validUser);
    attempt('weather for a user without a city', fn () => $weather->get_forecast_for_user($userId), ValidationException::class);

    echo "\n== Deletes ==\n";
    attempt('delete a workout (its entries are deleted too)', fn () => $workouts->delete($workoutId));
    attempt('deleted workout is gone', fn () => $workouts->get_by_id($workoutId), NotFoundException::class);
    attempt('delete an unused exercise', fn () => $exercises->delete($exerciseId));
    attempt('delete a missing exercise', fn () => $exercises->delete($exerciseId), NotFoundException::class);
    attempt('user with goals still cannot be deleted', fn () => $users->delete($userId), ValidationException::class);
    foreach ($goals->get_by_user($userId) as $userGoal) {
        $goals->delete($userGoal['id']);
    }
    foreach ($workouts->get_by_user($userId) as $userWorkout) {
        $workouts->delete($userWorkout['id']);
    }
    attempt('delete a user without workouts or goals', fn () => $users->delete($userId));
    attempt('deleted user is gone', fn () => $users->get_by_id($userId), NotFoundException::class);
} catch (Throwable $error) {
    $failed++;
    echo 'FAIL  unexpected error: ' . get_class($error) . ': ' . $error->getMessage() . "\n";
} finally {
    $userDao->rollback();
}

$countsAfter = [
    count($users->get_all()), count($exercises->get_all()), count($workouts->get_all()),
    count($workoutExercises->get_all()), count($goals->get_all()),
];
check('no test rows are left in the database', $countsBefore === $countsAfter);

echo "\n{$passed} passed, {$failed} failed.\n";
exit($failed === 0 ? 0 : 1);
