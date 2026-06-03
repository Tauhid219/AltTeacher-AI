# Implementation Plan - AI Verification Document Auditor Fix

Currently, uploading an invalid, mismatched, or unrelated file (like a favicon) in the Teacher Dashboard's document verification section incorrectly defaults to a `'verified'` status with the success message: *"Credential uploaded and parsed successfully by AI Auditor!"* This is because the controller defaults to `'verified'` and only flags it as `'rejected'` if the expiration date is in the past. Additionally, the mock Gemini service always returns valid mock information.

We will fix this by rejecting documents where crucial extracted information (such as name or license/document number) is missing, and updating the mock service to simulate a parsing failure for generic or non-document files.

## Proposed Changes

### Backend Logic

#### [MODIFY] [DashboardController.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Http/Controllers/DashboardController.php)
- Modify `storeCredential()` to check if `$info['name']` and `$info['license_number']` are present and not empty/null.
- If either critical field is missing or empty, set the verification status to `'rejected'` and redirect back with an error session message indicating the document was rejected or could not be parsed as the requested document type.

#### [MODIFY] [GeminiService.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Services/GeminiService.php)
- Update `getMockExtraction()` to return empty/null values for all fields if the uploaded file's name contains keywords indicating it is not a credential document (e.g. `favicon`, `logo`, `avatar`, `icon`, `image`, etc.). This simulates what real Gemini returns when the file is not a readable credential/certificate.

### Testing

#### [MODIFY] [CredentialVerificationTest.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/tests/Feature/CredentialVerificationTest.php)
- Add a new test case `test_invalid_document_gets_rejected` that uploads a fake file named `favicon.png` under `state_teaching_license` and asserts:
  - The response redirects back.
  - The session contains an error message.
  - The database records the status as `rejected`.

---

## Verification Plan

### Automated Tests
- Run PHPUnit tests to verify both existing tests and the new test case pass:
  ```powershell
  php artisan test
  ```

### Manual Verification
- Log in to the Teacher Dashboard.
- Try uploading a favicon or an image with `favicon` in its name under the document upload section.
- Confirm it is correctly rejected by the system with a red alert notice and status marked as `rejected`.
