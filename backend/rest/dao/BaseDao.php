<?php

abstract class BaseDao
{
    private static ?PDO $connection = null;

    public function __construct()
    {
        self::getConnection();
    }

    abstract protected function tableName(): string;

    abstract protected function writableColumns(): array;

    protected function query(string $sql, array $parameters = []): PDOStatement
    {
        $statement = self::getConnection()->prepare($sql);
        $statement->execute($parameters);

        return $statement;
    }

    protected function query_unique(string $sql, array $parameters = []): ?array
    {
        $result = $this->query($sql, $parameters)->fetch();

        return $result === false ? null : $result;
    }

    protected function execute(string $sql, array $parameters = []): int
    {
        return $this->query($sql, $parameters)->rowCount();
    }

    public function get_all(): array
    {
        return $this->query('SELECT * FROM `' . $this->tableName() . '`')->fetchAll();
    }

    public function get_by_id(int $id): ?array
    {
        return $this->query_unique(
            'SELECT * FROM `' . $this->tableName() . '` WHERE `id` = :id',
            [':id' => $id]
        );
    }

    public function add(array $data): int
    {
        $data = $this->only_columns($data, $this->writableColumns());
        if ($data === []) {
            throw new InvalidArgumentException('At least one writable column is required.');
        }

        $columns = array_keys($data);
        $columnSql = implode(', ', array_map(static fn (string $column): string => '`' . $column . '`', $columns));
        $placeholderSql = implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns));
        $parameters = [];
        foreach ($data as $column => $value) {
            $parameters[':' . $column] = $value;
        }

        $this->execute(
            'INSERT INTO `' . $this->tableName() . '` (' . $columnSql . ') VALUES (' . $placeholderSql . ')',
            $parameters
        );

        return (int) self::getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $data = $this->only_columns($data, $this->writableColumns());
        if ($data === []) {
            throw new InvalidArgumentException('At least one writable column is required.');
        }

        $assignments = [];
        $parameters = [':id' => $id];
        foreach ($data as $column => $value) {
            $assignments[] = '`' . $column . '` = :set_' . $column;
            $parameters[':set_' . $column] = $value;
        }

        return $this->execute(
            'UPDATE `' . $this->tableName() . '` SET ' . implode(', ', $assignments) . ' WHERE `id` = :id',
            $parameters
        );
    }

    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM `' . $this->tableName() . '` WHERE `id` = :id',
            [':id' => $id]
        );
    }

    protected function only_columns(array $data, array $allowedColumns): array
    {
        return array_intersect_key($data, array_flip($allowedColumns));
    }

    // Returns the rows whose columns equal the given values, for example ['user_id' => 2].
    // Column names cannot be bound as parameters, so they are checked against an allow-list;
    // the values are always bound as parameters.
    protected function find_where(array $conditions, ?string $orderBy = null, bool $descending = false): array
    {
        [$whereSql, $parameters] = $this->build_where($conditions);

        $sql = 'SELECT * FROM `' . $this->tableName() . '`' . $whereSql;
        if ($orderBy !== null) {
            $this->check_column($orderBy);
            $sql .= ' ORDER BY `' . $orderBy . '`' . ($descending ? ' DESC' : ' ASC') . ', `id`';
        }

        return $this->query($sql, $parameters)->fetchAll();
    }

    // Counts the rows whose columns equal the given values.
    protected function count_where(array $conditions): int
    {
        [$whereSql, $parameters] = $this->build_where($conditions);

        return (int) $this->query(
            'SELECT COUNT(*) FROM `' . $this->tableName() . '`' . $whereSql,
            $parameters
        )->fetchColumn();
    }

    private function build_where(array $conditions): array
    {
        $clauses = [];
        $parameters = [];
        foreach ($conditions as $column => $value) {
            $this->check_column($column);
            $clauses[] = '`' . $column . '` = :where_' . $column;
            $parameters[':where_' . $column] = $value;
        }

        $whereSql = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);

        return [$whereSql, $parameters];
    }

    private function check_column(string $column): void
    {
        $allowedColumns = array_merge(['id'], $this->writableColumns());
        if (!in_array($column, $allowedColumns, true)) {
            throw new InvalidArgumentException('Unknown column: ' . $column);
        }
    }

    public function begin_transaction(): void
    {
        if (!self::getConnection()->beginTransaction()) {
            throw new RuntimeException('Could not begin the database transaction.');
        }
    }

    public function commit(): void
    {
        if (!self::getConnection()->commit()) {
            throw new RuntimeException('Could not commit the database transaction.');
        }
    }

    public function rollback(): void
    {
        if (!self::getConnection()->rollBack()) {
            throw new RuntimeException('Could not roll back the database transaction.');
        }
    }

    // Loads backend/config/config.php without opening a database connection,
    // so code that only needs settings (for example the weather service) can use Config.
    public static function loadConfig(): void
    {
        if (class_exists('Config', false)) {
            return;
        }

        $configPath = __DIR__ . '/../../config/config.php';
        if (!is_file($configPath)) {
            throw new RuntimeException(
                'Configuration not found. Copy backend/config/config.example.php to backend/config/config.php.'
            );
        }

        require_once $configPath;
    }

    private static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::loadConfig();

            self::$connection = new PDO(
                'mysql:host=' . Config::DB_HOST()
                    . ';port=' . Config::DB_PORT()
                    . ';dbname=' . Config::DB_NAME()
                    . ';charset=utf8mb4',
                Config::DB_USER(),
                Config::DB_PASSWORD(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connection;
    }
}
