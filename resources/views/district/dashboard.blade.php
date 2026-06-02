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

    <!-- Analytics / Charts Section -->
    <div class="row mt-4">
        <!-- School Expenditures Bar Chart -->
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1 text-success"></i>
                        Approved School Expenditures
                    </h3>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="schoolSpendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timesheet Status Doughnut Chart -->
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1 text-info"></i>
                        Timesheet Processing Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="timesheetStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. School Spend Bar Chart
            const schoolSpendData = @json($schoolSpending);
            const schoolLabels = Object.keys(schoolSpendData);
            const schoolValues = Object.values(schoolSpendData);

            const ctx1 = document.getElementById('schoolSpendChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: schoolLabels.length > 0 ? schoolLabels : ['No Data'],
                    datasets: [{
                        label: 'Total Approved Spend ($)',
                        data: schoolValues.length > 0 ? schoolValues : [0],
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 2. Timesheet Status Doughnut Chart
            const statusData = @json($statusBreakdown);
            const statusLabels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
            const statusValues = Object.values(statusData);

            // Default color scheme matching status badges
            const colorMapping = {
                'Approved': '#28a745',
                'Pending': '#ffc107',
                'Rejected': '#dc3545'
            };
            const bgColors = Object.keys(statusData).map(s => colorMapping[s.charAt(0).toUpperCase() + s.slice(1)] || '#6c757d');

            const ctx2 = document.getElementById('timesheetStatusChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.length > 0 ? statusLabels : ['No Timesheets'],
                    datasets: [{
                        data: statusValues.length > 0 ? statusValues : [1],
                        backgroundColor: statusValues.length > 0 ? bgColors : ['#e9ecef'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endsection
