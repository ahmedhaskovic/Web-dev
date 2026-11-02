<?php

require_once __DIR__ . '/BaseDao.php';

class ExerciseDao extends BaseDao
{
    protected function tableName(): string
    {
        return 'exercises';
    }

    protected function writableColumns(): array
    {
        return ['name', 'description', 'muscle_group'];
    }

    public function get_workout_counts_by_muscle_group(): array
    {
        return $this->query(
            'SELECT exercises.muscle_group, COUNT(DISTINCT workout_exercises.workout_id) AS workout_count
             FROM exercises
             LEFT JOIN workout_exercises ON workout_exercises.exercise_id = exercises.id
             GROUP BY exercises.muscle_group
             ORDER BY exercises.muscle_group'
        )->fetchAll();
    }
}
