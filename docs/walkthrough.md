# AltTeacher-AI Project Walkthrough

This document records the modifications made and verified during each phase of development for the **AltTeacher-AI** platform.

---

## Phase 0: Project Setup & Repository Connection
- **Git Repository Initialized:** Set up Git tracking inside `C:\xampp\htdocs\My Works\Infinity AI Buildfest 2026\AltTeacher-AI`.
- **Remote Origin Connected:** Connected the local workspace to the remote repository: `https://github.com/Tauhid219/AltTeacher-AI.git`.
- **Initial Commit & Push:** Pushed the default Laravel skeleton files to the `main` branch.

---

## Phase 1: Database Architecture & Core Models
We designed and implemented the entire relational schema for the substitute teacher management platform:

### 1. Migrations Created & Executed
- `districts`: Stores school districts and their associated compliance rules (e.g. weekly hours cap).
- `school_profiles`: Stores school details and links them to users and districts.
- `teacher_profiles`: Stores teacher preferences, hourly rate, availability calendars, and onboarding approval status.
- `credentials`: Handles uploads of teacher certifications, IDs, background check statuses, and expiration dates.
- `substitute_jobs`: Custom job postings table. Naming avoided Laravel's built-in queue `jobs` conflict.
- `bookings`: Maps teacher assignments to jobs.
- `timesheets`: Records check-in/out stamps, computed hours, pay, and invoice verification.
- `lesson_plans`: Stores original class plans and will store future AI-adapted summaries, quizzes, and icebreaker games.

### 2. Eloquent Models Implemented
All models were created with fillable attributes, relations (e.g. `hasMany`, `belongsTo`, `hasOne`), and correct JSON/date casts:
- `District`
- `SchoolProfile`
- `TeacherProfile`
- `Credential`
- `SubstituteJob`
- `Booking`
- `Timesheet`
- `LessonPlan`
- `User` (extended with `schoolProfile` and `teacherProfile` relationships)

### 3. Realistic Mock Seeder
Implemented in [DatabaseSeeder.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/database/seeders/DatabaseSeeder.php) containing real-world districts, school principals, teachers (such as Bob Terwilliger, Lisa Simpson), licenses, past jobs, check-ins, and mock timesheets.

### 4. Tests Written & Verified
Created [DatabaseSetupTest.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/DatabaseSetupTest.php) to assert:
- Districts are correctly seeded and JSON properties cast to array correctly.
- User profiles and school relationships load successfully.
- Completed jobs correctly reference bookings, timesheets, and lesson plans.

All tests passed successfully:
```bash
Tests:    5 passed (21 assertions)
Duration: 1.25s
```

---

## Phase 2: Authentication (Laravel Breeze) & RBAC (Spatie)
We integrated auth scaffolding and Spatie roles to handle role-based permissions and onboarding redirects:

### 1. Packages Installed & Migrations Completed
- **Laravel Breeze:** Configured standard Blade authentication stack, generating Login, Register, Password Reset, and profile management systems.
- **Spatie Laravel Permission:** Installed version `6.25` (compatible with PHP 8.2), published and ran Spatie schema migrations successfully.

### 2. Seeding Spatie Roles
Updated [DatabaseSeeder.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/database/seeders/DatabaseSeeder.php) to:
- Seed three distinct roles: `teacher`, `school_admin`, and `district_admin`.
- Automatically assign the correct Spatie roles to our pre-seeded users.

### 3. Role-Based Public Registration
- Updated [register.blade.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/register.blade.php) view to include a "Register As" selector (`Substitute Teacher` or `School Admin`).
- Updated [RegisteredUserController.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/Auth/RegisteredUserController.php) to validate the role, assign it to the new user, and automatically create a `TeacherProfile` or `SchoolProfile` in the database.

### 4. Middleware & Redirect Routing
- Configured Spatie route middleware aliases (`role`, `permission`) in [bootstrap/app.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/bootstrap/app.php).
- Modified the `/dashboard` route in [routes/web.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/routes/web.php) to check the authenticated user's role and automatically redirect them to their specialized subdomain endpoints (e.g. `/teacher/dashboard`, `/school/dashboard`).

### 5. Verified with Tests
- Updated `RegistrationTest` to assert that registering as a teacher creates a `TeacherProfile` and assigns the `teacher` Spatie role.
- Updated `AuthenticationTest` to assert that users logging in redirect correctly based on their roles.
- All 31 tests passed successfully:
```bash
Tests:    31 passed (94 assertions)
Duration: 21.89s
```
