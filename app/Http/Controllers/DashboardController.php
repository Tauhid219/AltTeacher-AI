<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\SchoolProfile;
use App\Models\SubstituteJob;
use App\Models\Booking;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

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
        if (!$schoolProfile) {
            $schoolProfile = SchoolProfile::create([
                'user_id' => $schoolUser->id,
                'school_name' => $schoolUser->name . ' School',
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

        return view('school.dashboard', compact(
            'schoolProfile',
            'openJobsCount',
            'filledJobsCount',
            'approvedTeachersCount',
            'monthSpend',
            'jobs'
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

        return view('district.dashboard', compact(
            'totalTeachersCount',
            'pendingTeachersCount',
            'activeBookingsCount',
            'totalPayroll',
            'teachers'
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
        ]);

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

        if (!$teacherProfile) {
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
            ->with(['substituteJob.schoolProfile'])
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
        if (!$teacherProfile) {
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
            ]
        ]);

        return redirect()->route('teacher.dashboard')->with('success', 'Preferences updated successfully!');
    }

    /**
     * Book a substitute job.
     */
    public function bookJob(int $id): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (!$teacherProfile || $teacherProfile->onboarding_status !== 'approved') {
            return redirect()->route('teacher.dashboard')->with('error', 'You must be approved to book jobs.');
        }

        $job = SubstituteJob::findOrFail($id);
        if ($job->status !== 'open') {
            return redirect()->route('teacher.dashboard')->with('error', 'This job is no longer available.');
        }

        // Create Booking
        Booking::create([
            'substitute_job_id' => $job->id,
            'teacher_profile_id' => $teacherProfile->id,
            'status' => 'confirmed',
        ]);

        // Update job status
        $job->update(['status' => 'filled']);

        return redirect()->route('teacher.dashboard')->with('success', 'Job booked successfully! It has been added to your schedule.');
    }
}
