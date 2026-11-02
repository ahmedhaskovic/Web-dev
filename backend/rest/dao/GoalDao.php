<?php

require_once __DIR__ . '/BaseDao.php';

class GoalDao extends BaseDao
{
    protected function tableName(): string
    {
        return 'goals';
    }

    protected function writableColumns(): array
    {
        return ['user_id', 'target_weight', 'start_weight', 'target_date', 'workouts_completed'];
    }
}
