@extends('layouts.admin')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher Dashboard')

@section('styles')
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        #calendar {
            background-color: var(--card-bg, #fff);
            padding: 15px;
            border-radius: 8px;
        }
        .dark-mode #calendar {
            background-color: #343a40 !important;
            color: #fff;
        }
        .dark-mode .fc-theme-bootstrap a {
            color: #fff !important;
        }
        .dark-mode .fc-col-header-cell-cushion,
        .dark-mode .fc-daygrid-day-number {
            color: #fff !important;
        }
    </style>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Teacher Dashboard</li>
@endsection

@section('sidebar-menu')
    <li class="nav-item">
        <a href="{{ route('teacher.dashboard') }}" class="nav-link active">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-header">ONBOARDING & PREFERENCES</li>
    <li class="nav-item">
        <a href="#preferences-card" class="nav-link">
            <i class="nav-icon fas fa-sliders-h"></i>
            <p>Classroom Preferences</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="#calendar-card" class="nav-link">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>My Schedule Calendar</p>
        </a>
    </li>
@endsection

@section('content')
    <!-- Status Banner Alert -->
    @if($teacherProfile->onboarding_status === 'pending')
        <div class="alert alert-warning alert-dismissible">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Onboarding Review Pending</h5>
            Your credentials and background check are currently being reviewed by the district administrator. You will be able to book substitute jobs as soon as your profile is approved.
        </div>
    @elseif($teacherProfile->onboarding_status === 'rejected')
        <div class="alert alert-danger alert-dismissible">
            <h5><i class="icon fas fa-ban"></i> Onboarding Rejected</h5>
            Your substitute teacher onboarding request has been rejected. Please update your profile or contact the district administration.
        </div>
    @else
        <div class="alert alert-success alert-dismissible">
            <h5><i class="icon fas fa-check"></i> Profile Active & Approved</h5>
            Congratulations! Your profile is verified. You can now browse and book available substitute jobs that match your preferences.
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Matching Jobs Feed & Calendar -->
        <div class="col-lg-8">
            
            <!-- Matching Jobs Card -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-magic mr-1 text-primary"></i> 
                        AI Preference Matched Jobs
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ count($matchingJobs) }} Match(es)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($teacherProfile->onboarding_status !== 'approved')
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-lock fa-2x mb-2 text-warning"></i>
                            <p class="mb-0">Your profile must be approved to view and book jobs. Please wait for district verification.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Details</th>
                                        <th>School</th>
                                        <th>Timing</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($matchingJobs as $job)
                                        <tr>
                                            <td>
                                                <span class="badge badge-info mb-1">{{ $job->grade_level }}</span>
                                                <h6 class="mb-0 text-bold text-primary">{{ $job->subject }}</h6>
                                                <small class="text-muted d-block">{{ Str::limit($job->description, 50) }}</small>
                                            </td>
                                            <td>
                                                <div class="text-bold">{{ $job->schoolProfile->school_name }}</div>
                                                <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> {{ $job->schoolProfile->address }}</small>
                                            </td>
                                            <td>
                                                <div><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($job->date)->format('M d, Y') }}</div>
                                                <small class="text-muted"><i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($job->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($job->end_time)->format('h:i A') }}</small>
                                            </td>
                                            <td class="text-right align-middle">
                                                <form action="{{ route('teacher.book', $job->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                        <i class="fas fa-check-circle mr-1"></i> Book Job
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center p-4 text-muted">
                                                <i class="fas fa-search mb-2 fa-lg text-info"></i>
                                                <p class="mb-0">No matching open jobs found at the moment. Try broadening your preferences!</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="card card-outline card-success" id="calendar-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-week mr-1 text-success"></i>
                        My Booking Schedule
                    </h3>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Preferences Form -->
        <div class="col-lg-4" id="preferences-card">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog mr-1 text-info"></i>
                        Preferences & Rates
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.preferences.update') }}" method="POST">
                        @csrf
                        
                        <!-- Hourly Rate -->
                        <div class="form-group">
                            <label for="hourly_rate">Desired Hourly Rate ($)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" step="0.50" name="hourly_rate" id="hourly_rate" class="form-control" 
                                       value="{{ old('hourly_rate', $teacherProfile->hourly_rate) }}" required>
                            </div>
                        </div>

                        <!-- Grades Preferences -->
                        <div class="form-group">
                            <label class="d-block">Preferred Grade Levels</label>
                            <div class="row" style="max-height: 180px; overflow-y: auto; padding: 5px; border: 1px solid #ced4da; border-radius: 4px;">
                                @php
                                    $currentGrades = $teacherProfile->classroom_preferences['grades'] ?? [];
                                @endphp
                                @for($i = 1; $i <= 12; $i++)
                                    @php $gradeStr = "Grade " . $i; @endphp
                                    <div class="col-6 mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="grades[]" id="grade_{{ $i }}" value="{{ $gradeStr }}"
                                                {{ in_array($gradeStr, $currentGrades) ? 'checked' : '' }}>
                                            <label for="grade_{{ $i }}" class="custom-control-label font-weight-normal">{{ $gradeStr }}</label>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Subject Preferences -->
                        <div class="form-group">
                            <label class="d-block">Preferred Subjects</label>
                            <div class="row" style="max-height: 180px; overflow-y: auto; padding: 5px; border: 1px solid #ced4da; border-radius: 4px;">
                                @php
                                    $currentSubjects = $teacherProfile->classroom_preferences['subjects'] ?? [];
                                    $availableSubjects = ['Mathematics', 'Science', 'Chemistry', 'Biology', 'Physics', 'English', 'History', 'Art', 'Music', 'Drama', 'Physical Education'];
                                @endphp
                                @foreach($availableSubjects as $idx => $subj)
                                    <div class="col-6 mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="subjects[]" id="subj_{{ $idx }}" value="{{ $subj }}"
                                                {{ in_array($subj, $currentSubjects) ? 'checked' : '' }}>
                                            <label for="subj_{{ $idx }}" class="custom-control-label font-weight-normal">{{ $subj }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Preferred Schools -->
                        <div class="form-group">
                            <label for="preferred_schools">Preferred Schools</label>
                            <select name="preferred_schools[]" id="preferred_schools" class="form-control" multiple style="height: 120px;">
                                @php
                                    $currentSchools = $teacherProfile->classroom_preferences['preferred_schools'] ?? [];
                                @endphp
                                @foreach($schools as $sch)
                                    <option value="{{ $sch->id }}" {{ in_array($sch->id, $currentSchools) ? 'selected' : '' }}>
                                        {{ $sch->school_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple schools.</small>
                        </div>

                        <button type="submit" class="btn btn-info btn-block font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                themeSystem: 'standard',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: [
                    @foreach($bookings as $booking)
                    @if($booking->substituteJob)
                    {
                        title: "{{ $booking->substituteJob->subject }} ({{ $booking->substituteJob->grade_level }}) - {{ $booking->substituteJob->schoolProfile->school_name }}",
                        start: "{{ $booking->substituteJob->date->toDateString() }}T{{ $booking->substituteJob->start_time }}",
                        end: "{{ $booking->substituteJob->date->toDateString() }}T{{ $booking->substituteJob->end_time }}",
                        backgroundColor: "{{ $booking->status === 'completed' ? '#6c757d' : '#28a745' }}",
                        borderColor: "{{ $booking->status === 'completed' ? '#6c757d' : '#28a745' }}",
                        allDay: false
                    },
                    @endif
                    @endforeach
                ]
            });
            calendar.render();
        });
    </script>
@endsection
