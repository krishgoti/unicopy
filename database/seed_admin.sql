-- Seed default admin user
-- Email: admin@unihelp.edu
-- Password: Admin@123

INSERT INTO `users` (`email`, `password`, `role`, `status`) 
VALUES ('admin@unihelp.edu', '$2y$10$QeEW.220iYZqPppndXVthuySiwWNkz57Av.JeEjt/uN4xojduXFi2', 'admin', 'active');

SET @admin_id = LAST_INSERT_ID();

INSERT INTO `user_profiles` (`user_id`, `full_name`, `department`) 
VALUES (@admin_id, 'System Administrator', 'Administration');
