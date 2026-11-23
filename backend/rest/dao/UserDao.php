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
        return ['name', 'email', 'password', 'role', 'current_weight', 'city'];
    }

    public function get_all(): array
    {
        return $this->query(
            'SELECT id, name, email, role, current_weight, city, created_at, updated_at FROM users'
        )->fetchAll();
    }

    // Same as BaseDao::get_by_id, but never selects the password hash.
    public function get_by_id(int $id): ?array
    {
        return $this->query_unique(
            'SELECT id, name, email, role, current_weight, city, created_at, updated_at FROM users WHERE id = :id',
            [':id' => $id]
        );
    }

    public function count_by_role(string $role): int
    {
        return $this->count_where(['role' => $role]);
    }

    public function get_by_email(string $email): ?array
    {
        return $this->query_unique(
            'SELECT * FROM users WHERE email = :email',
            [':email' => $email]
        );
    }
}
