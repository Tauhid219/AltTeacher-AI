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

---

## Phase 3: AdminLTE Integration & Theme Switcher (Portals)
We integrated the AdminLTE Bootstrap template and built a persistent Light/Dark/System theme switcher:

### 1. Assets Copying & Reusable Layout
- **Asset Directory:** AdminLTE assets (`dist` and `plugins` directories) were copied to [public/vendor/adminlte](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/public/vendor/adminlte).
- **Layout Structure:** Created the reusable admin shell [admin.blade.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/layouts/admin.blade.php) featuring standard AdminLTE layout headers, left-navigation, breadcrumbs, content yields, flash notifications, and footers.

### 2. Interactive Theme Switcher
- Implemented a Javascript-based theme switcher supporting **Light**, **Dark**, and **System** themes.
- Utilized `localStorage` to persist user choices across session refreshes.
- Integrated an inline self-executing CSS injection script in the header block to load classes immediately and prevent page flash on page-load.

### 3. School and District Dashboards
- **School Dashboard:** Implemented [school/dashboard.blade.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/school/dashboard.blade.php). Displays metrics (open postings, filled bookings, available subs, monthly expenses), lists current postings, and provides a form to request substitute teacher jobs.
- **District Dashboard:** Implemented [district/dashboard.blade.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/district/dashboard.blade.php). Displays district-wide summary counts, payroll expenditures, and a grid showing registered teacher candidates, licenses, verification logs, and quick action Approve/Reject forms.
- **Dashboard Controller:** Authored [DashboardController.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/DashboardController.php) to calculate metrics and process jobs creation and onboarding approvals.

### 4. Verification with Tests
- Created [DashboardTest.php](file:///C:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/DashboardTest.php) to assert dashboard access authorization, job posting limits, and candidate status transitioning.
- All 34 tests passed successfully:
```bash
Tests:    34 passed (110 assertions)
Duration: 5.74s
```

---

## Phase 4: Teacher Portal & Preference Matching Engine
We built a comprehensive workspace for substitute teachers, matching available job postings with their grade level, subject matter, and school preferences:

### 1. Teacher Portal View
- **Dashboard Layout:** Developed [teacher/dashboard.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/teacher/dashboard.blade.php) extending the AdminLTE master layout.
- **Onboarding Banner Alerts:** Implemented warning and status indicators. If the teacher's profile is in a `pending` or `rejected` state, they are blocked from viewing or booking jobs.
- **Preferences Controls:** Created form elements to configure desired hourly rates, preferred grade-levels (Grade 1-12 checkboxes), preferred subjects (multi-select checkboxes), and preferred schools.

### 2. Preference Matching Engine
- **Real-time Filtering:** Developed filtering logic inside [DashboardController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/DashboardController.php). It retrieves all open jobs and displays only those matching the teacher's classroom grade levels, subjects, and school preferences.
- **Booking Actions:** Added instant booking triggers which link the teacher's profile to the job, update the job status to `filled`, and register the booking record.

### 3. Booking Calendar Integration
- Embedded **FullCalendar.js** into the teacher dashboard to dynamically load confirmed and completed bookings as events, color-coded by job status.

### 4. Verified with Feature Tests
- Authored [TeacherPortalTest.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/TeacherPortalTest.php) to assert dashboard access authorization, preference submission, matching rules, and booking constraints.
- All 41 tests passed successfully:
```bash
Tests:    41 passed (144 assertions)
Duration: 6.14s
```

---

## Phase 5: AI-Assisted Document Verification & Onboarding
We implemented automated credential scanning and verification using the Gemini API:

### 1. Gemini Client Integration (Wrapper)
- Created [GeminiService.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Services/GeminiService.php) to call Gemini's `gemini-1.5-flash` model. It reads uploaded documents, converts them to base64, and extracts credential details (full name, license/case number, and expiry date) as a structured JSON object.
- Integrated a high-fidelity local Mock AI fallback. It processes normal files and detects files containing `'expired'` in their original filename, returning expired mock dates for compliance test cases.

### 2. Document Upload & Parsing
- Added a document upload card to the Teacher Dashboard [teacher/dashboard.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/teacher/dashboard.blade.php) where teachers can submit State Teaching Licenses, Background Checks, or Government IDs.
- Added a verification history list that displays the AI-extracted information (holder's name, license number, and expiry dates) as well as the verification status badge (`verified`, `pending`, or `rejected`).

### 3. Compliance Rules & Booking Validation
- Configured automated compliance checks. When booking a job, the system queries the school district's compliance rules (e.g. required licenses) and verifies that the teacher has verified, non-expired credentials of those types.
- Non-compliant teachers are blocked from booking jobs and redirected with a warning message.

### 4. Verified with Feature Tests
- Authored [CredentialVerificationTest.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/CredentialVerificationTest.php) to verify guest upload blocking, mock verification logic, expired credential flagging, compliance engine booking blocks, and compliant booking approvals.
- All 46 tests passed successfully:
```bash
Tests:    46 passed (170 assertions)
Duration: 5.35s
```

---

## Phase 6: AI-Assisted Lesson Continuity & Classroom Prep
We built an automated system that prepares substitute teachers for booked classes using Gemini AI analysis and exports prep packets to PDF format:

### 1. Optional Lesson Plan Posting
- Updated the School Admin dashboard to allow uploading an optional lesson plan file (PDF, DOCX, TXT, or images) when posting a new substitute job.
- Added file input field with dynamic Bootstrap label updating (using jQuery) to show the selected file's basename.

### 2. Automatic AI Adaptations on Booking
- Modified the booking process: upon confirmation, the platform automatically triggers the Gemini AI model (`gemini-1.5-flash`) via the [GeminiService](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Services/GeminiService.php) to analyze the uploaded file and the job's description.
- Generates a structured JSON continuity packet containing:
  - **Summary**: A concise 2-3 sentence overview of what the substitute should focus on.
  - **Quizzes**: A list of exactly 10 multiple-choice questions suitable for the class grade and subject.
  - **Icebreakers**: Exactly 3 quick classroom icebreaker activities.
- Saves the results to the `lesson_plans` table associated with the booking.

### 3. Teacher Dashboard Integration & Modal
- Added a "My Scheduled Bookings" section to the Teacher Dashboard where teachers can click a "View Prep Packet" button.
- Populates an AdminLTE modal dynamically using jQuery, displaying the AI-adapted summary, 10 quiz questions (along with choices and correct answers), and the 3 icebreakers.

### 4. PDF Export Generation
- Installed `barryvdh/laravel-dompdf` package to support HTML-to-PDF rendering.
- Created a beautifully styled PDF blade template [pdf/lesson_plan.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/pdf/lesson_plan.blade.php) featuring standard school headers, a metadata table, page-break safeguards, and clean section groupings.
- Added a download endpoint `/teacher/booking/{id}/pdf` to generate and download the adapted lesson prep packet.

### 5. Verified with Feature Tests
- Authored [LessonAdaptationTest.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/LessonAdaptationTest.php) to verify school admin job posting with files, booking plan generation, structure validation, and PDF download integrity.
- Ran all 49 tests in the test suite and confirmed all passed successfully:
```bash
Tests:    49 passed (198 assertions)
Duration: 10.37s
```

