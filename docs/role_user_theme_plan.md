# Implementation Plan - AdminLTE Role & User Management and Theme Switcher Fix

This plan details the design and implementation of:
1. **Super Admin Role & Permissions Bypass**: Super Admin role that has access to everything.
2. **User Management CRUD**: Interface for managing system users.
3. **Role Management CRUD**: Interface for managing Spatie roles and permissions.
4. **Theme Switcher Fix**: Correcting the AdminLTE theme switcher to toggle dark mode for the entire panel consistently.

---

## Proposed Changes

### 1. Theme Switcher Fix

#### [MODIFY] [admin.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/layouts/admin.blade.php)
- Update the HTML initialization script in the `<head>` to add the `dark-mode` class to the `document.body` as soon as the DOM is parsed.
- Update the jQuery `applyTheme` script at the bottom to toggle the `dark-mode` class on both the `<html>` and `<body>` tags.
- Update CSS override selectors (e.g. `.dark-mode body, body.dark-mode`) to style components correctly when switched at runtime.

---

### 2. Role & Permission Architecture

#### [MODIFY] [AppServiceProvider.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Providers/AppServiceProvider.php)
- Register a Spatie Permission Super Admin Gate bypass in the `boot()` method:
  ```php
  Gate::before(function ($user, $ability) {
      return $user->hasRole('super_admin') ? true : null;
  });
  ```

#### [MODIFY] [DatabaseSeeder.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/database/seeders/DatabaseSeeder.php)
- Seed the `super_admin` role.
- Seed specific Spatie permissions (`manage_users`, `manage_roles`, `verify_teachers`, `post_jobs`, `book_jobs`).
- Assign all permissions to `super_admin` and appropriate ones to other roles.
- Create and seed a default Super Admin user:
  - **Email**: `admin@example.com`
  - **Password**: `password`

---

### 3. Routing & Controllers

#### [MODIFY] [web.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/routes/web.php)
- Redirect `super_admin` role from `/dashboard` to `admin.dashboard`.
- Create a route group prefix `admin` protected by `auth` and `role:super_admin` middleware:
  - `/admin/dashboard` -> `DashboardController@superAdminDashboard`
  - `/admin/users` (Resource Controller for User CRUD)
  - `/admin/roles` (Resource Controller for Role CRUD)

#### [MODIFY] [DashboardController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/DashboardController.php)
- Add `superAdminDashboard()` action aggregating stats (Total Users, Roles, Districts, Jobs) and fetching list tables for the overview.

#### [NEW] [UserController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/Admin/UserController.php)
- Implement User CRUD. Support creating/editing users, password updates, assigning Spatie roles, and creating profile relations as necessary.

#### [NEW] [RoleController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/Admin/RoleController.php)
- Implement Role CRUD. Support creating/editing roles, and checking/syncing Spatie permissions.

---

### 4. Admin Management Views

#### [NEW] [dashboard.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/dashboard.blade.php)
- Super Admin Dashboard view with stats widgets (cards) and overview tables of recent users/roles.

#### [NEW] [index.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/users/index.blade.php)
- Users list page using AdminLTE table formatting.

#### [NEW] [create.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/users/create.blade.php) / [edit.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/users/edit.blade.php)
- User Add/Edit forms with name, email, password fields and role checkboxes.

#### [NEW] [index.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/roles/index.blade.php)
- Roles and permissions overview list.

#### [NEW] [create.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/roles/create.blade.php) / [edit.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/admin/roles/edit.blade.php)
- Role Add/Edit forms with checkboxes to assign/toggle permissions.

#### [MODIFY] [edit.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/profile/edit.blade.php)
- Update dynamic sidebar generation to support the Super Admin navigation menu.

---

## Verification Plan

### Automated Tests
- Run automated tests to check all authentication and profile functions are untouched:
  ```powershell
  php artisan test
  ```
- Write test assertions for:
  - Super Admin role redirects from dashboard.
  - Access to User Management and Role Management is blocked for non-super admins.
  - Super Admin can successfully fetch User and Role indexes.

### Manual Verification
- Log in as the seeded Super Admin (`admin@example.com` / `password`).
- Test switching themes and verify that the content wrappers, headers, background colors, and layouts toggle dark mode correctly.
- Access User Management: Create a test user, edit their name/roles, and delete.
- Access Role Management: Create a test role, edit permissions, and delete.
