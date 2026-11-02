<?php

require_once __DIR__ . '/BaseDao.php';

class WorkoutDao extends BaseDao
{
    protected function tableName(): string
    {
        return 'workouts';
    }

    protected function writableColumns(): array
    {
        return ['user_id', 'date', 'total_calories', 'notes'];
    }
}
