# Publication_Management_System

A PHP and MySQL-based publication/bookstore management system designed to manage publishers, titles, authors, employees, jobs, and publisher reports through a web dashboard.

## Project Overview

This project provides a simple database management system for a publication/bookstore environment. It uses PHP for the web interface, MySQL for database storage, and XAMPP as the local development server.

The system includes modules for:

* Managing publishers and book titles
* Managing publishers and employees
* Managing authors and their titles
* Viewing publisher reports and book counts
* Adding job positions through an admin page
* Running SQL queries required for Part 3

## Technologies Used

* PHP
* MySQL / MariaDB
* XAMPP
* HTML
* CSS
* JavaScript
* phpMyAdmin

## Project Structure

```text
bookstore/
├── db_connect.php
├── index.php
├── task1.php
├── task2.php
├── task3.php
├── task4.php
├── add_job.php
├── Part3_Queries_SQL.sql
├── grp6bookstore.sql
├── test_custom_db.php
└── README.md
```

## Main Files

| File                    | Description                                                          |
| ----------------------- | -------------------------------------------------------------------- |
| `db_connect.php`        | Handles the database connection settings.                            |
| `index.php`             | Main dashboard of the system.                                        |
| `task1.php`             | Manages publishers and titles.                                       |
| `task2.php`             | Manages publishers and employees.                                    |
| `task3.php`             | Manages authors and titles.                                          |
| `task4.php`             | Displays publisher reports and book counts.                          |
| `add_job.php`           | Admin page for adding job positions.                                 |
| `grp6bookstore.sql`     | Creates the `pubs` database, tables, relationships, and sample data. |
| `Part3_Queries_SQL.sql` | Contains the SQL queries required for Part 3.                        |
| `test_custom_db.php`    | Optional database connection test file.                              |

## Database Name

The project uses the database name:

```sql
pubs
```

Make sure that `db_connect.php` contains the same database name:

```php
$dbname = "pubs";
```

## How to Run the Project Locally

### 1. Install XAMPP

Download and install XAMPP on your computer.

### 2. Move the Project Folder

Place the project folder inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\bookstore\
```

### 3. Start Apache and MySQL

Open the XAMPP Control Panel, then start:

* Apache
* MySQL

### 4. Import the Database

1. Open your browser.
2. Go to:

```text
http://localhost/phpmyadmin/
```

3. Click **Import**.
4. Choose the file:

```text
grp6bookstore.sql
```

5. Click **Go** or **Import**.
6. Confirm that the `pubs` database appears in phpMyAdmin.

### 5. Check the Database Connection

Open `db_connect.php` and confirm the settings:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pubs";
```

### 6. Open the System

In your browser, go to:

```text
http://localhost/bookstore/index.php
```

You should now see the PUBS Database Management System dashboard.

## System Modules

### Publishers & Titles

This module allows users to create publishers and add book titles linked to selected publishers and authors.

### Publishers & Employees

This module allows users to create publishers and add employees under a selected publisher.

### Authors & Titles

This module allows users to add authors and view or add titles linked to a selected author.

### Publisher Reports

This module displays publishers, their published titles, and the total number of books per publisher.

### Admin Add Job

This page allows admin users to add new job positions that can be assigned to employees.

## Part 3 SQL Queries

The file `Part3_Queries_SQL.sql` includes the SQL queries required for Part 3, such as:

* Authors with publishers from different cities and states
* Authors without books
* Authors without linked titles using different query methods
* Highest-priced title per author

Run these queries in phpMyAdmin under the `pubs` database.

## Troubleshooting

### Access denied for user `root`

Check if your MySQL password is empty. For a default XAMPP setup, the password is usually blank.

### Unknown database `pubs`

Import `grp6bookstore.sql` again using phpMyAdmin.

### Object not found / 404 error

Make sure the folder path is correct:

```text
C:\xampp\htdocs\bookstore\
```

Also make sure the URL is:

```text
http://localhost/bookstore/index.php
```

### Page loads but data is missing

Check if the SQL file was imported successfully and if the tables contain sample records.

## Notes

This project is intended for academic use and local testing. It is not yet configured for production deployment.

## Contributors

Group 6
