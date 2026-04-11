# Grading System

This project is a Laravel-based academic grading system for managing students, courses, assessments, marks, roles, and permissions in one platform.

The system supports role-based access control, academic-year scoped records, and dashboard analytics for mark performance and enrollment tracking.

## Core Features

- User management with multi-role assignment (Spatie roles)
- Role CRUD and role-permission management
- Department, student, course, and course-user management
- Academic year management with active year support
- Enrollment management (including bulk upload)
- Assessment and mark management (including bulk upload)
- Dashboard metrics and chart analytics

## Dashboard Highlights

- Summary cards for users, students, departments, courses, active academic year, and active-year enrollments
- Enrollment distribution by course for the active academic year
- Mark analysis chart by course showing:
    - average student total
    - minimum student total
    - maximum student total
- Mark analytics are scoped to the active academic year

## Authentication and Access

- Authentication is enabled (login, logout, password reset)
- Self-registration is disabled
- New user accounts are expected to be created by authorized administrators through the system

## Technology Stack

- Laravel (PHP)
- Blade templates
- Tailwind CSS
- Vite
- Chart.js
- Spatie laravel-permission
- MySQL

## Main Modules

- Users
- Roles
- Role Permissions
- Departments
- Students
- Academic Years
- Courses
- Course Users
- Enrollments
- Assessments
- Marks
- Dashboard

## Setup Instructions

1. Clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Create and configure environment file:

```bash
cp .env.example .env
php artisan key:generate
```

5. Update database credentials in `.env`.
6. Run migrations and seeders:

```bash
php artisan migrate --seed
```

7. Start the development servers:

```bash
php artisan serve
npm run dev
```

## Notes

- Ensure at least one `academic_years` record is marked as current (`is_current = true`) to enable active-year dashboard metrics.
- Since registration routes are disabled, manage users from the authenticated admin area.

## License

This project is open-sourced software licensed under the MIT license.
