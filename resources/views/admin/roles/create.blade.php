@extends('layouts.admin')

@section('title', 'Create Role')
@section('page-title', 'Create New Role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Create</li>
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
        <a href="{{ route('admin.users.index') }}" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>User Management</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.roles.index') }}" class="nav-link active">
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
                    <h3 class="card-title"><i class="fas fa-plus mr-1 text-warning"></i> Create System Role</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <!-- Role Name -->
                        <div class="form-group">
                            <label for="name">Role Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. support_staff" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <!-- Permissions selection checkboxes -->
                        <div class="form-group">
                            <label class="d-block">Select Permissions</label>
                            <div class="row border p-3 rounded bg-light mx-0">
                                @forelse($permissions as $perm)
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="permissions[]" id="perm_{{ $perm->id }}" value="{{ $perm->name }}" {{ is_array(old('permissions')) && in_array($perm->name, old('permissions')) ? 'checked' : '' }}>
                                            <label for="perm_{{ $perm->id }}" class="custom-control-label font-weight-normal text-capitalize">{{ str_replace('_', ' ', $perm->name) }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted font-italic">No permissions configured in the database.</div>
                                @endforelse
                            </div>
                            @error('permissions')
                                <span class="text-danger text-sm d-block mt-1"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group mt-4 mb-0">
                            <button type="submit" class="btn btn-warning font-weight-bold text-white"><i class="fas fa-save mr-1"></i> Create Role</button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary font-weight-bold ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
