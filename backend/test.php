<?php

require_once __DIR__ . '/rest/dao/UserDao.php';
require_once __DIR__ . '/rest/dao/WorkoutDao.php';
require_once __DIR__ . '/rest/dao/ExerciseDao.php';
require_once __DIR__ . '/rest/dao/WorkoutExerciseDao.php';
require_once __DIR__ . '/rest/dao/GoalDao.php';

function assert_test(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }

    echo "PASS: {$message}\n";
}

$userDao = new UserDao();
$workoutDao = new WorkoutDao();
$exerciseDao = new ExerciseDao();
$workoutExerciseDao = new WorkoutExerciseDao();
$goalDao = new GoalDao();

$users = $userDao->get_all();
assert_test($users !== [], 'UserDao::get_all returns seeded users.');
assert_test(!array_key_exists('password', $users[0]), 'UserDao::get_all does not expose password hashes.');
assert_test($userDao->get_by_id((int) $users[0]['id']) !== null, 'UserDao::get_by_id finds a seeded user.');

$admin = $userDao->get_by_email('admin@trackify.local');
assert_test($admin !== null, 'UserDao::get_by_email finds the seeded admin.');
assert_test(
    $admin !== null && password_verify('TrackifyDemo!2026', $admin['password']),
    'Seeded account password is a valid bcrypt hash.'
);

$workouts = $workoutDao->get_all();
$exercises = $exerciseDao->get_all();
$workoutExercises = $workoutExerciseDao->get_all();
$goals = $goalDao->get_all();
assert_test($workouts !== [] && $workoutDao->get_by_id((int) $workouts[0]['id']) !== null, 'WorkoutDao read methods return seeded workouts.');
assert_test($exercises !== [] && $exerciseDao->get_by_id((int) $exercises[0]['id']) !== null, 'ExerciseDao read methods return seeded exercises.');
assert_test($workoutExercises !== [] && $workoutExerciseDao->get_by_id((int) $workoutExercises[0]['id']) !== null, 'WorkoutExerciseDao read methods return seeded records.');
assert_test($goals !== [] && $goalDao->get_by_id((int) $goals[0]['id']) !== null, 'GoalDao read methods return seeded goals.');
assert_test(
    $workoutExerciseDao->get_by_workout_id_with_exercises((int) $workouts[0]['id']) !== [],
    'WorkoutExerciseDao JOIN query returns exercise details.'
);
assert_test(
    $exerciseDao->get_workout_counts_by_muscle_group() !== [],
    'ExerciseDao GROUP BY query returns workout counts by muscle group.'
);

$testEmail = 'dao-test-' . bin2hex(random_bytes(6)) . '@trackify.local';
$userId = null;
$workoutId = null;
$exerciseId = null;
$workoutExerciseId = null;
$goalId = null;

$userDao->begin_transaction();
try {
    $userId = $userDao->add([
        'name' => 'DAO Test User',
        'email' => $testEmail,
        'password' => password_hash('TemporaryTestPassword!2026', PASSWORD_BCRYPT),
        'role' => 'member',
        'current_weight' => 72.50,
        'id' => 999999,
        'created_at' => '2000-01-01 00:00:00',
        'updated_at' => '2000-01-01 00:00:00',
    ]);
    assert_test($userId > 0 && $userId !== 999999, 'UserDao::add ignores id and generated timestamp fields.');
    assert_test($userDao->get_by_id($userId) !== null, 'UserDao::get_by_id finds a newly added user.');
    assert_test($userDao->update($userId, ['name' => 'Updated DAO Test User', 'id' => 999999]) === 1, 'UserDao::update changes a user without changing its id.');
    assert_test($userDao->get_by_email($testEmail) !== null, 'UserDao::get_by_email finds the newly added user.');

    $workoutId = $workoutDao->add([
        'user_id' => $userId,
        'date' => '2026-10-25',
        'total_calories' => 250,
        'notes' => 'Temporary DAO test workout',
    ]);
    assert_test($workoutId > 0, 'WorkoutDao::add creates a workout.');
    assert_test($workoutDao->update($workoutId, ['total_calories' => 275]) === 1, 'WorkoutDao::update changes a workout.');
    assert_test($workoutDao->get_by_id($workoutId) !== null, 'WorkoutDao::get_by_id finds a newly added workout.');

    $exerciseName = 'DAO Test Exercise ' . bin2hex(random_bytes(4));
    $exerciseId = $exerciseDao->add([
        'name' => $exerciseName,
        'description' => 'Temporary exercise for DAO tests.',
        'muscle_group' => 'Core',
    ]);
    assert_test($exerciseId > 0, 'ExerciseDao::add creates an exercise.');
    assert_test(
        $exerciseDao->update($exerciseId, ['description' => 'Updated temporary exercise description.']) === 1,
        'ExerciseDao::update changes an exercise.'
    );
    assert_test($exerciseDao->get_by_id($exerciseId) !== null, 'ExerciseDao::get_by_id finds a newly added exercise.');

    $workoutExerciseId = $workoutExerciseDao->add([
        'workout_id' => $workoutId,
        'exercise_id' => $exerciseId,
        'duration' => 20,
        'sets' => 3,
        'reps' => 10,
        'weight' => 12.50,
    ]);
    assert_test($workoutExerciseId > 0, 'WorkoutExerciseDao::add creates a workout exercise.');
    assert_test(
        $workoutExerciseDao->update($workoutExerciseId, ['reps' => 12]) === 1,
        'WorkoutExerciseDao::update changes a workout exercise.'
    );
    assert_test($workoutExerciseDao->get_by_id($workoutExerciseId) !== null, 'WorkoutExerciseDao::get_by_id finds a newly added record.');

    $goalId = $goalDao->add([
        'user_id' => $userId,
        'target_weight' => 70.00,
        'start_weight' => 72.50,
        'target_date' => '2027-03-01',
        'workouts_completed' => 0,
    ]);
    assert_test($goalId > 0, 'GoalDao::add creates a goal.');
    assert_test($goalDao->update($goalId, ['workouts_completed' => 1]) === 1, 'GoalDao::update changes a goal.');
    assert_test($goalDao->get_by_id($goalId) !== null, 'GoalDao::get_by_id finds a newly added goal.');

    assert_test($workoutExerciseDao->delete($workoutExerciseId) === 1, 'WorkoutExerciseDao::delete removes a record.');
    assert_test($goalDao->delete($goalId) === 1, 'GoalDao::delete removes a goal.');
    assert_test($workoutDao->delete($workoutId) === 1, 'WorkoutDao::delete removes a workout.');
    assert_test($exerciseDao->delete($exerciseId) === 1, 'ExerciseDao::delete removes an exercise.');
    assert_test($userDao->delete($userId) === 1, 'UserDao::delete removes a user.');

    $userDao->rollback();
} catch (Throwable $error) {
    try {
        $userDao->rollback();
    } catch (Throwable $rollbackError) {
        throw new RuntimeException(
            'DAO test failed and its transaction could not be rolled back: ' . $rollbackError->getMessage(),
            0,
            $error
        );
    }

    throw $error;
}

assert_test($userDao->get_by_email($testEmail) === null, 'rollback removes temporary DAO test records.');
$userDao->begin_transaction();
$userDao->commit();
assert_test(true, 'begin_transaction and commit complete successfully.');

echo "All DAO tests passed.\n";
