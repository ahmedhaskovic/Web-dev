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

    // Newest workouts first.
    public function get_by_user(int $userId): array
    {
        return $this->find_where(['user_id' => $userId], 'date', true);
    }

    public function count_by_user(int $userId): int
    {
        return $this->count_where(['user_id' => $userId]);
    }

    // Number of the user's workouts dated from $fromDate to $toDate (both included).
    public function count_by_user_between(int $userId, string $fromDate, string $toDate): int
    {
        return (int) $this->query(
            'SELECT COUNT(*) FROM workouts
             WHERE user_id = :user_id AND `date` BETWEEN :from_date AND :to_date',
            [':user_id' => $userId, ':from_date' => $fromDate, ':to_date' => $toDate]
        )->fetchColumn();
    }
}
