@extends('layouts.app')

@section('title', 'Login Aktif & Riwayat - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2 class="mb-0">
                    <i class="fas fa-user-clock me-2"></i>Login Aktif &amp; Riwayat
                </h2>
            </div>

            <p class="text-muted mb-4">
                Pengguna dianggap <strong>sedang aktif</strong> bila ada aktivitas dalam
                <strong>{{ $activeWithinMinutes }} menit</strong> terakhir.
                Ubah jendela waktu lewat <code>.env</code>: <code>LOGIN_ACTIVE_WITHIN_MINUTES</code>.
            </p>

            <!-- Sesi aktif -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-circle text-success me-2"></i>Sedang aktif ({{ $activeSessions->count() }})
                </div>
                <div class="card-body p-0">
                    @if($activeSessions->isEmpty())
                        <p class="p-4 mb-0 text-muted">Tidak ada pengguna yang tercatat aktif dalam {{ $activeWithinMinutes }} menit terakhir.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Terakhir aktivitas</th>
                                        <th>IP</th>
                                        <th>Browser / UA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSessions as $row)
                                    <tr>
                                        <td>{{ $row->user->name ?? '—' }}</td>
                                        <td><small>{{ $row->user->email ?? '—' }}</small></td>
                                        <td>{{ $row->last_activity_at?->format('d-m-Y H:i:s') }}</td>
                                        <td><code>{{ $row->ip_address }}</code></td>
                                        <td><small class="text-break">{{ \Illuminate\Support\Str::limit($row->user_agent, 80) }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-history me-2"></i>Riwayat login &amp; logout</h5>
                    <form method="GET" action="{{ route('login-activity.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Semua</option>
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="event" class="form-label">Kejadian</label>
                                <select class="form-select" id="event" name="event">
                                    <option value="">Semua</option>
                                    <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>Login</option>
                                    <option value="logout" {{ request('event') === 'logout' ? 'selected' : '' }}>Logout</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Dari tanggal</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Sampai tanggal</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                                <a href="{{ route('login-activity.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Kejadian</th>
                                    <th>IP</th>
                                    <th>Browser / UA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $row)
                                <tr>
                                    <td>{{ $row->created_at?->format('d-m-Y H:i:s') }}</td>
                                    <td>{{ $row->user->name ?? '—' }} <small class="text-muted d-block">{{ $row->user->email ?? '' }}</small></td>
                                    <td>
                                        @if($row->event === 'login')
                                            <span class="badge bg-success">Login</span>
                                        @else
                                            <span class="badge bg-secondary">Logout</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $row->ip_address }}</code></td>
                                    <td><small class="text-break">{{ \Illuminate\Support\Str::limit($row->user_agent, 80) }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($history->hasPages())
                    <div class="card-body border-top">
                        {{ $history->links() }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
