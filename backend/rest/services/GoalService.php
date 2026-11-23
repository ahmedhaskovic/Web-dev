<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/GoalDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';

class GoalService extends BaseService
{
    protected string $entityName = 'Goal';

    // workouts_completed is not here: the server calculates it, so a client value is ignored.
    protected array $fields = ['user_id', 'target_weight', 'start_weight', 'target_date'];

    private UserDao $userDao;
    private WorkoutDao $workoutDao;

    public function __construct(?GoalDao $dao = null, ?UserDao $userDao = null, ?WorkoutDao $workoutDao = null)
    {
        parent::__construct($dao ?? new GoalDao());
        $this->userDao = $userDao ?? new UserDao();
        $this->workoutDao = $workoutDao ?? new WorkoutDao();
    }

    public function get_by_user($userId): array
    {
        $userId = $this->validate_id($userId);
        if ($this->userDao->get_by_id($userId) === null) {
            throw new NotFoundException('User not found.');
        }

        return $this->dao->get_by_user($userId);
    }

    public function add(array $data): array
    {
        $goal = parent::add($data);
        $this->recalculate_goal($goal);

        return $this->get_by_id($goal['id']);
    }

    public function update($id, array $data): array
    {
        $goal = parent::update($id, $data);
        $this->recalculate_goal($goal);

        return $this->get_by_id($goal['id']);
    }

    // Called by WorkoutService whenever one of the user's workouts is added, changed or deleted.
    public function recalculate_for_user(int $userId): void
    {
        foreach ($this->dao->get_by_user($userId) as $goal) {
            $this->recalculate_goal($goal);
        }
    }

    // workouts_completed = the user's workouts dated from the day the goal was created to its target date.
    private function recalculate_goal(array $goal): void
    {
        $startDate = substr($goal['created_at'], 0, 10);
        $count = $this->workoutDao->count_by_user_between((int) $goal['user_id'], $startDate, $goal['target_date']);

        if ($count !== (int) $goal['workouts_completed']) {
            $this->dao->update((int) $goal['id'], ['workouts_completed' => $count]);
        }
    }

    protected function validate(array $data, ?int $id = null): void
    {
        $existing = $id === null ? null : $this->get_by_id($id);

        $this->require_existing($this->userDao, $data['user_id'], 'user_id', 'user');
        if ($existing !== null && (int) $data['user_id'] !== (int) $existing['user_id']) {
            throw new ValidationException('A goal cannot be moved to another user.');
        }

        Validator::required($data['start_weight'], 'start_weight');
        Validator::number($data['start_weight'], 'start_weight', 20, 500);

        Validator::required($data['target_weight'], 'target_weight');
        Validator::number($data['target_weight'], 'target_weight', 20, 500);

        if ((float) $data['target_weight'] === (float) $data['start_weight']) {
            throw new ValidationException('target_weight must be different from start_weight.');
        }

        Validator::required($data['target_date'], 'target_date');
        Validator::date($data['target_date'], 'target_date');

        // A new goal needs a future target date. An existing goal may keep its old date,
        // but a changed date must again be in the future.
        if ($existing === null || $data['target_date'] !== $existing['target_date']) {
            Validator::in_future($data['target_date'], 'target_date');
        }
    }
}
