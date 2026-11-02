<?php

require_once __DIR__ . '/BaseDao.php';

class WorkoutExerciseDao extends BaseDao
{
    protected function tableName(): string
    {
        return 'workout_exercises';
    }

    protected function writableColumns(): array
    {
        return ['workout_id', 'exercise_id', 'duration', 'sets', 'reps', 'weight'];
    }

    public function get_by_workout_id_with_exercises(int $workoutId): array
    {
        return $this->query(
            'SELECT workout_exercises.id, workout_exercises.workout_id, workout_exercises.exercise_id,
                    workout_exercises.duration, workout_exercises.sets, workout_exercises.reps,
                    workout_exercises.weight, exercises.name AS exercise_name,
                    exercises.muscle_group
             FROM workout_exercises
             INNER JOIN exercises ON exercises.id = workout_exercises.exercise_id
             WHERE workout_exercises.workout_id = :workout_id
             ORDER BY workout_exercises.id',
            [':workout_id' => $workoutId]
        )->fetchAll();
    }
}
