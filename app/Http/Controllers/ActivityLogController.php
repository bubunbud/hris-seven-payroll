<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by model
        if ($request->filled('model')) {
            $query->byModel($request->model);
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from . ' 00:00:00';
            $dateTo = $request->date_to ?? date('Y-m-d') . ' 23:59:59';
            if ($request->filled('date_to')) {
                $dateTo = $request->date_to . ' 23:59:59';
            }
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Search by description
        if ($request->filled('search')) {
            $query->searchDescription($request->search);
        }

        // Search by IP
        if ($request->filled('ip_address')) {
            $query->searchIp($request->ip_address);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = User::where('is_active', true)->orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->sort();
        $models = ActivityLog::distinct()->whereNotNull('model')->pluck('model')->sort();
        $modules = ActivityLog::distinct()->whereNotNull('module')->pluck('module')->sort();

        return view('logs.index', compact('logs', 'users', 'actions', 'models', 'modules'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);

        return view('logs.show', compact('log'));
    }

    /**
     * Export logs to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        if ($request->filled('model')) {
            $query->byModel($request->model);
        }
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }
        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from . ' 00:00:00';
            $dateTo = $request->date_to ?? date('Y-m-d') . ' 23:59:59';
            if ($request->filled('date_to')) {
                $dateTo = $request->date_to . ' 23:59:59';
            }
            $query->byDateRange($dateFrom, $dateTo);
        }

        $logs = $query->get();

        $filename = 'activity_logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Tanggal',
                'User',
                'Action',
                'Model',
                'Model ID',
                'Description',
                'Module',
                'IP Address',
                'Route',
                'Method',
            ]);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name ?? ($log->user->name ?? 'System'),
                    $log->action,
                    $log->model ?? '-',
                    $log->model_id ?? '-',
                    $log->description ?? '-',
                    $log->module ?? '-',
                    $log->ip_address ?? '-',
                    $log->route ?? '-',
                    $log->method ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
