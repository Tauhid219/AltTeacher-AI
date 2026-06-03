@extends('layouts.admin')

@section('title', 'Role Management')
@section('page-title', 'Role Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Roles</li>
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
        <div class="col-12">
            <!-- Session Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="icon fas fa-check mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="icon fas fa-ban mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> System Roles & Permissions</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-warning font-weight-bold text-white">
                            <i class="fas fa-plus mr-1 text-white"></i> Create New Role
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Role Name</th>
                                    <th>Assigned Permissions</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>
                                            <span class="badge badge-warning font-weight-bold text-sm text-dark">{{ $role->name }}</span>
                                        </td>
                                        <td>
                                            @if($role->name === 'super_admin')
                                                <span class="badge badge-success text-xs font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Full Access (Gate Bypass)</span>
                                            @else
                                                @forelse($role->permissions as $p)
                                                    <span class="badge badge-light border text-xs mr-1">{{ str_replace('_', ' ', $p->name) }}</span>
                                                @empty
                                                    <span class="text-muted font-italic text-xs">No permissions assigned</span>
                                                @endforelse
                                            @endif
                                        </td>
                                        <td class="text-right align-middle">
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning btn-xs font-weight-bold mr-1">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs font-weight-bold" {{ $role->name === 'super_admin' ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">No roles configured.</td>
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
