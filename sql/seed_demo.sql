-- Optional demo data: sample verified users + sample completed test results
-- Run AFTER schema.sql and seed_questions.sql
-- Demo password for all sample users: Password123!

INSERT INTO users (name, email, password_hash, is_verified) VALUES
('Ava Carter',  'ava@example.com',  '$2y$10$wH8u1z3qg6xK9oM5f0aB1uVbqD0i7c4y6Y8H2J7K1L3M5N7P9Q1Re', 1),
('Liam Patel',  'liam@example.com', '$2y$10$wH8u1z3qg6xK9oM5f0aB1uVbqD0i7c4y6Y8H2J7K1L3M5N7P9Q1Re', 1),
('Noah Kim',    'noah@example.com', '$2y$10$wH8u1z3qg6xK9oM5f0aB1uVbqD0i7c4y6Y8H2J7K1L3M5N7P9Q1Re', 1);

-- NOTE: the hash above is a placeholder. Reset passwords from the app's
-- "Forgot Password" flow, or register a fresh account to log in. Sample
-- analytics below let the dashboard look populated immediately.

INSERT INTO tests (user_id, started_at, completed_at, time_taken_seconds, status) VALUES
(1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY + INTERVAL 18 MINUTE, 1080, 'completed'),
(1, NOW() - INTERVAL 5 DAY,  NOW() - INTERVAL 5 DAY  + INTERVAL 16 MINUTE, 960,  'completed'),
(1, NOW() - INTERVAL 1 DAY,  NOW() - INTERVAL 1 DAY  + INTERVAL 14 MINUTE, 840,  'completed'),
(2, NOW() - INTERVAL 7 DAY,  NOW() - INTERVAL 7 DAY  + INTERVAL 19 MINUTE, 1140, 'completed'),
(3, NOW() - INTERVAL 2 DAY,  NOW() - INTERVAL 2 DAY  + INTERVAL 17 MINUTE, 1020, 'completed');

INSERT INTO results (test_id, user_id, raw_score, total_questions, percentage, iq_estimate, category_breakdown, time_taken_seconds) VALUES
(1, 1, 21, 30, 70.00, 112, '{"Pattern Recognition":75,"Numerical Reasoning":70,"Verbal Reasoning":65,"Logical Reasoning":72,"Analytical Thinking":68}', 1080),
(2, 1, 24, 30, 80.00, 118, '{"Pattern Recognition":82,"Numerical Reasoning":74,"Verbal Reasoning":78,"Logical Reasoning":85,"Analytical Thinking":80}', 960),
(3, 1, 27, 30, 90.00, 126, '{"Pattern Recognition":92,"Numerical Reasoning":86,"Verbal Reasoning":88,"Logical Reasoning":94,"Analytical Thinking":90}', 840),
(4, 2, 22, 30, 73.33, 114, '{"Pattern Recognition":70,"Numerical Reasoning":76,"Verbal Reasoning":72,"Logical Reasoning":74,"Analytical Thinking":74}', 1140),
(5, 3, 25, 30, 83.33, 120, '{"Pattern Recognition":84,"Numerical Reasoning":80,"Verbal Reasoning":82,"Logical Reasoning":86,"Analytical Thinking":84}', 1020);
