<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'model',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'route',
        'method',
        'url',
        'module',
        'submodule',
        'metadata',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Disable updated_at (logs are immutable)
    public $timestamps = false;
    const UPDATED_AT = null;

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by model
     */
    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope: Filter by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Search by description
     */
    public function scopeSearchDescription($query, $search)
    {
        return $query->where('description', 'like', "%{$search}%");
    }

    /**
     * Scope: Search by IP address
     */
    public function scopeSearchIp($query, $ip)
    {
        return $query->where('ip_address', 'like', "%{$ip}%");
    }

    /**
     * Get action badge color
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'create' => 'success',
            'update' => 'primary',
            'delete' => 'danger',
            'view' => 'warning',
            'login', 'logout' => 'secondary',
            'export', 'import' => 'info',
            default => 'dark',
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'create' => 'fa-plus-circle',
            'update' => 'fa-edit',
            'delete' => 'fa-trash',
            'view' => 'fa-eye',
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'export' => 'fa-download',
            'import' => 'fa-upload',
            default => 'fa-info-circle',
        };
    }
}
