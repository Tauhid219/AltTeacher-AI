@extends('layouts.admin')

@section('title', 'School Dashboard')
@section('page-title', 'School Admin Dashboard - ' . $schoolProfile->school_name)

@section('breadcrumb')
    <li class="breadcrumb-item active">School Dashboard</li>
@endsection

@section('sidebar-menu')
    <li class="nav-item">
        <a href="{{ route('school.dashboard') }}" class="nav-link active">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-header">JOB MANAGEMENT</li>
    <li class="nav-item">
        <a href="{{ route('school.dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-plus-circle"></i>
            <p>Post a Job (Form below)</p>
        </a>
    </li>
@endsection

@section('content')
    <!-- Info Boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-bullhorn"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Open Postings</span>
                    <span class="info-box-number">{{ $openJobsCount }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Filled Bookings</span>
                    <span class="info-box-number">{{ $filledJobsCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Available Subs</span>
                    <span class="info-box-number">{{ $approvedTeachersCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">This Month Spend</span>
                    <span class="info-box-number">${{ number_format($monthSpend, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Job Postings List -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Substitute Job Postings</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Booked Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobs as $job)
                                <tr>
                                    <td>{{ $job->subject }}</td>
                                    <td>{{ $job->grade_level }}</td>
                                    <td>{{ $job->date->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($job->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($job->end_time)->format('h:i A') }}</td>
                                    <td>
                                        @if($job->status === 'open')
                                            <span class="badge badge-info">Open</span>
                                        @elseif($job->status === 'filled')
                                            <span class="badge badge-success">Filled</span>
                                        @elseif($job->status === 'completed')
                                            <span class="badge badge-secondary">Completed</span>
                                        @else
                                            <span class="badge badge-danger">{{ ucfirst($job->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->booking && $job->booking->teacherProfile)
                                            {{ $job->booking->teacherProfile->user->name }}
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">No job postings found for your school.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Post a Job Form -->
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Post Substitute Job</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('school.jobs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" id="subject" placeholder="e.g. Mathematics, Biology" required>
                            @error('subject')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="grade_level">Grade Level</label>
                            <select name="grade_level" class="form-control" id="grade_level" required>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" id="date" required>
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" name="start_time" class="form-control" id="start_time" value="08:00" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="end_time">End Time</label>
                                    <input type="time" name="end_time" class="form-control" id="end_time" value="15:00" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description & Lesson Continuity Notes</label>
                            <textarea name="description" class="form-control" id="description" rows="3" placeholder="Provide topic outline, classroom rules..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="lesson_plan_file">Upload Lesson Plan (PDF/DOCX/TXT - Optional)</label>
                            <div class="custom-file">
                                <input type="file" name="lesson_plan_file" class="custom-file-input" id="lesson_plan_file">
                                <label class="custom-file-label" for="lesson_plan_file">Choose file</label>
                            </div>
                            <small class="form-text text-muted">The AI will adapt this document for the substitute teacher.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-paper-plane mr-1"></i> Post Job</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Timesheet Approvals Row -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1 text-warning"></i> Timesheet Approvals & Billing</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Teacher Name</th>
                                    <th>Subject & Grade</th>
                                    <th>Hours</th>
                                    <th>Calculated Pay</th>
                                    <th>Check In & Out</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timesheets as $ts)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $ts->booking->teacherProfile->user->name }}</div>
                                            <small class="text-muted">{{ $ts->booking->teacherProfile->user->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info mb-1">{{ $ts->booking->substituteJob->grade_level }}</span>
                                            <h6 class="mb-0 text-bold text-primary">{{ $ts->booking->substituteJob->subject }}</h6>
                                        </td>
                                        <td>
                                            @if($ts->calculated_hours)
                                                {{ $ts->calculated_hours }} hrs
                                            @else
                                                <span class="text-muted font-italic">Active / Not Checked Out</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ts->calculated_pay)
                                                <strong>${{ number_format($ts->calculated_pay, 2) }}</strong>
                                                <small class="text-muted d-block">(${{ number_format($ts->booking->teacherProfile->hourly_rate, 2) }}/hr)</small>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td>
                                            <div><small><strong>In:</strong> {{ $ts->check_in_time ? $ts->check_in_time->format('M d, Y h:i A') : 'N/A' }}</small></div>
                                            <div><small><strong>Out:</strong> {{ $ts->check_out_time ? $ts->check_out_time->format('M d, Y h:i A') : 'N/A' }}</small></div>
                                        </td>
                                        <td>
                                            @if($ts->status === 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($ts->status === 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-warning">Pending Review</span>
                                            @endif
                                        </td>
                                        <td class="text-right align-middle">
                                            @if($ts->status === 'pending' && $ts->check_out_time)
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('school.timesheets.approve', $ts->id) }}" method="POST" class="d-inline mr-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-xs font-weight-bold">
                                                            <i class="fas fa-check mr-1"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('school.timesheets.reject', $ts->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-xs font-weight-bold">
                                                            <i class="fas fa-times mr-1"></i> Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif(!$ts->check_out_time)
                                                <span class="text-xs text-muted font-italic">In Progress...</span>
                                            @else
                                                <span class="text-xs text-muted"><i class="fas fa-lock mr-1"></i> Locked</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center p-4 text-muted">No timesheets submitted for your school's jobs yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endsection
