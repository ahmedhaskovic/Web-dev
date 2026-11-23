<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';
require_once __DIR__ . '/../dao/GoalDao.php';

class UserService extends BaseService
{
    public const ROLES = ['admin', 'member'];

    protected string $entityName = 'User';
    protected array $fields = ['name', 'email', 'password', 'role', 'current_weight', 'city'];

    private WorkoutDao $workoutDao;
    private GoalDao $goalDao;

    public function __construct(?UserDao $dao = null, ?WorkoutDao $workoutDao = null, ?GoalDao $goalDao = null)
    {
        parent::__construct($dao ?? new UserDao());
        $this->workoutDao = $workoutDao ?? new WorkoutDao();
        $this->goalDao = $goalDao ?? new GoalDao();
    }

    protected function prepare(array $data): array
    {
        $clean = parent::prepare($data);

        // Passwords are kept exactly as typed (prepare() trims every other text field).
        if (isset($data['password']) && is_string($data['password']) && $data['password'] !== '') {
            $clean['password'] = $data['password'];
        }

        // Emails are stored in lowercase so "Ana@Mail.com" and "ana@mail.com" are the same account.
        if ($clean['email'] !== null) {
            $clean['email'] = mb_strtolower($clean['email']);
        }

        // New users are members unless a role is given.
        if ($clean['role'] === null) {
            $clean['role'] = 'member';
        }

        return $clean;
    }

    protected function validate(array $data, ?int $id = null): void
    {
        Validator::required($data['name'], 'name');
        Validator::length($data['name'], 'name', 100, 2);
        Validator::no_html($data['name'], 'name');

        Validator::required($data['email'], 'email');
        Validator::length($data['email'], 'email', 254);
        Validator::email($data['email'], 'email');

        $sameEmail = $this->dao->get_by_email($data['email']);
        if ($sameEmail !== null && (int) $sameEmail['id'] !== $id) {
            throw new ValidationException('A user with this email already exists.');
        }

        // A password is required for new users. On update it is optional:
        // when it is left out, the current password is kept.
        if ($id === null) {
            Validator::required($data['password'], 'password');
        }
        Validator::password($data['password'], 'password');

        Validator::one_of($data['role'], 'role', self::ROLES);

        Validator::required($data['current_weight'], 'current_weight');
        Validator::number($data['current_weight'], 'current_weight', 20, 500);

        Validator::length($data['city'], 'city', 100, 2);
        Validator::no_html($data['city'], 'city');

        // The application must always keep at least one admin.
        if ($id !== null && $data['role'] !== 'admin') {
            $existing = $this->get_by_id($id);
            if ($existing['role'] === 'admin' && $this->dao->count_by_role('admin') === 1) {
                throw new ValidationException('The last admin cannot be changed to a member.');
            }
        }
    }

    protected function before_save(array $data): array
    {
        if ($data['password'] === null) {
            // Update without a new password: do not touch the stored hash.
            unset($data['password']);
        } else {
            // Only the bcrypt hash is stored, never the plain password.
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return $data;
    }

    protected function validate_delete(array $row): void
    {
        if ($row['role'] === 'admin' && $this->dao->count_by_role('admin') === 1) {
            throw new ValidationException('The last admin cannot be deleted.');
        }

        // The database would silently delete the user's history, so the service blocks it instead.
        $workoutCount = $this->workoutDao->count_by_user((int) $row['id']);
        $goalCount = $this->goalDao->count_by_user((int) $row['id']);
        if ($workoutCount > 0 || $goalCount > 0) {
            throw new ValidationException(
                "This user still has {$workoutCount} workout(s) and {$goalCount} goal(s). Delete them first."
            );
        }
    }
}
