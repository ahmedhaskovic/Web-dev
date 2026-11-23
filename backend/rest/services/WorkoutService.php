<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/GoalService.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class WorkoutService extends BaseService
{
    protected string $entityName = 'Workout';
    protected array $fields = ['user_id', 'date', 'total_calories', 'notes'];

    private UserDao $userDao;
    private GoalService $goalService;

    public function __construct(?WorkoutDao $dao = null, ?UserDao $userDao = null, ?GoalService $goalService = null)
    {
        parent::__construct($dao ?? new WorkoutDao());
        $this->userDao = $userDao ?? new UserDao();
        $this->goalService = $goalService ?? new GoalService();
    }

    public function get_by_user($userId): array
    {
        $userId = $this->validate_id($userId);
        if ($this->userDao->get_by_id($userId) === null) {
            throw new NotFoundException('User not found.');
        }

        return $this->dao->get_by_user($userId);
    }

    // After every change, the user's goals are recounted (goals.workouts_completed).
    public function add(array $data): array
    {
        $workout = parent::add($data);
        $this->goalService->recalculate_for_user((int) $workout['user_id']);

        return $workout;
    }

    public function update($id, array $data): array
    {
        $workout = parent::update($id, $data);
        $this->goalService->recalculate_for_user((int) $workout['user_id']);

        return $workout;
    }

    // Deleting a workout also deletes its exercise entries (ON DELETE CASCADE in the schema).
    public function delete($id): void
    {
        $workout = $this->get_by_id($id);
        parent::delete($id);
        $this->goalService->recalculate_for_user((int) $workout['user_id']);
    }

    protected function prepare(array $data): array
    {
        $clean = parent::prepare($data);

        // Same default as the database column.
        if ($clean['total_calories'] === null) {
            $clean['total_calories'] = '0';
        }

        return $clean;
    }

    protected function validate(array $data, ?int $id = null): void
    {
        $this->require_existing($this->userDao, $data['user_id'], 'user_id', 'user');
        if ($id !== null && (int) $data['user_id'] !== (int) $this->get_by_id($id)['user_id']) {
            throw new ValidationException('A workout cannot be moved to another user.');
        }

        // A workout is logged after it happens, so its date cannot be in the future.
        Validator::required($data['date'], 'date');
        Validator::date($data['date'], 'date');
        Validator::in_past($data['date'], 'date', true);
        if ($data['date'] < '2000-01-01') {
            throw new ValidationException('date cannot be before 2000-01-01.');
        }

        Validator::integer($data['total_calories'], 'total_calories', 0, 10000);

        Validator::length($data['notes'], 'notes', 1000);
        Validator::no_html($data['notes'], 'notes');
    }
}
