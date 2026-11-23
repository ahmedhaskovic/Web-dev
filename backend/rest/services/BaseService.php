<?php

require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';

// Shared CRUD flow for every entity service.
// Services are the only layer that calls DAOs. They check every rule and throw an
// HttpException subclass when a rule is broken; they never return false or error arrays.
//
//   add:    prepare -> validate -> before_save -> DAO add -> read the saved row back
//   update: check it exists -> prepare -> validate -> before_save -> DAO update -> read it back
//   delete: check it exists -> validate_delete -> DAO delete

abstract class BaseService
{
    protected BaseDao $dao;

    // Used in messages, for example "Exercise not found."
    protected string $entityName;

    // The only fields a client may send. Anything else (id, timestamps, calculated values) is dropped.
    protected array $fields = [];

    public function __construct(BaseDao $dao)
    {
        $this->dao = $dao;
    }

    public function get_all(): array
    {
        return $this->dao->get_all();
    }

    public function get_by_id($id): array
    {
        $id = $this->validate_id($id);

        $row = $this->dao->get_by_id($id);
        if ($row === null) {
            throw new NotFoundException("{$this->entityName} not found.");
        }

        return $row;
    }

    public function add(array $data): array
    {
        $data = $this->prepare($data);
        $this->validate($data);
        $data = $this->before_save($data);

        $id = $this->dao->add($data);

        return $this->get_by_id($id);
    }

    // PUT is a full replace: every required field must be sent again.
    public function update($id, array $data): array
    {
        $existing = $this->get_by_id($id);
        $id = (int) $existing['id'];

        $data = $this->prepare($data);
        $this->validate($data, $id);
        $data = $this->before_save($data);

        // The DAO returns 0 when the values did not change; that is not an error.
        $this->dao->update($id, $data);

        return $this->get_by_id($id);
    }

    public function delete($id): void
    {
        $existing = $this->get_by_id($id);
        $this->validate_delete($existing);

        $this->dao->delete((int) $existing['id']);
    }

    // Checks every rule for a new row ($id is null) or an updated row ($id is its id).
    abstract protected function validate(array $data, ?int $id = null): void;

    // Override to block deleting a row, for example while other rows still use it.
    protected function validate_delete(array $row): void
    {
    }

    // Override to calculate server-owned values (such as a password hash) after validation.
    protected function before_save(array $data): array
    {
        return $data;
    }

    // Turns the request data into a clean array that contains exactly the fields in $fields.
    protected function prepare(array $data): array
    {
        $clean = [];
        foreach ($this->fields as $field) {
            $value = $data[$field] ?? null;

            if (is_array($value) || is_object($value)) {
                throw new ValidationException("{$field} must be a single value.");
            }

            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_int($value) || is_float($value)) {
                $value = (string) $value;
            } elseif (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }

            $clean[$field] = $value;
        }

        return $clean;
    }

    // A foreign key in the request must point to an existing row; otherwise 400, not a SQL error.
    protected function require_existing(BaseDao $dao, $id, string $field, string $label): array
    {
        Validator::required($id, $field);
        Validator::integer($id, $field, 1);

        $row = $dao->get_by_id((int) $id);
        if ($row === null) {
            throw new ValidationException("The selected {$label} does not exist.");
        }

        return $row;
    }

    protected function validate_id($id): int
    {
        Validator::required($id, 'id');
        Validator::integer($id, 'id', 1);

        return (int) $id;
    }
}
