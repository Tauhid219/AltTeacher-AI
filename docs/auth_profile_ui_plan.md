# Implementation Plan - AdminLTE Auth and Profile UI Integration

Migrate Laravel Breeze default Tailwind-based guest/auth and user profile views in the AltTeacher-AI project to match the AdminLTE-3.1.0 Bootstrap-based layout.

## Proposed Changes

We will rewrite the Breeze default views to use AdminLTE styles and Bootstrap components. This ensures complete visual cohesion with the rest of the application.

---

### Layouts & Guest (Auth) Views

#### [MODIFY] [guest.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/layouts/guest.blade.php)
- Replace Tailwind styles with AdminLTE fonts, stylesheets, and JavaScript.
- Standardize the card container to render the AdminLTE logo and box classes dynamically based on the page (e.g. `login-box`, `register-box`).

#### [MODIFY] [login.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/login.blade.php)
- Replace `<x-text-input>`, `<x-primary-button>` and other Tailwind elements with standard AdminLTE/Bootstrap card markup, input groups with FontAwesome icons (e.g. envelope, lock), and icheck-bootstrap remember checkboxes.

#### [MODIFY] [register.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/register.blade.php)
- Re-align name, email, register-as role selector, password, and confirm-password fields using Bootstrap input groups and dropdown styles.

#### [MODIFY] [forgot-password.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/forgot-password.blade.php)
- Adapt the screen to present an AdminLTE password recovery style with Bootstrap layout classes.

#### [MODIFY] [reset-password.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/reset-password.blade.php)
- Adapt the input elements and buttons to AdminLTE standards.

#### [MODIFY] [confirm-password.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/confirm-password.blade.php)
- Adapt form styles and button markup to match.

#### [MODIFY] [verify-email.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/auth/verify-email.blade.php)
- Apply AdminLTE page text layouts and buttons.

---

### Profile Settings

#### [MODIFY] [edit.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/profile/edit.blade.php)
- Change from `<x-app-layout>` to extend `layouts.admin` layout.
- Define `@section('title')`, `@section('page-title')`, and `@section('breadcrumb')`.
- Dynamic rendering of `@section('sidebar-menu')` depending on the logged-in user's role (Teacher, School Admin, District Admin) to make navigation seamless.
- Format the content into a 2-column Bootstrap grid (Left: Profile Info, Right: Password Update & Account Deletion).
- Add jQuery/JavaScript handlers at the bottom to auto-show the deletion modal upon validation error and fade out status notifications.

#### [MODIFY] [update-profile-information-form.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/profile/partials/update-profile-information-form.blade.php)
- Use Bootstrap `.form-group`, `.form-control` styles with `invalid-feedback` classes instead of Breeze Tailwind inputs.

#### [MODIFY] [update-password-form.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/profile/partials/update-password-form.blade.php)
- Align with Bootstrap `.form-group` and `.form-control` error structures.

#### [MODIFY] [delete-user-form.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/profile/partials/delete-user-form.blade.php)
- Replace Tailwind `<x-modal>` overlay with a native Bootstrap Modal overlay (`confirm-user-deletion-modal`) triggered by standard button selectors.

---

## Verification Plan

### Automated Tests
- Run existing PHPUnit tests to make sure user authentication flow and profile updates/deletions continue working properly without regressions:
  ```powershell
  php artisan test
  ```

### Manual Verification
- Log in and verify that the layout uses AdminLTE styles.
- Visit Profile Settings as a Teacher, School Admin, or District Admin. Confirm the layout matches AdminLTE, the sidebar matches the user role, and validation/update messages function correctly.
- Test account deletion modal trigger and auto-show behavior.
