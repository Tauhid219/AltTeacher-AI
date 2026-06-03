@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User: ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('sidebar-menu')
    <li class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li class="nav-header">SYSTEM MANAGEMENT</li>
    <li class="nav-item">
        <a href="{{ route('admin.users.index') }}" class="nav-link active">
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
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-1 text-warning"></i> Edit System User</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- User Name -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row bg-light p-3 rounded mb-3 border mx-0">
                            <div class="col-12 mb-2">
                                <label class="text-xs text-muted text-uppercase tracking-wider">Change Password (Leave blank to keep current password)</label>
                            </div>
                            <div class="col-6">
                                <!-- Password -->
                                <div class="form-group mb-0">
                                    <label for="password" class="font-weight-normal text-sm">New Password</label>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <!-- Confirm Password -->
                                <div class="form-group mb-0">
                                    <label for="password_confirmation" class="font-weight-normal text-sm">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Retype password">
                                </div>
                            </div>
                        </div>

                        <!-- Roles selection checkboxes -->
                        <div class="form-group">
                            <label class="d-block">User Roles</label>
                            <div class="row border p-3 rounded bg-light mx-0">
                                @forelse($roles as $role)
                                    <div class="col-sm-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->name }}" 
                                                {{ (is_array(old('roles')) && in_array($role->name, old('roles'))) || (!is_array(old('roles')) && $user->hasRole($role->name)) ? 'checked' : '' }}>
                                            <label for="role_{{ $role->id }}" class="custom-control-label font-weight-normal">{{ $role->name }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted font-italic">No roles configured.</div>
                                @endforelse
                            </div>
                            @error('roles')
                                <span class="text-danger text-sm d-block mt-1"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group mt-4 mb-0">
                            <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Update User</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary font-weight-bold ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
