<?php

require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    protected function tableName(): string
    {
        return 'users';
    }

    protected function writableColumns(): array
    {
        return ['name', 'email', 'password', 'role', 'current_weight'];
    }

    public function get_all(): array
    {
        return $this->query(
            'SELECT id, name, email, role, current_weight, created_at, updated_at FROM users'
        )->fetchAll();
    }

    public function get_by_email(string $email): ?array
    {
        return $this->query_unique(
            'SELECT * FROM users WHERE email = :email',
            [':email' => $email]
        );
    }
}
