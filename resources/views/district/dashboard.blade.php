@extends('layouts.admin')

@section('title', 'District Dashboard')
@section('page-title', 'District Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">District Dashboard</li>
@endsection

@section('sidebar-menu')
    <li class="nav-item">
        <a href="{{ route('district.dashboard') }}" class="nav-link active">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-header">ONBOARDING & CREDENTIALS</li>
    <li class="nav-item">
        <a href="{{ route('district.dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-shield-alt"></i>
            <p>Verify Certificates (below)</p>
        </a>
    </li>
@endsection

@section('content')
    <!-- District Info Boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Substitutes</span>
                    <span class="info-box-number">{{ $totalTeachersCount }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Approvals</span>
                    <span class="info-box-number">{{ $pendingTeachersCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Bookings</span>
                    <span class="info-box-number">{{ $activeBookingsCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payroll Distributed</span>
                    <span class="info-box-number">${{ number_format($totalPayroll, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Approvals Section -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-check mr-1"></i> Teacher Onboarding & Verification Approval</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Teacher Name</th>
                                <th>Preferences (Subjects/Grades)</th>
                                <th>Hourly Rate</th>
                                <th>Uploaded Credentials</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teachers as $teacher)
                                <tr>
                                    <td>
                                        <div class="text-bold">{{ $teacher->user->name }}</div>
                                        <div class="text-muted text-xs">{{ $teacher->user->email }}</div>
                                    </td>
                                    <td>
                                        <div><strong>Grades:</strong> {{ implode(', ', $teacher->classroom_preferences['grades'] ?? ['None']) }}</div>
                                        <div><strong>Subjects:</strong> {{ implode(', ', $teacher->classroom_preferences['subjects'] ?? ['None']) }}</div>
                                    </td>
                                    <td>${{ number_format($teacher->hourly_rate, 2) }}/hr</td>
                                    <td>
                                        <ul class="list-unstyled mb-0 text-sm">
                                            @forelse($teacher->credentials as $credential)
                                                <li>
                                                    @if($credential->verification_status === 'verified')
                                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                                    @elseif($credential->verification_status === 'rejected')
                                                        <i class="fas fa-times-circle text-danger mr-1"></i>
                                                    @else
                                                        <i class="fas fa-clock text-warning mr-1"></i>
                                                    @endif
                                                    <strong>{{ str_replace('_', ' ', ucfirst($credential->document_type)) }}:</strong>
                                                    @if($credential->extracted_info)
                                                        <span class="text-xs text-muted">Parsed: License #{{ $credential->extracted_info['license_number'] ?? $credential->extracted_info['case_number'] ?? 'N/A' }}</span>
                                                    @else
                                                        <span class="text-xs text-muted font-italic">Extraction Pending</span>
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="text-muted font-italic">No files uploaded yet.</li>
                                            @endforelse
                                        </ul>
                                    </td>
                                    <td>
                                        @if($teacher->onboarding_status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($teacher->onboarding_status === 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-warning">Pending Review</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($teacher->onboarding_status === 'pending')
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('district.approve', $teacher->id) }}" method="POST" class="mr-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                                </form>
                                                <form action="{{ route('district.reject', $teacher->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted text-xs"><i class="fas fa-lock mr-1"></i> Finalized</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">No teacher onboarding requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
