# Minimal PHP Ticketing System

This is a simple RESTful PHP-based ticketing system built without any frameworks. It supports basic functionality such as user authentication, ticket creation, and department categorization.

## 🧰 Requirements

Make sure the following dependencies are installed on your system:

- **PHP** (>= 7.4 recommended)
- **MySQL** or **MariaDB**
- **Redis** server
- **PHP Redis client** (e.g., `php-redis` extension)

## 🚀 Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/your-repo/ticketing-system.git
   cd ticketing-system
Import Database

Use the included SQL file (database/ticketing.sql) to create the required tables:

bash
Copy
Edit
mysql -u your_user -p your_database < database/ticketing.sql
A default admin user is created with the following credentials:

Email: admin@gmail.com

Password: 12345678

Set Up Configuration

Open the file helpers/database.php

Update the database and Redis configuration with your credentials:

php
Copy
Edit
// Example: helpers/database.php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ticketing_system';

$redisHost = '127.0.0.1';
$redisPort = 6379;
Create uploads/ Folder

Create the uploads directory if it doesn’t exist:

bash
Copy
Edit
mkdir -p uploads
chmod -R 775 uploads
🖥️ Running the Project
🅇 If you're using XAMPP:
Place the project folder in htdocs/

Start Apache and MySQL from the XAMPP control panel

Visit: http://localhost/your-project-folder

🅽 If you're using Nginx:
Set the document root to the public/ folder (or the main folder if not structured)

Ensure PHP is configured with php-fpm

Restart Nginx after setup

Example server block:

nginx
Copy
Edit
server {
    listen 80;
    server_name localhost;

    root /path/to/your/project;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
📄 API Documentation
Full API documentation is included in the project under the docs/ folder or accessible via /docs route if set up.

🧪 Testing
You can use tools like Postman or cURL to test the available API endpoints. Make sure Redis and MySQL services are running properly.