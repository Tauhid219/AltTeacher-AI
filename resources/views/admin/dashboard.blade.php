@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'System Administrator Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Super Admin Dashboard</li>
@endsection

@section('sidebar-menu')
    <li class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link active">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-header">SYSTEM MANAGEMENT</li>
    <li class="nav-item">
        <a href="{{ route('admin.users.index') }}" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>User Management</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.roles.index') }}" class="nav-link">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Role Management</p>
        </a>
    </li>
@endsection

@section('content')
    <!-- Admin Info Boxes -->
    <div class="row">
        <!-- Users Box -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ $totalUsers }}</span>
                </div>
            </div>
        </div>
        
        <!-- Roles Box -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-shield text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Roles Configured</span>
                    <span class="info-box-number">{{ $totalRoles }}</span>
                </div>
            </div>
        </div>

        <!-- Districts Box -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-school"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Districts</span>
                    <span class="info-box-number">{{ $totalDistricts }}</span>
                </div>
            </div>
        </div>

        <!-- Jobs Box -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Postings</span>
                    <span class="info-box-number">{{ $totalJobs }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Recent Users Table -->
        <div class="col-md-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Recent Users</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-info text-xs">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles and Permissions Summary -->
        <div class="col-md-5">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> System Roles</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-warning font-weight-bold text-white">Manage Roles</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>
                                            <span class="badge badge-warning font-weight-bold text-sm text-dark">{{ $role->name }}</span>
                                        </td>
                                        <td>
                                            @if($role->name === 'super_admin')
                                                <span class="badge badge-success text-xs"><i class="fas fa-check-circle mr-1"></i> All Permissions (Bypass)</span>
                                            @else
                                                @forelse($role->permissions as $p)
                                                    <span class="badge badge-light text-xs border mr-1">{{ str_replace('_', ' ', $p->name) }}</span>
                                                @empty
                                                    <span class="text-muted font-italic text-xs">No permissions</span>
                                                @endforelse
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center p-4">No roles seeded.</td>
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
