@extends('layouts.app')

@section('title', 'Detail Activity Log - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>Detail Activity Log
                </h2>
                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="row">
                <!-- Main Info -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Informasi Log</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Tanggal & Waktu</th>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($log->user)
                                        <strong>{{ $log->user->name }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                        <span class="text-muted">{{ $log->user_name ?? 'System' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        <span class="badge bg-{{ $log->action_color }} fs-6">
                                            <i class="fas {{ $log->action_icon }} me-1"></i>
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Model</th>
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
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $log->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Module</th>
                                    <td>
                                        @if($log->module)
                                        <span class="badge bg-info">
                                            {{ ucwords(str_replace('-', ' ', $log->module)) }}
                                        </span>
                                        @if($log->submodule)
                                        <br><small class="text-muted">{{ $log->submodule }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($log->changed_fields)
                                <tr>
                                    <th>Changed Fields</th>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ $log->changed_fields }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Old vs New Values -->
                    @if($log->old_values || $log->new_values)
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Perubahan Data</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($log->old_values)
                                <div class="col-md-6">
                                    <h6 class="text-danger mb-3">
                                        <i class="fas fa-arrow-left me-1"></i>Data Lama (Old Values)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($log->old_values as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if(is_array($value))
                                                        <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @else
                                                        {{ $value ?? '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($log->new_values)
                                <div class="col-md-6">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-arrow-right me-1"></i>Data Baru (New Values)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($log->new_values as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if(is_array($value))
                                                        <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @else
                                                        {{ $value ?? '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Context Info -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Context Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="120">IP Address</th>
                                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Route</th>
                                    <td><code>{{ $log->route ?? '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Method</th>
                                    <td><span class="badge bg-secondary">{{ $log->method ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <th>URL</th>
                                    <td>
                                        <small class="text-break">{{ $log->url ?? '-' }}</small>
                                    </td>
                                </tr>
                                @if($log->user_agent)
                                <tr>
                                    <th>User Agent</th>
                                    <td>
                                        <small class="text-break">{{ $log->user_agent }}</small>
                                    </td>
                                </tr>
                                @endif
                                @if($log->severity != 'info')
                                <tr>
                                    <th>Severity</th>
                                    <td>
                                        <span class="badge bg-{{ $log->severity == 'error' ? 'danger' : ($log->severity == 'warning' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($log->severity) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($log->metadata)
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Metadata</h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




@section('title', 'Detail Activity Log - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>Detail Activity Log
                </h2>
                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="row">
                <!-- Main Info -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Informasi Log</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Tanggal & Waktu</th>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($log->user)
                                        <strong>{{ $log->user->name }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                        <span class="text-muted">{{ $log->user_name ?? 'System' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        <span class="badge bg-{{ $log->action_color }} fs-6">
                                            <i class="fas {{ $log->action_icon }} me-1"></i>
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Model</th>
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
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $log->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Module</th>
                                    <td>
                                        @if($log->module)
                                        <span class="badge bg-info">
                                            {{ ucwords(str_replace('-', ' ', $log->module)) }}
                                        </span>
                                        @if($log->submodule)
                                        <br><small class="text-muted">{{ $log->submodule }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($log->changed_fields)
                                <tr>
                                    <th>Changed Fields</th>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ $log->changed_fields }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Old vs New Values -->
                    @if($log->old_values || $log->new_values)
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Perubahan Data</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($log->old_values)
                                <div class="col-md-6">
                                    <h6 class="text-danger mb-3">
                                        <i class="fas fa-arrow-left me-1"></i>Data Lama (Old Values)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($log->old_values as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if(is_array($value))
                                                        <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @else
                                                        {{ $value ?? '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($log->new_values)
                                <div class="col-md-6">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-arrow-right me-1"></i>Data Baru (New Values)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($log->new_values as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if(is_array($value))
                                                        <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @else
                                                        {{ $value ?? '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Context Info -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Context Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="120">IP Address</th>
                                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Route</th>
                                    <td><code>{{ $log->route ?? '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Method</th>
                                    <td><span class="badge bg-secondary">{{ $log->method ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <th>URL</th>
                                    <td>
                                        <small class="text-break">{{ $log->url ?? '-' }}</small>
                                    </td>
                                </tr>
                                @if($log->user_agent)
                                <tr>
                                    <th>User Agent</th>
                                    <td>
                                        <small class="text-break">{{ $log->user_agent }}</small>
                                    </td>
                                </tr>
                                @endif
                                @if($log->severity != 'info')
                                <tr>
                                    <th>Severity</th>
                                    <td>
                                        <span class="badge bg-{{ $log->severity == 'error' ? 'danger' : ($log->severity == 'warning' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($log->severity) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($log->metadata)
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Metadata</h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


