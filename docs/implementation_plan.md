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
- `[x]` Initial commit and push of existing files.

### Phase 1: Database Architecture & Core Models
- `[x]` Create migrations for `districts`, `teacher_profiles`, `school_profiles`, `credentials`, `jobs`, `bookings`, `timesheets`, and `lesson_plans`.
- `[x]` Create Laravel Models with relations, accessors, and JSON casts.
- `[x]` Create database factories and seeders for all models to enable testing.

### Phase 2: Authentication (Laravel Breeze) & RBAC (Spatie)
- `[x]` Install **Laravel Breeze** and run its installation command (Blade stack).
- `[x]` Install **Spatie Laravel Permission** package and run its migrations.
- `[x]` Create Roles (`district_admin`, `school_admin`, `teacher`) and assign permissions in database seeders.
- `[x]` Modify Breeze Registration flow to allow users to select their role (`teacher` or `school_admin`) and automatically assign the corresponding Spatie role on registration.
- `[x]` Setup Role-based redirect middleware to route logged-in users to their respective dashboards.

### Phase 3: AdminLTE Integration & Theme Switcher (Portals)
- `[x]` Copy static files/assets (CSS, JS, plugins) from `C:\Reza\Tauhid\Templates\AdminLTE-3.1.0` to `public/vendor/adminlte`.
- `[x]` Create a reusable Blade layout matching the AdminLTE structure (Sidebar, Navbar, Footer, Main Content) with full viewport style.
- `[x]` Implement the Theme Switcher in the AdminLTE layout supporting **Light, Dark, and System** options using localStorage and custom JS.
- `[x]` Integrate the School Admin Dashboard and District Admin Dashboard views into this layout.
- `[x]` Build Job Posting management for School Admins (Create, read, update, delete jobs).
- `[x]` Build District Admin view to review teacher onboarding checklist, credentials, and approve/reject them.

### Phase 4: Teacher Portal & Preference Matching Engine
- `[x]` Build Teacher Dashboard layout using AdminLTE structure.
- `[x]` Implement Classroom Preferences management form (grades, subjects, schools).
- `[x]` Implement Job Matching Engine (matching active jobs with teacher preferences, availability, and onboarding status).
- `[x]` Build Calendar view showing booking schedules using FullCalendar.js inside the AdminLTE dashboard.

### Phase 5: AI-Assisted Document Verification & Onboarding
- `[x]` Create document upload form for Teacher Credentials (teaching license, ID, background checks).
- `[x]` Build Gemini API client integration wrapper with environment variables (`GEMINI_API_KEY`) and a high-fidelity local Mock AI fallback.
- `[x]` Build AI Certificate Verification service using Gemini to parse uploaded credentials and extract details (name, license ID, expiry date).
- `[x]` Setup automatic compliance check (flagging expired licenses or blocking bookings for non-compliant teachers).

### Phase 6: AI-Assisted Lesson Continuity & Classroom Prep
- `[x]` Build lesson plan upload system for School Admins when booking jobs.
- `[x]` Create AI Lesson Adaptation helper using Gemini API (generating summaries, 10 quiz questions, and 3 classroom icebreaker games from the original lesson notes).
- `[x]` Add PDF export feature for the AI-generated classroom prep files.

### Phase 7: Payroll Tracking & Timesheets
- `[x]` Implement digital check-in and check-out system for teachers on their booking pages.
  - **Routes**:
    - `POST /teacher/booking/{id}/clock-in` -> `teacher.booking.clock_in`
    - `POST /teacher/booking/{id}/clock-out` -> `teacher.booking.clock_out`
  - **Check-in**: Records current time to `timesheets.check_in_time` and sets status to `pending`.
  - **Check-out**: Records current time to `timesheets.check_out_time`. Calculates decimal hours (seconds difference / 3600), multiplies by `teacher_profiles.hourly_rate` to set `calculated_pay`, and updates `substitute_jobs.status` to `completed`.
- `[x]` Implement pay rate calculations and Timesheet approval workflows.
  - **Routes**:
    - `POST /school/timesheet/{id}/approve` -> `school.timesheets.approve`
    - `POST /school/timesheet/{id}/reject` -> `school.timesheets.reject`
  - **School Dashboard**: Add a "Timesheet Approvals & Billing" card listing pending/approved/rejected timesheets with quick actions.
- `[x]` Create timesheets and billing invoices summary charts in the District Admin dashboard.
  - **Analytics**: Aggregates total approved pay per school and count by timesheet status.
  - **Visualizations**: Renders a Bar Chart (spending per school) and a Doughnut Chart (timesheet status) in `district/dashboard` using Chart.js.

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
