# Implementation Plan - AI Substitute Teacher Workforce & Scheduling Platform

This document describes the phase-by-phase implementation plan for building the **AltTeacher-AI** application, a workforce management and scheduling platform for substitute teachers with AI features, built using Laravel 12, Tailwind CSS v4, and AdminLTE 3 for admin panels.

---

## User Review Required

> [!IMPORTANT]
> - The application will use **MySQL** as configured in the `.env` file. Please ensure your local MySQL server (XAMPP) is running.
> - We will use the **Gemini API Key** for AI features. A fallback Mock AI service will be built so development is not blocked while the API key is being acquired.
> - The Admin Panel UI will fully match `C:\Reza\Tauhid\Templates\AdminLTE-3.1.0` (full viewport style).
> - We will implement a theme switcher supporting **Light, Dark, and System** themes for the AdminLTE dashboard.
> - **Authentication & RBAC:** We will install and configure **Laravel Breeze** (Blade stack) for authentication and **Spatie Laravel Permission** for Role-Based Access Control (RBAC).

---

## Proposed Changes

Here is the phase-by-phase development plan. We will execute one phase at a time and ask for your approval before moving to the next.

### Phase 0: Project Setup & Repository Connection
- `[x]` Initialize Git repository inside project folder.
- `[x]` Connect local repository to the remote origin: `https://github.com/Tauhid219/AltTeacher-AI.git`.
- `[ ]` Initial commit and push of existing files.

### Phase 1: Database Architecture & Core Models
- `[ ]` Create migrations for `districts`, `teacher_profiles`, `school_profiles`, `credentials`, `jobs`, `bookings`, `timesheets`, and `lesson_plans`.
- `[ ]` Create Laravel Models with relations, accessors, and JSON casts.
- `[ ]` Create database factories and seeders for all models to enable testing.

### Phase 2: Authentication (Laravel Breeze) & RBAC (Spatie)
- `[ ]` Install **Laravel Breeze** and run its installation command (Blade stack).
- `[ ]` Install **Spatie Laravel Permission** package and run its migrations.
- `[ ]` Create Roles (`district_admin`, `school_admin`, `teacher`) and assign permissions in database seeders.
- `[ ]` Modify Breeze Registration flow to allow users to select their role (`teacher` or `school_admin`) and automatically assign the corresponding Spatie role on registration.
- `[ ]` Setup Role-based redirect middleware to route logged-in users to their respective dashboards.

### Phase 3: AdminLTE Integration & Theme Switcher (Portals)
- `[ ]` Copy static files/assets (CSS, JS, plugins) from `C:\Reza\Tauhid\Templates\AdminLTE-3.1.0` to `public/vendor/adminlte`.
- `[ ]` Create a reusable Blade layout matching the AdminLTE structure (Sidebar, Navbar, Footer, Main Content) with full viewport style.
- `[ ]` Implement the Theme Switcher in the AdminLTE layout supporting **Light, Dark, and System** options using localStorage and custom JS.
- `[ ]` Integrate the School Admin Dashboard and District Admin Dashboard views into this layout.
- `[ ]` Build Job Posting management for School Admins (Create, read, update, delete jobs).
- `[ ]` Build District Admin view to review teacher onboarding checklist, credentials, and approve/reject them.

### Phase 4: Teacher Portal & Preference Matching Engine
- `[ ]` Build Teacher Dashboard layout using AdminLTE structure.
- `[ ]` Implement Classroom Preferences management form (grades, subjects, schools).
- `[ ]` Implement Job Matching Engine (matching active jobs with teacher preferences, availability, and onboarding status).
- `[ ]` Build Calendar view showing booking schedules using FullCalendar.js inside the AdminLTE dashboard.

### Phase 5: AI-Assisted Document Verification & Onboarding
- `[ ]` Create document upload form for Teacher Credentials (teaching license, ID, background checks).
- `[ ]` Build Gemini API client integration wrapper with environment variables (`GEMINI_API_KEY`) and a high-fidelity local Mock AI fallback.
- `[ ]` Build AI Certificate Verification service using Gemini to parse uploaded credentials and extract details (name, license ID, expiry date).
- `[ ]` Setup automatic compliance check (flagging expired licenses or blocking bookings for non-compliant teachers).

### Phase 6: AI-Assisted Lesson Continuity & Classroom Prep
- `[ ]` Build lesson plan upload system for School Admins when booking jobs.
- `[ ]` Create AI Lesson Adaptation helper using Gemini API (generating summaries, 10 quiz questions, and 3 classroom icebreaker games from the original lesson notes).
- `[ ]` Add PDF export feature for the AI-generated classroom prep files.

### Phase 7: Payroll Tracking & Timesheets
- `[ ]` Implement digital check-in and check-out system for teachers on their booking pages.
- `[ ]` Implement pay rate calculations and Timesheet approval workflows.
- `[ ]` Create timesheets and billing invoices summary charts in the District Admin dashboard.

### Phase 8: Final Review, Testing & Git Push
- `[ ]` Run Laravel Pint formatting check.
- `[ ]` Write Pest/PHPUnit tests for core booking and matching flows.
- `[ ]` Push all completed features to GitHub repo.

---

## Verification Plan

### Automated Tests
- Run `php artisan test` to verify models, Spatie roles/permissions assignments, matching algorithm, and core authorization logic.

### Manual Verification
- Verify Laravel Breeze registration with role selection.
- Test Spatie RBAC restrictions by attempting to access School Admin dashboard as a Teacher.
- Create dummy jobs and check if they match with teacher profiles based on preferences.
- Perform mock credential file uploads and verify AI extraction output.
- Check timesheet calculations.
- Test theme changes (Light, Dark, System) and confirm persistence across page reloads.
