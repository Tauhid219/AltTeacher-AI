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

            <!-- My Booked Jobs Card -->
            <div class="card card-outline card-success mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check mr-1 text-success"></i> 
                        My Scheduled Bookings
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">{{ count($bookings) }} Booking(s)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject & Grade</th>
                                    <th>School</th>
                                    <th>Date & Time</th>
                                    <th class="text-right">Classroom Prep</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                    <tr>
                                        <td>
                                            <span class="badge badge-success mb-1">{{ $booking->substituteJob->grade_level }}</span>
                                            <h6 class="mb-0 text-bold">{{ $booking->substituteJob->subject }}</h6>
                                        </td>
                                        <td>
                                            <div class="text-bold">{{ $booking->substituteJob->schoolProfile->school_name }}</div>
                                            <small class="text-muted">{{ $booking->substituteJob->schoolProfile->address }}</small>
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($booking->substituteJob->date)->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($booking->substituteJob->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->substituteJob->end_time)->format('h:i A') }}</small>
                                        </td>
                                        <td class="text-right align-middle">
                                            @if($booking->lessonPlan)
                                                <button class="btn btn-info btn-xs font-weight-bold view-prep-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#prepModal"
                                                        data-subject="{{ $booking->substituteJob->subject }}"
                                                        data-grade="{{ $booking->substituteJob->grade_level }}"
                                                        data-school="{{ $booking->substituteJob->schoolProfile->school_name }}"
                                                        data-summary="{{ $booking->lessonPlan->ai_summary }}"
                                                        data-quizzes="{{ json_encode($booking->lessonPlan->ai_generated_activities['quizzes'] ?? []) }}"
                                                        data-icebreakers="{{ json_encode($booking->lessonPlan->ai_generated_activities['icebreakers'] ?? []) }}"
                                                        data-export-url="{{ route('teacher.lesson_plan.pdf', $booking->id) }}">
                                                    <i class="fas fa-magic mr-1"></i> View Prep Packet
                                                </button>
                                            @else
                                                <span class="text-muted text-xs font-italic">No Packet Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4 text-muted">
                                            <i class="fas fa-calendar-times mb-2 fa-lg text-secondary"></i>
                                            <p class="mb-0">You have no booked jobs yet. Browse and book matching jobs above!</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

            <!-- Upload Credentials Card -->
            <div class="card card-outline card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-1 text-warning"></i>
                        AI Verification Documents
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Upload Form -->
                    <form action="{{ route('teacher.credentials.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label for="document_type">Document Type</label>
                            <select name="document_type" id="document_type" class="form-control" required>
                                <option value="state_teaching_license">State Teaching License</option>
                                <option value="background_check">Background Check</option>
                                <option value="government_id">Government ID Card</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="document">Upload File (PDF, PNG, JPG)</label>
                            <div class="custom-file">
                                <input type="file" name="document" class="custom-file-input" id="document" required>
                                <label class="custom-file-label" for="document">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Maximum file size: 5MB.</small>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block font-weight-bold">
                            <i class="fas fa-upload mr-1"></i> Upload for AI Verification
                        </button>
                    </form>

                    <!-- Existing Credentials List -->
                    <label class="d-block border-top pt-3">Verification History</label>
                    <ul class="list-unstyled mb-0">
                        @forelse($teacherProfile->credentials as $cred)
                            <li class="p-2 border rounded mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-sm">
                                        {{ str_replace('_', ' ', ucfirst($cred->document_type)) }}
                                    </span>
                                    @if($cred->verification_status === 'verified')
                                        <span class="badge badge-success">Verified</span>
                                    @elseif($cred->verification_status === 'rejected')
                                        <span class="badge badge-danger">Rejected/Expired</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted">
                                    @if($cred->expiry_date)
                                        <span>Expires: {{ \Carbon\Carbon::parse($cred->expiry_date)->format('M d, Y') }}</span>
                                    @else
                                        <span class="font-italic">No expiration date parsed</span>
                                    @endif
                                </div>
                                @if($cred->extracted_info)
                                    <div class="bg-light p-1 mt-1 rounded text-xs text-secondary border">
                                        <strong>AI Extracted Name:</strong> {{ $cred->extracted_info['name'] ?? 'N/A' }}<br>
                                        <strong>License #:</strong> {{ $cred->extracted_info['license_number'] ?? $cred->extracted_info['case_number'] ?? 'N/A' }}
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="text-center p-3 text-muted border rounded bg-light">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-1 text-secondary"></i>
                                <p class="mb-0 text-xs font-italic">No documents uploaded yet.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Classroom Prep Packet -->
    <div class="modal fade" id="prepModal" tabindex="-1" aria-labelledby="prepModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none;">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="prepModalLabel"><i class="fas fa-magic mr-1"></i> AI Adapted Classroom Prep Packet</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <h6 class="text-bold text-success border-bottom pb-2 mb-3" id="prep-modal-header">Classroom Details</h6>
                    
                    <div class="mb-4">
                        <label class="text-xs text-muted font-weight-bold uppercase tracking-wider d-block mb-1">AI Lesson Summary</label>
                        <div class="p-3 bg-white rounded text-sm border-left border-success" id="prep-modal-summary" style="border-left-width: 4px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            Summary goes here...
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs text-muted font-weight-bold uppercase tracking-wider d-block mb-1">Quick Icebreaker Games (3 Activities)</label>
                        <div id="prep-modal-icebreakers">
                            <!-- list of games -->
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs text-muted font-weight-bold uppercase tracking-wider d-block mb-1">Lesson Continuity Quiz (10 Questions)</label>
                        <div id="prep-modal-quizzes" style="max-height: 250px; overflow-y: auto;" class="p-2 border rounded bg-white">
                            <!-- quiz list -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
                    <a href="#" id="prep-modal-download-btn" class="btn btn-success font-weight-bold"><i class="fas fa-file-pdf mr-1"></i> Export to PDF</a>
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
                    @foreach($bookings as $bookingsItem)
                    @if($bookingsItem->substituteJob)
                    {
                        title: "{{ $bookingsItem->substituteJob->subject }} ({{ $bookingsItem->substituteJob->grade_level }}) - {{ $bookingsItem->substituteJob->schoolProfile->school_name }}",
                        start: "{{ $bookingsItem->substituteJob->date->toDateString() }}T{{ $bookingsItem->substituteJob->start_time }}",
                        end: "{{ $bookingsItem->substituteJob->date->toDateString() }}T{{ $bookingsItem->substituteJob->end_time }}",
                        backgroundColor: "{{ $bookingsItem->status === 'completed' ? '#6c757d' : '#28a745' }}",
                        borderColor: "{{ $bookingsItem->status === 'completed' ? '#6c757d' : '#28a745' }}",
                        allDay: false
                    },
                    @endif
                    @endforeach
                ]
            });
            calendar.render();

            // Bootstrap custom file input dynamic filename display update
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // View Prep Modal Dynamic Populator
            $(document).on('click', '.view-prep-btn', function() {
                const subject = $(this).data('subject');
                const grade = $(this).data('grade');
                const school = $(this).data('school');
                const summary = $(this).data('summary');
                const quizzes = $(this).data('quizzes');
                const icebreakers = $(this).data('icebreakers');
                const downloadUrl = $(this).data('export-url');

                $('#prep-modal-header').html(`<i class="fas fa-school mr-1"></i> ${school} &mdash; ${grade} ${subject}`);
                $('#prep-modal-summary').text(summary || 'No summary generated.');
                
                // Render Icebreakers
                let icebreakerHtml = '';
                if (Array.isArray(icebreakers) && icebreakers.length > 0) {
                    icebreakers.forEach((game, index) => {
                        icebreakerHtml += `
                            <div class="p-2 mb-2 border rounded bg-white" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <strong class="text-success text-xs">Activity ${index + 1}</strong>
                                <div class="text-sm mt-1 text-secondary">${game}</div>
                            </div>`;
                    });
                } else {
                    icebreakerHtml = '<div class="text-muted text-xs italic p-2">No activities generated.</div>';
                }
                $('#prep-modal-icebreakers').html(icebreakerHtml);

                // Render Quizzes
                let quizHtml = '';
                if (Array.isArray(quizzes) && quizzes.length > 0) {
                    quizzes.forEach((quiz, index) => {
                        let optionsHtml = '';
                        const labels = ['A', 'B', 'C', 'D'];
                        if (Array.isArray(quiz.options)) {
                            quiz.options.forEach((opt, optIndex) => {
                                optionsHtml += `<div class="col-6 text-xs text-muted mb-1"><strong>${labels[optIndex]}.</strong> ${opt}</div>`;
                            });
                        }
                        quizHtml += `
                            <div class="p-2 mb-2 border-bottom">
                                <div class="text-sm font-weight-bold text-dark">${index + 1}. ${quiz.question}</div>
                                <div class="row mt-1">${optionsHtml}</div>
                                <div class="text-xs text-success mt-1"><strong>Correct Answer:</strong> ${quiz.answer}</div>
                            </div>`;
                    });
                } else {
                    quizHtml = '<div class="text-muted text-xs italic p-2">No quiz questions generated.</div>';
                }
                $('#prep-modal-quizzes').html(quizHtml);

                // Update download URL
                $('#prep-modal-download-btn').attr('href', downloadUrl);
            });
        });
    </script>
@endsection
