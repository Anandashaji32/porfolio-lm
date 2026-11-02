<?php
/**
 * Database Configuration & Connection
 * Supports both SQLite (zero configuration, instant setup) and MySQL.
 */

define('DB_DRIVER', 'sqlite'); // Options: 'sqlite' or 'mysql'

// MySQL Credentials (used when DB_DRIVER is set to 'mysql')
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// SQLite Database Path
define('SQLITE_FILE', __DIR__ . '/../data/library.db');

function getDBConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        if (DB_DRIVER === 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } else {
            // SQLite
            $dir = dirname(SQLITE_FILE);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $pdo = new PDO("sqlite:" . SQLITE_FILE, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            // Enable foreign keys in SQLite
            $pdo->exec("PRAGMA foreign_keys = ON;");
        }

        // Check if database is initialized, if not initialize tables & seed data
        initDatabase($pdo);

        return $pdo;
    } catch (PDOException $e) {
        die("<div style='font-family:sans-serif;padding:20px;margin:20px;border-left:4px solid #ef4444;background:#fef2f2;color:#991b1b;'>
            <h3 style='margin:0 0 8px'>Database Connection Error</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p><small>If using MySQL, make sure MySQL is started in XAMPP or set DB_DRIVER to 'sqlite' in config/db.php.</small></p>
        </div>");
    }
}

/**
 * Automatically creates tables and seeds starter data if not already present
 */
