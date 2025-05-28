-- Users table
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL UNIQUE,
    password_hash varchar(255) NOT NULL,
    role enum('admin', 'agent', 'user') NOT NULL DEFAULT 'user'
);

-- Departments table
CREATE TABLE departments (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL UNIQUE
);

-- Tickets table
CREATE TABLE tickets (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description TEXT NOT NULL,
    status enum('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open',
    user_id INTEGER NOT NULL,
    department_id INTEGER NOT NULL,
    assigned_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (assigned_id) REFERENCES users(id)
);

-- Ticket notes table
CREATE TABLE ticket_notes (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    ticket_id INTEGER NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);

-- Ticket attachments table
CREATE TABLE ticket_attachments (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    ticket_id INTEGER NOT NULL,
    file_path varchar(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INTEGER NOT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);