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

    // Goals with the closest target date first.
    public function get_by_user(int $userId): array
    {
        return $this->find_where(['user_id' => $userId], 'target_date');
    }

    public function count_by_user(int $userId): int
    {
        return $this->count_where(['user_id' => $userId]);
    }
}
