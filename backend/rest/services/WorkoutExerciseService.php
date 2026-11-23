<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WorkoutExerciseDao.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

// One exercise performed inside one workout (the workout_exercises join table).
class WorkoutExerciseService extends BaseService
{
    protected string $entityName = 'Workout exercise';
    protected array $fields = ['workout_id', 'exercise_id', 'duration', 'sets', 'reps', 'weight'];

    private WorkoutDao $workoutDao;
    private ExerciseDao $exerciseDao;

    public function __construct(
        ?WorkoutExerciseDao $dao = null,
        ?WorkoutDao $workoutDao = null,
        ?ExerciseDao $exerciseDao = null
    ) {
        parent::__construct($dao ?? new WorkoutExerciseDao());
        $this->workoutDao = $workoutDao ?? new WorkoutDao();
        $this->exerciseDao = $exerciseDao ?? new ExerciseDao();
    }

    // The exercises of one workout, with each exercise's name and muscle group.
    public function get_by_workout($workoutId): array
    {
        $workoutId = $this->validate_id($workoutId);
        if ($this->workoutDao->get_by_id($workoutId) === null) {
            throw new NotFoundException('Workout not found.');
        }

        return $this->dao->get_by_workout_id_with_exercises($workoutId);
    }

    protected function prepare(array $data): array
    {
        $clean = parent::prepare($data);

        // Bodyweight and cardio exercises have no extra weight; same default as the database column.
        if ($clean['weight'] === null) {
            $clean['weight'] = '0';
        }

        return $clean;
    }

    protected function validate(array $data, ?int $id = null): void
    {
        $this->require_existing($this->workoutDao, $data['workout_id'], 'workout_id', 'workout');
        if ($id !== null && (int) $data['workout_id'] !== (int) $this->get_by_id($id)['workout_id']) {
            throw new ValidationException('An exercise entry cannot be moved to another workout.');
        }

        $this->require_existing($this->exerciseDao, $data['exercise_id'], 'exercise_id', 'exercise');

        // Each exercise appears at most once per workout; change its sets/reps instead.
        $sameEntry = $this->dao->get_by_workout_and_exercise((int) $data['workout_id'], (int) $data['exercise_id']);
        if ($sameEntry !== null && (int) $sameEntry['id'] !== $id) {
            throw new ValidationException('This exercise is already part of the workout.');
        }

        Validator::required($data['duration'], 'duration');
        Validator::integer($data['duration'], 'duration', 1, 600);

        Validator::required($data['sets'], 'sets');
        Validator::integer($data['sets'], 'sets', 1, 100);

        Validator::required($data['reps'], 'reps');
        Validator::integer($data['reps'], 'reps', 1, 1000);

        Validator::number($data['weight'], 'weight', 0, 1000);
    }
}
