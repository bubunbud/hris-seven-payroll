@extends('layouts.app')

@section('title', 'Activity Logs - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>Activity Logs
                </h2>
                <div>
                    <a href="{{ route('logs.export', request()->all()) }}" class="btn btn-success me-2">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('logs.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Semua User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select" id="action" name="action">
                                    <option value="">Semua Action</option>
                                    @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="model" class="form-label">Model</label>
                                <select class="form-select" id="model" name="model">
                                    <option value="">Semua Model</option>
                                    @foreach($models as $model)
                                    <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                                        {{ $model }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-select" id="module" name="module">
                                    <option value="">Semua Module</option>
                                    @foreach($modules as $module)
                                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('-', ' ', $module)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Description atau IP Address">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                    value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 me-2">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Model</th>
                                    <th>Description</th>
                                    <th>Module</th>
                                    <th>IP Address</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($log->user)
                                        <strong>{{ $log->user->name }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                        <span class="text-muted">{{ $log->user_name ?? 'System' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->action_color }}">
                                            <i class="fas {{ $log->action_icon }} me-1"></i>
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->model)
                                        <strong>{{ $log->model }}</strong>
                                        @if($log->model_id)
                                        <br><small class="text-muted">ID: {{ $log->model_id }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 300px;" title="{{ $log->description }}">
                                            {{ $log->description ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->module)
                                        <span class="badge bg-info">
                                            {{ ucwords(str_replace('-', ' ', $log->module)) }}
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->ip_address ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('logs.show', $log->id) }}" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">Tidak ada data log</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




@section('title', 'Activity Logs - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>Activity Logs
                </h2>
                <div>
                    <a href="{{ route('logs.export', request()->all()) }}" class="btn btn-success me-2">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('logs.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Semua User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select" id="action" name="action">
                                    <option value="">Semua Action</option>
                                    @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="model" class="form-label">Model</label>
                                <select class="form-select" id="model" name="model">
                                    <option value="">Semua Model</option>
                                    @foreach($models as $model)
                                    <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                                        {{ $model }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-select" id="module" name="module">
                                    <option value="">Semua Module</option>
                                    @foreach($modules as $module)
                                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('-', ' ', $module)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Description atau IP Address">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                    value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 me-2">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Model</th>
                                    <th>Description</th>
                                    <th>Module</th>
                                    <th>IP Address</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($log->user)
                                        <strong>{{ $log->user->name }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                        <span class="text-muted">{{ $log->user_name ?? 'System' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->action_color }}">
                                            <i class="fas {{ $log->action_icon }} me-1"></i>
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->model)
                                        <strong>{{ $log->model }}</strong>
                                        @if($log->model_id)
                                        <br><small class="text-muted">ID: {{ $log->model_id }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 300px;" title="{{ $log->description }}">
                                            {{ $log->description ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->module)
                                        <span class="badge bg-info">
                                            {{ ucwords(str_replace('-', ' ', $log->module)) }}
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->ip_address ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('logs.show', $log->id) }}" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">Tidak ada data log</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