function initDatabase($pdo) {
    // Check if `users` table exists
    $tableExists = false;
    try {
        $test = $pdo->query("SELECT 1 FROM users LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {
        $tableExists = false;
    }

    if ($tableExists) {
        return;
    }

    $isSqlite = (DB_DRIVER === 'sqlite');

    if ($isSqlite) {
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'member', -- 'admin' or 'member'
            phone TEXT,
            address TEXT,
            avatar TEXT DEFAULT 'assets/images/default-avatar.png',
            status TEXT DEFAULT 'active', -- 'active', 'suspended'
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            icon TEXT DEFAULT 'fas fa-book',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            isbn TEXT UNIQUE,
            category_id INTEGER,
            publisher TEXT,
            published_year INTEGER,
            total_copies INTEGER NOT NULL DEFAULT 1,
            available_copies INTEGER NOT NULL DEFAULT 1,
            rack_location TEXT,
            description TEXT,
            cover_image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS issues (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            book_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            issue_date DATE NOT NULL,
            due_date DATE NOT NULL,
            return_date DATE,
            fine_amount REAL DEFAULT 0.0,
            status TEXT NOT NULL DEFAULT 'issued', -- 'issued', 'returned', 'overdue'
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ";
    } else {
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
            phone VARCHAR(25),
            address TEXT,
            avatar VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
            status ENUM('active', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'fas fa-book',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS books (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(150) NOT NULL,
            isbn VARCHAR(30) UNIQUE,
            category_id INT,
            publisher VARCHAR(150),
            published_year INT,
            total_copies INT NOT NULL DEFAULT 1,
            available_copies INT NOT NULL DEFAULT 1,
            rack_location VARCHAR(50),
            description TEXT,
            cover_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS issues (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            user_id INT NOT NULL,
            issue_date DATE NOT NULL,
            due_date DATE NOT NULL,
            return_date DATE,
            fine_amount DECIMAL(8,2) DEFAULT 0.00,
            status ENUM('issued', 'returned', 'overdue') NOT NULL DEFAULT 'issued',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
    }

    $pdo->exec($schema);

    // Seed Initial Data: Admin, Member, Categories, Books, Sample Issues
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $memberPassword = password_hash('student123', PASSWORD_DEFAULT);

    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtUser->execute(['System Librarian', 'admin@library.com', $adminPassword, 'admin', '+1-555-0100', 'Central Library Administration', 'active']);
    $stmtUser->execute(['Alex Johnson', 'student@library.com', $memberPassword, 'member', '+1-555-0101', 'Department of Computer Science', 'active']);
    $stmtUser->execute(['Sophia Martinez', 'sophia@library.com', $memberPassword, 'member', '+1-555-0102', 'Department of Literature', 'active']);

    // Seed Categories
    $categories = [
        ['Computer Science & IT', 'Books on programming, algorithms, networks, AI, and systems.', 'fas fa-laptop-code'],
        ['Classic Literature', 'Novels, drama, poetry, and world classics.', 'fas fa-feather-alt'],
        ['Science & Physics', 'Theoretical physics, astronomy, and general sciences.', 'fas fa-atom'],
        ['Mathematics', 'Calculus, linear algebra, discrete math, and statistics.', 'fas fa-square-root-alt'],
        ['World History & Civilizations', 'Ancient, medieval, and modern world history.', 'fas fa-landmark'],
        ['Business & Economics', 'Finance, management, entrepreneurship, and economics.', 'fas fa-chart-line'],
    ];

    $stmtCat = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
    }

    // Seed Books
    $books = [
        [
            'Clean Code: A Handbook of Agile Software Craftsmanship',
            'Robert C. Martin',
            '978-0132350884',
            1,
            'Prentice Hall',
            2008,
            5,
            4,
            'Rack CS-01',
            'Even bad code can function. But if code is not clean, it can bring a development organization to its knees.',
            'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=400&q=80'
        ],
        [
            'Introduction to Algorithms (CLRS)',
            'Thomas H. Cormen, Charles E. Leiserson',
            '978-0262033848',
            1,
            'MIT Press',
            2009,
            4,
            3,
            'Rack CS-02',
            'A comprehensive update of the leading algorithms text, with new material on matchings in bipartite graphs, online algorithms, and machine learning.',
            'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80'
        ],
        [
            'The Great Gatsby',
            'F. Scott Fitzgerald',
            '978-0743273565',
            2,
            'Scribner',
            1925,
            6,
            5,
            'Rack LIT-03',
            'The story of the mysteriously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.',
            'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'
        ],
        [
            'A Brief History of Time',
            'Stephen Hawking',
            '978-0553380163',
            3,
            'Bantam Books',
            1988,
            3,
            2,
            'Rack SCI-01',
            'From the big bang to black holes, Stephen Hawking explores the fundamental questions about the universe.',
            'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&q=80'
        ],
        [
            'Design Patterns: Elements of Reusable Object-Oriented Software',
            'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
            '978-0201633610',
            1,
            'Addison-Wesley',
            1994,
            4,
            4,
            'Rack CS-03',
            'Captures a wealth of experience in design patterns that make object-oriented designs more flexible, elegant, and ultimately reusable.',
            'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=400&q=80'
        ],
        [
            'To Kill a Mockingbird',
            'Harper Lee',
            '978-0060935467',
            2,
            'Harper Perennial',
            1960,
            5,
            5,
            'Rack LIT-01',
            'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.',
            'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400&q=80'
        ],
        [
            'Sapiens: A Brief History of Humankind',
            'Yuval Noah Harari',
            '978-0062316097',
            5,
            'Harper',
            2014,
            5,
            4,
            'Rack HIS-02',
            'From renowned historian Yuval Noah Harari comes a groundbreaking narrative of humanity\'s creation and evolution.',
            'https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=400&q=80'
        ],
        [
            'Thinking, Fast and Slow',
            'Daniel Kahneman',
            '978-0374533557',
            6,
            'Farrar, Straus and Giroux',
            2011,
            3,
            3,
            'Rack BUS-04',
            'The phenomenal international bestseller on the two systems that drive the way we think—System 1 and System 2.',
            'https://images.unsplash.com/photo-1507842229458-577741364d23?w=400&q=80'
        ]
    ];

    $stmtBook = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, publisher, published_year, total_copies, available_copies, rack_location, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($books as $b) {
        $stmtBook->execute($b);
    }

    // Seed Sample Issues
    $today = date('Y-m-d');
    $dueDate1 = date('Y-m-d', strtotime('+14 days'));
    $overdueDate = date('Y-m-d', strtotime('-5 days'));
    $pastIssueDate = date('Y-m-d', strtotime('-19 days'));

    $stmtIssue = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, return_date, fine_amount, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    // Active issue
    $stmtIssue->execute([1, 2, $today, $dueDate1, null, 0.0, 'issued', 'Semester research project']);
    // Overdue issue
    $stmtIssue->execute([2, 3, $pastIssueDate, $overdueDate, null, 5.0, 'overdue', 'Overdue by 5 days ($1/day)']);
    // Returned issue
    $stmtIssue->execute([4, 2, date('Y-m-d', strtotime('-30 days')), date('Y-m-d', strtotime('-16 days')), date('Y-m-d', strtotime('-18 days')), 0.0, 'returned', 'Returned in good condition']);

    // Log seed activity
    $pdo->exec("INSERT INTO activity_logs (user_id, action, details) VALUES (1, 'System Initialized', 'Database tables and sample library records installed successfully.')");
}
