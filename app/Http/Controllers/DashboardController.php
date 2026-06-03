<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Credential;
use App\Models\LessonPlan;
use App\Models\SchoolProfile;
use App\Models\SubstituteJob;
use App\Models\TeacherProfile;
use App\Models\Timesheet;
use App\Models\User;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the School Admin Dashboard.
     */
    public function schoolDashboard(): View
    {
        $schoolUser = auth()->user();
        $schoolProfile = $schoolUser->schoolProfile;

        // If user registers as school admin, they will have a schoolProfile.
        // If profile is missing (e.g. seeded error), handle gracefully.
        if (! $schoolProfile) {
            $schoolProfile = SchoolProfile::create([
                'user_id' => $schoolUser->id,
                'school_name' => $schoolUser->name.' School',
            ]);
        }

        $openJobsCount = SubstituteJob::where('school_profile_id', $schoolProfile->id)
            ->where('status', 'open')
            ->count();

        $filledJobsCount = SubstituteJob::where('school_profile_id', $schoolProfile->id)
            ->where('status', 'filled')
            ->count();

        $approvedTeachersCount = TeacherProfile::where('onboarding_status', 'approved')->count();

        // Calculate current month spend
        $monthSpend = Timesheet::whereHas('booking.substituteJob', function ($query) use ($schoolProfile) {
            $query->where('school_profile_id', $schoolProfile->id);
        })->where('status', 'approved')
            ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('calculated_pay');

        $jobs = SubstituteJob::where('school_profile_id', $schoolProfile->id)
            ->with(['booking.teacherProfile.user'])
            ->orderBy('date', 'desc')
            ->get();

        // Load timesheets for this school's postings
        $timesheets = Timesheet::whereHas('booking.substituteJob', function ($query) use ($schoolProfile) {
            $query->where('school_profile_id', $schoolProfile->id);
        })->with(['booking.teacherProfile.user', 'booking.substituteJob'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.dashboard', compact(
            'schoolProfile',
            'openJobsCount',
            'filledJobsCount',
            'approvedTeachersCount',
            'monthSpend',
            'jobs',
            'timesheets'
        ));
    }

    /**
     * Display the District Admin Dashboard.
     */
    public function districtDashboard(): View
    {
        $totalTeachersCount = TeacherProfile::count();
        $pendingTeachersCount = TeacherProfile::where('onboarding_status', 'pending')->count();
        $activeBookingsCount = Booking::where('status', 'confirmed')->count();
        $totalPayroll = Timesheet::where('status', 'approved')->sum('calculated_pay');

        $teachers = TeacherProfile::with(['user', 'credentials'])
            ->orderBy('onboarding_status', 'desc')
            ->get();

        // School-wise spending for bar chart
        $schoolSpending = Timesheet::where('timesheets.status', 'approved')
            ->join('bookings', 'timesheets.booking_id', '=', 'bookings.id')
            ->join('substitute_jobs', 'bookings.substitute_job_id', '=', 'substitute_jobs.id')
            ->join('school_profiles', 'substitute_jobs.school_profile_id', '=', 'school_profiles.id')
            ->selectRaw('school_profiles.school_name, SUM(timesheets.calculated_pay) as total_spend')
            ->groupBy('school_profiles.school_name')
            ->pluck('total_spend', 'school_name')
            ->toArray();

        // Timesheet status breakdown for doughnut chart
        $statusBreakdown = Timesheet::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('district.dashboard', compact(
            'totalTeachersCount',
            'pendingTeachersCount',
            'activeBookingsCount',
            'totalPayroll',
            'teachers',
            'schoolSpending',
            'statusBreakdown'
        ));
    }

    /**
     * Store a new substitute job.
     */
    public function storeJob(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'description' => 'nullable|string',
            'lesson_plan_file' => 'nullable|file|max:5000|mimes:pdf,docx,txt,jpeg,jpg,png',
        ]);

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $filePath = $request->file('lesson_plan_file')->store('lesson_plans', 'public');
        }

        $schoolProfile = auth()->user()->schoolProfile;

        SubstituteJob::create([
            'school_profile_id' => $schoolProfile->id,
            'subject' => $request->subject,
            'grade_level' => $request->grade_level,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'open',
            'description' => $request->description,
            'lesson_plan_file' => $filePath,
        ]);

        return redirect()->route('school.dashboard')->with('success', 'Substitute teacher request posted successfully!');
    }

    /**
     * Approve teacher onboarding.
     */
    public function approveTeacher(int $id): RedirectResponse
    {
        $teacher = TeacherProfile::findOrFail($id);
        $teacher->update(['onboarding_status' => 'approved']);

        return redirect()->route('district.dashboard')->with('success', 'Teacher onboarding has been approved successfully!');
    }

    /**
     * Reject teacher onboarding.
     */
    public function rejectTeacher(int $id): RedirectResponse
    {
        $teacher = TeacherProfile::findOrFail($id);
        $teacher->update(['onboarding_status' => 'rejected']);

        return redirect()->route('district.dashboard')->with('error', 'Teacher onboarding was rejected.');
    }

    /**
     * Display the Teacher Dashboard.
     */
    public function teacherDashboard(): View
    {
        $teacherUser = auth()->user();
        $teacherProfile = $teacherUser->teacherProfile;

        if (! $teacherProfile) {
            $teacherProfile = TeacherProfile::create([
                'user_id' => $teacherUser->id,
                'hourly_rate' => 30.00,
                'classroom_preferences' => [
                    'grades' => [],
                    'subjects' => [],
                    'preferred_schools' => [],
                ],
                'onboarding_status' => 'pending',
            ]);
        }

        $schools = SchoolProfile::all();
        $bookings = Booking::where('teacher_profile_id', $teacherProfile->id)
            ->with(['substituteJob.schoolProfile', 'timesheet'])
            ->get();

        $matchingJobs = [];
        if ($teacherProfile->onboarding_status === 'approved') {
            $allOpenJobs = SubstituteJob::where('status', 'open')
                ->where('date', '>=', Carbon::now()->toDateString())
                ->with('schoolProfile')
                ->get();

            $prefs = $teacherProfile->classroom_preferences ?? [];
            $prefGrades = $prefs['grades'] ?? [];
            $prefSubjects = $prefs['subjects'] ?? [];
            $prefSchools = $prefs['preferred_schools'] ?? [];

            foreach ($allOpenJobs as $job) {
                // If preference arrays are empty, treat as matching all (no filter)
                $gradeMatches = empty($prefGrades) || in_array($job->grade_level, $prefGrades);
                $subjectMatches = empty($prefSubjects) || in_array($job->subject, $prefSubjects);
                $schoolMatches = empty($prefSchools) || in_array($job->school_profile_id, $prefSchools);

                if ($gradeMatches && $subjectMatches && $schoolMatches) {
                    $matchingJobs[] = $job;
                }
            }
        }

        return view('teacher.dashboard', compact(
            'teacherProfile',
            'schools',
            'bookings',
            'matchingJobs'
        ));
    }

    /**
     * Update teacher classroom preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Profile not found.');
        }

        $request->validate([
            'grades' => 'nullable|array',
            'grades.*' => 'string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'string',
            'preferred_schools' => 'nullable|array',
            'preferred_schools.*' => 'integer|exists:school_profiles,id',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $teacherProfile->update([
            'hourly_rate' => $request->hourly_rate,
            'classroom_preferences' => [
                'grades' => $request->grades ?? [],
                'subjects' => $request->subjects ?? [],
                'preferred_schools' => array_map('intval', $request->preferred_schools ?? []),
            ],
        ]);

        return redirect()->route('teacher.dashboard')->with('success', 'Preferences updated successfully!');
    }

    /**
     * Book a substitute job.
     */
    public function bookJob(int $id): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile || $teacherProfile->onboarding_status !== 'approved') {
            return redirect()->route('teacher.dashboard')->with('error', 'You must be approved to book jobs.');
        }

        // Compliance checks
        $district = $teacherProfile->district;
        if ($district) {
            $rules = $district->compliance_rules ?? [];
            $requiredDocs = $rules['required_credentials'] ?? [];

            foreach ($requiredDocs as $docType) {
                // Find if there is a verified, non-expired credential of this type
                $credential = $teacherProfile->credentials()
                    ->where('document_type', $docType)
                    ->where('verification_status', 'verified')
                    ->where(function ($query) {
                        $query->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', Carbon::now()->toDateString());
                    })
                    ->first();

                if (! $credential) {
                    return redirect()->route('teacher.dashboard')->with('error', 'Booking blocked: You are non-compliant. Please ensure you have uploaded a valid, non-expired '.str_replace('_', ' ', $docType).'.');
                }
            }
        }

        $job = SubstituteJob::findOrFail($id);
        if ($job->status !== 'open') {
            return redirect()->route('teacher.dashboard')->with('error', 'This job is no longer available.');
        }

        // Create Booking
        $booking = Booking::create([
            'substitute_job_id' => $job->id,
            'teacher_profile_id' => $teacherProfile->id,
            'status' => 'confirmed',
        ]);

        // Update job status
        $job->update(['status' => 'filled']);

        // Generate and store AI Adapted Lesson Plan
        $geminiService = app(GeminiService::class);
        $filePath = $job->lesson_plan_file ? storage_path('app/public/'.$job->lesson_plan_file) : null;

        $aiAdaptedData = $geminiService->generateAdaptedLessonPlan(
            $job->subject,
            $job->grade_level,
            $job->description,
            $filePath
        );

        LessonPlan::create([
            'booking_id' => $booking->id,
            'original_plan_text' => $job->description ?: 'No text notes provided.',
            'ai_summary' => $aiAdaptedData['summary'] ?? null,
            'ai_generated_activities' => [
                'quizzes' => $aiAdaptedData['quizzes'] ?? [],
                'icebreakers' => $aiAdaptedData['icebreakers'] ?? [],
            ],
        ]);

        return redirect()->route('teacher.dashboard')->with('success', 'Job booked successfully! AI has prepared your classroom prep packet.');
    }

    /**
     * Store and verify uploaded credentials.
     */
    public function storeCredential(Request $request): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Profile not found.');
        }

        $request->validate([
            'document_type' => 'required|string|in:state_teaching_license,background_check,government_id',
            'document' => 'required|file|max:5000|mimes:pdf,jpeg,png,jpg',
        ]);

        // Store file
        $path = $request->file('document')->store('credentials', 'public');

        // Extract using GeminiService
        $geminiService = app(GeminiService::class);
        $filePath = storage_path('app/public/'.$path);

        $info = $geminiService->extractCredentialInfo(
            $filePath,
            $request->document_type,
            $request->file('document')->getClientOriginalName()
        );

        $expiryDate = null;
        if (! empty($info['expiry_date'])) {
            try {
                $expiryDate = Carbon::parse($info['expiry_date']);
            } catch (\Exception $e) {
                // ignore invalid date parse errors
            }
        }

        // Set status
        $status = 'verified';
        if ($expiryDate && $expiryDate->isPast()) {
            $status = 'rejected';
        }

        // Save
        Credential::updateOrCreate([
            'teacher_profile_id' => $teacherProfile->id,
            'document_type' => $request->document_type,
        ], [
            'document_path' => $path,
            'extracted_info' => $info,
            'verification_status' => $status,
            'expiry_date' => $expiryDate,
        ]);

        if ($status === 'rejected') {
            return redirect()->route('teacher.dashboard')->with('error', 'Credential uploaded, but flagged as EXPIRED/INVALID by AI auditor.');
        }

        return redirect()->route('teacher.dashboard')->with('success', 'Credential uploaded and parsed successfully by AI Auditor!');
    }

    /**
     * Download AI Adapted Lesson Plan as a PDF.
     */
    public function downloadLessonPlanPdf(int $bookingId): Response|RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Profile not found.');
        }

        $booking = Booking::with(['substituteJob.schoolProfile', 'lessonPlan'])
            ->where('teacher_profile_id', $teacherProfile->id)
            ->findOrFail($bookingId);

        $lessonPlan = $booking->lessonPlan;
        if (! $lessonPlan) {
            return redirect()->back()->with('error', 'Lesson plan not found.');
        }

        $pdf = Pdf::loadView('pdf.lesson_plan', compact('booking', 'lessonPlan'));

        return $pdf->download("lesson_plan_booking_{$booking->id}.pdf");
    }

    /**
     * Clock in the teacher for their booked substitute job.
     */
    public function clockIn(Request $request, int $bookingId): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Profile not found.');
        }

        $booking = Booking::where('teacher_profile_id', $teacherProfile->id)->findOrFail($bookingId);

        if ($booking->timesheet) {
            return redirect()->route('teacher.dashboard')->with('error', 'You have already clocked in for this job.');
        }

        $checkInTime = $request->check_in_time ? Carbon::parse($request->check_in_time) : Carbon::now();

        Timesheet::create([
            'booking_id' => $booking->id,
            'check_in_time' => $checkInTime,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.dashboard')->with('success', 'Clock-in recorded successfully!');
    }

    /**
     * Clock out the teacher for their booked substitute job.
     */
    public function clockOut(Request $request, int $bookingId): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Profile not found.');
        }

        $booking = Booking::where('teacher_profile_id', $teacherProfile->id)
            ->with(['substituteJob'])
            ->findOrFail($bookingId);

        $timesheet = $booking->timesheet;
        if (! $timesheet || ! $timesheet->check_in_time) {
            return redirect()->route('teacher.dashboard')->with('error', 'You must clock in first.');
        }

        if ($timesheet->check_out_time) {
            return redirect()->route('teacher.dashboard')->with('error', 'You have already clocked out.');
        }

        $checkOutTime = $request->check_out_time ? Carbon::parse($request->check_out_time) : Carbon::now();
        $checkInTime = $timesheet->check_in_time;

        $secondsDiff = $checkOutTime->diffInSeconds($checkInTime, false);
        $calculatedHours = abs(round($secondsDiff / 3600, 2));

        $hourlyRate = $teacherProfile->hourly_rate ?: 30.00;
        $calculatedPay = round($calculatedHours * $hourlyRate, 2);

        $timesheet->update([
            'check_out_time' => $checkOutTime,
            'calculated_hours' => $calculatedHours,
            'calculated_pay' => $calculatedPay,
            'status' => 'pending',
        ]);

        $booking->update(['status' => 'completed']);
        if ($booking->substituteJob) {
            $booking->substituteJob->update(['status' => 'completed']);
        }

        return redirect()->route('teacher.dashboard')->with('success', 'Clock-out recorded successfully! Timesheet submitted for school approval.');
    }

    /**
     * Approve timesheet from school dashboard.
     */
    public function approveTimesheet(int $timesheetId): RedirectResponse
    {
        $schoolProfile = auth()->user()->schoolProfile;
        if (! $schoolProfile) {
            return redirect()->route('school.dashboard')->with('error', 'School profile not found.');
        }

        $timesheet = Timesheet::whereHas('booking.substituteJob', function ($query) use ($schoolProfile) {
            $query->where('school_profile_id', $schoolProfile->id);
        })->findOrFail($timesheetId);

        $timesheet->update(['status' => 'approved']);

        return redirect()->route('school.dashboard')->with('success', 'Timesheet approved successfully!');
    }

    /**
     * Reject timesheet from school dashboard.
     */
    public function rejectTimesheet(int $timesheetId): RedirectResponse
    {
        $schoolProfile = auth()->user()->schoolProfile;
        if (! $schoolProfile) {
            return redirect()->route('school.dashboard')->with('error', 'School profile not found.');
        }

        $timesheet = Timesheet::whereHas('booking.substituteJob', function ($query) use ($schoolProfile) {
            $query->where('school_profile_id', $schoolProfile->id);
        })->findOrFail($timesheetId);

        $timesheet->update(['status' => 'rejected']);

        return redirect()->route('school.dashboard')->with('error', 'Timesheet was rejected.');
    }
}
