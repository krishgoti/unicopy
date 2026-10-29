-- Seed default admin user
-- Email: admin@gmail.com
-- Password: admin123

INSERT INTO `users` (`email`, `password`, `role`, `status`) 
VALUES ('admin@gmail.com', '$2y$10$dilS0VmI9GPpQMOX85JBZOSM2pjI3DVgmeOO0lR4C9erCtuyCMOsm', 'admin', 'active');

SET @admin_id = LAST_INSERT_ID();

INSERT INTO `user_profiles` (`user_id`, `full_name`, `department`) 
VALUES (@admin_id, 'System Administrator', 'Administration');
