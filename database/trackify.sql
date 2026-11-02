CREATE DATABASE IF NOT EXISTS trackify
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE trackify;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'member',
    current_weight DECIMAL(6,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_users_role CHECK (role IN ('admin', 'member')),
    CONSTRAINT chk_users_current_weight CHECK (current_weight > 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    `date` DATE NOT NULL,
    total_calories INT NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_workouts_total_calories CHECK (total_calories >= 0),
    CONSTRAINT fk_workouts_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_workouts_user_date (user_id, `date`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    muscle_group VARCHAR(60) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workout_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_id INT NOT NULL,
    exercise_id INT NOT NULL,
    duration INT NOT NULL,
    sets INT NOT NULL,
    reps INT NOT NULL,
    weight DECIMAL(7,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_workout_exercises_duration CHECK (duration > 0),
    CONSTRAINT chk_workout_exercises_sets CHECK (sets > 0),
    CONSTRAINT chk_workout_exercises_reps CHECK (reps > 0),
    CONSTRAINT chk_workout_exercises_weight CHECK (weight >= 0),
    CONSTRAINT fk_workout_exercises_workout FOREIGN KEY (workout_id)
        REFERENCES workouts (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_workout_exercises_exercise FOREIGN KEY (exercise_id)
        REFERENCES exercises (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_workout_exercises_workout (workout_id),
    INDEX idx_workout_exercises_exercise (exercise_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    target_weight DECIMAL(6,2) NOT NULL,
    start_weight DECIMAL(6,2) NOT NULL,
    target_date DATE NOT NULL,
    workouts_completed INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_goals_target_weight CHECK (target_weight > 0),
    CONSTRAINT chk_goals_start_weight CHECK (start_weight > 0),
    CONSTRAINT chk_goals_workouts_completed CHECK (workouts_completed >= 0),
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_goals_user_target_date (user_id, target_date)
) ENGINE=InnoDB;

INSERT INTO users (id, name, email, password, role, current_weight) VALUES
    (1, 'Amina Hadzic', 'admin@trackify.local', '$2y$10$Dpu0ZcokZ/9qqvQRBw658OtG1eGdWReEJS.J2ohWBlJUyGMUwLuJS', 'admin', 68.50),
    (2, 'Marko Petrovic', 'marko@trackify.local', '$2y$10$Dpu0ZcokZ/9qqvQRBw658OtG1eGdWReEJS.J2ohWBlJUyGMUwLuJS', 'member', 82.00),
    (3, 'Lejla Kovac', 'lejla@trackify.local', '$2y$10$Dpu0ZcokZ/9qqvQRBw658OtG1eGdWReEJS.J2ohWBlJUyGMUwLuJS', 'member', 61.25)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    email = VALUES(email),
    password = VALUES(password),
    role = VALUES(role),
    current_weight = VALUES(current_weight);

INSERT INTO exercises (id, name, description, muscle_group) VALUES
    (1, 'Barbell Squat', 'Compound lower-body lift performed with a barbell.', 'Legs'),
    (2, 'Push-up', 'Bodyweight press targeting the chest, shoulders and triceps.', 'Chest'),
    (3, 'Dumbbell Row', 'Single-arm pull focused on the upper back.', 'Back'),
    (4, 'Running', 'Steady-paced outdoor or treadmill cardiovascular exercise.', 'Cardio')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    muscle_group = VALUES(muscle_group);

INSERT INTO workouts (id, user_id, `date`, total_calories, notes) VALUES
    (1, 2, '2026-10-19', 320, 'Lower-body strength session'),
    (2, 2, '2026-10-21', 210, 'Upper-body strength session'),
    (3, 2, '2026-10-24', 410, 'Easy weekend run'),
    (4, 3, '2026-10-20', 180, 'Short strength session'),
    (5, 3, '2026-10-23', 260, 'Steady outdoor run')
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    `date` = VALUES(`date`),
    total_calories = VALUES(total_calories),
    notes = VALUES(notes);

INSERT INTO workout_exercises (id, workout_id, exercise_id, duration, sets, reps, weight) VALUES
    (1, 1, 1, 35, 4, 8, 50.00),
    (2, 1, 4, 15, 1, 1, 0.00),
    (3, 2, 2, 20, 3, 12, 0.00),
    (4, 2, 3, 25, 3, 10, 18.00),
    (5, 3, 4, 40, 1, 1, 0.00),
    (6, 4, 2, 15, 3, 10, 0.00),
    (7, 5, 4, 30, 1, 1, 0.00)
ON DUPLICATE KEY UPDATE
    workout_id = VALUES(workout_id),
    exercise_id = VALUES(exercise_id),
    duration = VALUES(duration),
    sets = VALUES(sets),
    reps = VALUES(reps),
    weight = VALUES(weight);

INSERT INTO goals (id, user_id, target_weight, start_weight, target_date, workouts_completed) VALUES
    (1, 2, 78.00, 82.00, '2027-01-31', 3),
    (2, 2, 80.00, 82.00, '2026-12-31', 8),
    (3, 3, 58.00, 61.25, '2027-02-15', 2)
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    target_weight = VALUES(target_weight),
    start_weight = VALUES(start_weight),
    target_date = VALUES(target_date),
    workouts_completed = VALUES(workouts_completed);
