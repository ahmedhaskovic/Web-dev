<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';
require_once __DIR__ . '/../dao/WorkoutExerciseDao.php';

class ExerciseService extends BaseService
{
    protected string $entityName = 'Exercise';
    protected array $fields = ['name', 'description', 'muscle_group'];

    private WorkoutExerciseDao $workoutExerciseDao;

    public function __construct(?ExerciseDao $dao = null, ?WorkoutExerciseDao $workoutExerciseDao = null)
    {
        parent::__construct($dao ?? new ExerciseDao());
        $this->workoutExerciseDao = $workoutExerciseDao ?? new WorkoutExerciseDao();
    }

    // How many workouts trained each muscle group (for the dashboard chart).
    public function get_muscle_group_stats(): array
    {
        return $this->dao->get_workout_counts_by_muscle_group();
    }

    protected function validate(array $data, ?int $id = null): void
    {
        Validator::required($data['name'], 'name');
        Validator::length($data['name'], 'name', 100, 2);
        Validator::no_html($data['name'], 'name');

        // Names are unique, ignoring upper/lower case.
        $sameName = $this->dao->get_by_name($data['name']);
        if ($sameName !== null && (int) $sameName['id'] !== $id) {
            throw new ValidationException('An exercise with this name already exists.');
        }

        Validator::required($data['description'], 'description');
        Validator::length($data['description'], 'description', 1000);
        Validator::no_html($data['description'], 'description');

        Validator::required($data['muscle_group'], 'muscle_group');
        Validator::length($data['muscle_group'], 'muscle_group', 60, 2);
        Validator::no_html($data['muscle_group'], 'muscle_group');
    }

    protected function validate_delete(array $row): void
    {
        $usageCount = $this->workoutExerciseDao->count_by_exercise((int) $row['id']);
        if ($usageCount > 0) {
            throw new ValidationException(
                "This exercise is used in {$usageCount} workout entr" . ($usageCount === 1 ? 'y' : 'ies')
                . ' and cannot be deleted.'
            );
        }
    }
}
