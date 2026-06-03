# Implementation Plan - AI Verification Document Auditor Fix

Currently, uploading an invalid, mismatched, or unrelated file (like a favicon) in the Teacher Dashboard's document verification section incorrectly defaults to a `'verified'` status with the success message: *"Credential uploaded and parsed successfully by AI Auditor!"* This is because the controller defaults to `'verified'` and only flags it as `'rejected'` if the expiration date is in the past. Additionally, the mock Gemini service always returns valid mock information.

We will fix this by rejecting documents where crucial extracted information (such as name or license/document number) is missing, and updating the mock service to simulate a parsing failure for generic or non-document files.

## Proposed Changes

### Backend Logic

#### [MODIFY] [DashboardController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/DashboardController.php)
- Modify `storeCredential()` to check if `$info['name']` and `$info['license_number']` are present and not empty/null.
- If either critical field is missing or empty, set the verification status to `'rejected'` and redirect back with an error session message indicating the document was rejected or could not be parsed as the requested document type.

#### [MODIFY] [GeminiService.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Services/GeminiService.php)
- Update `getMockExtraction()` to return empty/null values for all fields if:
  - The uploaded file's name contains invalid keywords (e.g. `favicon`, `logo`, `avatar`, `icon`, `image`, etc.).
  - The uploaded file's name **does not** contain any positive document-identifying keywords (e.g. `license`, `check`, `id`, `cert`, `doc`, `credential`, `expired`, `stl`, `bc`).
- This accurately simulates what real Gemini returns when the file is not a readable credential/certificate (i.e. returns empty/null values).

### Testing

#### [MODIFY] [CredentialVerificationTest.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/CredentialVerificationTest.php)
- Add a new test case `test_invalid_document_gets_rejected` that uploads a fake file named `favicon.png` under `state_teaching_license` and asserts it is rejected.
- Add a new test case `test_generic_pdf_without_keywords_gets_rejected` that uploads a fake file named `random_notes.pdf` under `state_teaching_license` and asserts it is rejected.

---

## Verification Plan

### Automated Tests
- Run PHPUnit tests to verify both existing tests and the new test case pass:
  ```powershell
  php artisan test
  ```

### Manual Verification
- Log in to the Teacher Dashboard.
- Try uploading a favicon, or a generic PDF (e.g., `invoice.pdf` or `notes.pdf`) that doesn't contain the positive keywords.
- Confirm it is correctly rejected by the system with a red alert notice and status marked as `rejected`.
