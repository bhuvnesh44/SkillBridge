-- Skillbridg Sample Seed Data
-- Demo accounts password for all users: password123

USE `skillbridg_db`;

-- Insert Master Skills
INSERT INTO `skills` (`skill_id`, `skill_name`, `category`) VALUES
(1, 'Python Programming', 'Computer Science'),
(2, 'MySQL & Database Design', 'Computer Science'),
(3, 'Web Development (HTML/CSS/JS)', 'Computer Science'),
(4, 'Core PHP Development', 'Computer Science'),
(5, 'Data Structures & Algorithms', 'Computer Science'),
(6, 'Graphic Design & UI/UX', 'Design'),
(7, 'Public Speaking & Communication', 'Soft Skills'),
(8, 'Machine Learning Basics', 'Data Science');

-- Insert Demo Users (Password: password123)
-- Hash generated via password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `bio`, `department`, `semester`) VALUES
(1, 'Rahul Sharma', 'rahul@example.com', '$2y$10$1hnVFZkcLMkPOHwvPpr5yOJ1bNRfsN5XTvCOkU3txF5yt346Id37W', 'MCA student interested in Python, Backend Development, and SQL.', 'MCA', 'Semester 1'),
(2, 'Ananya Verma', 'ananya@example.com', '$2y$10$1hnVFZkcLMkPOHwvPpr5yOJ1bNRfsN5XTvCOkU3txF5yt346Id37W', 'Passionate about Web Development, UI/UX Design, and Frontend Frameworks.', 'MCA', 'Semester 1'),
(3, 'Vikram Singh', 'vikram@example.com', '$2y$10$1hnVFZkcLMkPOHwvPpr5yOJ1bNRfsN5XTvCOkU3txF5yt346Id37W', 'Tech enthusiast focus on Core PHP, Data Structures, and Database optimization.', 'M.Tech CSE', 'Semester 2'),
(4, 'Priya Nair', 'priya@example.com', '$2y$10$1hnVFZkcLMkPOHwvPpr5yOJ1bNRfsN5XTvCOkU3txF5yt346Id37W', 'Love teaching Soft Skills and Public Speaking while learning Data Science.', 'B.Tech IT', 'Semester 6');

-- Insert User Skill Mappings
INSERT INTO `user_skills` (`user_id`, `skill_id`, `skill_type`, `proficiency_level`) VALUES
-- Rahul Sharma
(1, 1, 'teach', 'Advanced'),     -- Rahul teaches Python
(1, 2, 'teach', 'Intermediate'), -- Rahul teaches MySQL
(1, 4, 'learn', 'Beginner'),     -- Rahul wants to learn Core PHP

-- Ananya Verma
(2, 3, 'teach', 'Advanced'),     -- Ananya teaches Web Dev
(2, 6, 'teach', 'Advanced'),     -- Ananya teaches Graphic Design
(2, 1, 'learn', 'Intermediate'), -- Ananya wants to learn Python

-- Vikram Singh
(3, 4, 'teach', 'Advanced'),     -- Vikram teaches Core PHP
(3, 5, 'teach', 'Advanced'),     -- Vikram teaches Data Structures
(3, 8, 'learn', 'Beginner'),     -- Vikram wants to learn Machine Learning

-- Priya Nair
(4, 7, 'teach', 'Advanced'),     -- Priya teaches Public Speaking
(4, 8, 'teach', 'Intermediate'), -- Priya teaches ML Basics
(4, 2, 'learn', 'Beginner');     -- Priya wants to learn MySQL

-- Insert Sample Learning Requests
INSERT INTO `learning_requests` (`request_id`, `sender_id`, `receiver_id`, `skill_id`, `status`, `message`) VALUES
(1, 1, 3, 4, 'Pending', 'Hi Vikram! I saw you offer Core PHP. I would love to learn form handling and PDO from you.'),
(2, 2, 1, 1, 'Accepted', 'Hey Rahul, I am looking to get hands-on with Python fundamentals. Can you help me out?'),
(3, 4, 2, 3, 'Completed', 'Hi Ananya, could you guide me on responsive web design using standard CSS flexbox?');

-- Insert Sample Feedback for Completed Interaction
INSERT INTO `feedback` (`feedback_id`, `request_id`, `given_by_user_id`, `received_by_user_id`, `rating`, `feedback_text`) VALUES
(1, 3, 4, 2, 5, 'Ananya was super helpful and explained CSS layout concepts extremely clearly!');
