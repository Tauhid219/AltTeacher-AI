@extends('layouts.admin')

@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profile Settings</li>
@endsection

@section('sidebar-menu')
    @if(auth()->user()->hasRole('teacher'))
        <li class="nav-item">
            <a href="{{ route('teacher.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-header">ONBOARDING & PREFERENCES</li>
        <li class="nav-item">
            <a href="{{ route('teacher.dashboard') }}#preferences-card" class="nav-link">
                <i class="nav-icon fas fa-sliders-h"></i>
                <p>Classroom Preferences</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher.dashboard') }}#calendar-card" class="nav-link">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>My Schedule Calendar</p>
            </a>
        </li>
    @elseif(auth()->user()->hasRole('school_admin'))
        <li class="nav-item">
            <a href="{{ route('school.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-header">JOB MANAGEMENT</li>
        <li class="nav-item">
            <a href="{{ route('school.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-plus-circle"></i>
                <p>Post a Job</p>
            </a>
        </li>
    @elseif(auth()->user()->hasRole('district_admin'))
        <li class="nav-item">
            <a href="{{ route('district.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-header">ONBOARDING & CREDENTIALS</li>
        <li class="nav-item">
            <a href="{{ route('district.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-shield-alt"></i>
                <p>Verify Certificates</p>
            </a>
        </li>
    @elseif(auth()->user()->hasRole('super_admin'))
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
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
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <!-- Profile Info Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-1 text-primary"></i> Profile Information</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Update Password Card -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key mr-1 text-warning"></i> Update Password</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Card -->
            <div class="card card-danger card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trash-alt mr-1 text-danger"></i> Delete Account</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-show delete modal if validation failed
            @if($errors->userDeletion->isNotEmpty())
                $('#confirm-user-deletion-modal').modal('show');
            @endif

            // Fade out saved toasts
            @if(session('status') === 'profile-updated')
                setTimeout(function() {
                    $('#profile-saved-toast').fadeOut('slow');
                }, 3000);
            @endif

            @if(session('status') === 'password-updated')
                setTimeout(function() {
                    $('#password-saved-toast').fadeOut('slow');
                }, 3000);
            @endif
        });
    </script>
@endsection

