-- Athenaeum Library Management System
-- MySQL Schema & Starter Dataset

CREATE DATABASE IF NOT EXISTS `library_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_db`;

-- Drop existing tables if needed
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `issues`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  `phone` VARCHAR(25) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
  `status` ENUM('active', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'fas fa-book',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Books Table
CREATE TABLE `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(150) NOT NULL,
  `isbn` VARCHAR(30) UNIQUE DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `publisher` VARCHAR(150) DEFAULT NULL,
  `published_year` INT DEFAULT NULL,
  `total_copies` INT NOT NULL DEFAULT 1,
  `available_copies` INT NOT NULL DEFAULT 1,
  `rack_location` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Issues (Loans) Table
CREATE TABLE `issues` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `book_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `fine_amount` DECIMAL(8,2) DEFAULT 0.00,
  `status` ENUM('issued', 'returned', 'overdue') NOT NULL DEFAULT 'issued',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs Table
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Demo Passwords (admin123 and student123)
-- Admin: admin@library.com / admin123
-- Member: student@library.com / student123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `status`) VALUES
(1, 'System Librarian', 'admin@library.com', '$2y$10$wE0pD/V7r0hP1P1yC1a4mOqTfB9aZqF3xJzJm9Gk8uD5gL4/sI0u6', 'admin', '+1-555-0100', 'Central Library Administration', 'active'),
(2, 'Alex Johnson', 'student@library.com', '$2y$10$Qj2z2y2gqM8L6N3t8gZJCeJ9WnLzH1yY7bF8dD4eF0aB5c6e7g8hi', 'member', '+1-555-0101', 'Department of Computer Science', 'active'),
(3, 'Sophia Martinez', 'sophia@library.com', '$2y$10$Qj2z2y2gqM8L6N3t8gZJCeJ9WnLzH1yY7bF8dD4eF0aB5c6e7g8hi', 'member', '+1-555-0102', 'Department of Literature', 'active');

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Computer Science & IT', 'Books on programming, algorithms, networks, AI, and systems.', 'fas fa-laptop-code'),
(2, 'Classic Literature', 'Novels, drama, poetry, and world classics.', 'fas fa-feather-alt'),
(3, 'Science & Physics', 'Theoretical physics, astronomy, and general sciences.', 'fas fa-atom'),
(4, 'Mathematics', 'Calculus, linear algebra, discrete math, and statistics.', 'fas fa-square-root-alt'),
(5, 'World History & Civilizations', 'Ancient, medieval, and modern world history.', 'fas fa-landmark'),
(6, 'Business & Economics', 'Finance, management, entrepreneurship, and economics.', 'fas fa-chart-line');

-- Seed Books
INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category_id`, `publisher`, `published_year`, `total_copies`, `available_copies`, `rack_location`, `description`, `cover_image`) VALUES
(1, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '978-0132350884', 1, 'Prentice Hall', 2008, 5, 4, 'Rack CS-01', 'Even bad code can function. But if code is not clean, it can bring a development organization to its knees.', 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=400&q=80'),
(2, 'Introduction to Algorithms (CLRS)', 'Thomas H. Cormen, Charles E. Leiserson', '978-0262033848', 1, 'MIT Press', 2009, 4, 3, 'Rack CS-02', 'A comprehensive update of the leading algorithms text, with new material on matchings in bipartite graphs, online algorithms, and machine learning.', 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80'),
(3, 'The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 2, 'Scribner', 1925, 6, 5, 'Rack LIT-03', 'The story of the mysteriously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'),
(4, 'A Brief History of Time', 'Stephen Hawking', '978-0553380163', 3, 'Bantam Books', 1988, 3, 2, 'Rack SCI-01', 'From the big bang to black holes, Stephen Hawking explores the fundamental questions about the universe.', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&q=80'),
(5, 'Design Patterns: Elements of Reusable Object-Oriented Software', 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides', '978-0201633610', 1, 'Addison-Wesley', 1994, 4, 4, 'Rack CS-03', 'Captures a wealth of experience in design patterns that make object-oriented designs more flexible, elegant, and ultimately reusable.', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=400&q=80'),
(6, 'To Kill a Mockingbird', 'Harper Lee', '978-0060935467', 2, 'Harper Perennial', 1960, 5, 5, 'Rack LIT-01', 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400&q=80'),
(7, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '978-0062316097', 5, 'Harper', 2014, 5, 4, 'Rack HIS-02', 'From renowned historian Yuval Noah Harari comes a groundbreaking narrative of humanity''s creation and evolution.', 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=400&q=80'),
(8, 'Thinking, Fast and Slow', 'Daniel Kahneman', '978-0374533557', 6, 'Farrar, Straus and Giroux', 2011, 3, 3, 'Rack BUS-04', 'The phenomenal international bestseller on the two systems that drive the way we think—System 1 and System 2.', 'https://images.unsplash.com/photo-1507842229458-577741364d23?w=400&q=80');

-- Seed Sample Circulation Issues
INSERT INTO `issues` (`id`, `book_id`, `user_id`, `issue_date`, `due_date`, `return_date`, `fine_amount`, `status`, `notes`) VALUES
(1, 1, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), NULL, 0.00, 'issued', 'Semester research project'),
(2, 2, 3, DATE_SUB(CURDATE(), INTERVAL 19 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), NULL, 5.00, 'overdue', 'Overdue by 5 days ($1/day)'),
(3, 4, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), 0.00, 'returned', 'Returned in pristine condition');

-- Activity Log
INSERT INTO `activity_logs` (`user_id`, `action`, `details`) VALUES
(1, 'System Initialized', 'Database tables and sample library records installed successfully.');
