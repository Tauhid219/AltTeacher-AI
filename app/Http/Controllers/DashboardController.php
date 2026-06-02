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
}
