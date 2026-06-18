<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'role',
        'is_active',
        'default_dashboard',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Karyawan berdasarkan NIK
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'Nik');
    }

    /**
     * Relasi many-to-many dengan Role (sistem baru)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Check apakah user memiliki role tertentu (backward compatibility dengan kolom role)
     */
    public function hasRole($role)
    {
        return $this->roles()->where(function ($query) use ($role) {
            $query->where('slug', $role)
                ->orWhere('name', $role);
        })->exists();
    }

    /**
     * Check apakah user memiliki salah satu dari roles yang diberikan
     */
    public function hasAnyRole(array $roles)
    {
        return $this->roles()->where(function ($query) use ($roles) {
            $query->whereIn('slug', $roles)
                ->orWhereIn('name', $roles);
        })->exists();
    }

    /**
     * Check apakah user memiliki permission tertentu
     */
    public function hasPermission($permissionSlug)
    {
        if ($this->isAdmin()) {
            return true;
        }

        $hasPermission = $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();

        if ($hasPermission) {
            return true;
        }
        return false;
    }

    /**
     * Check apakah user memiliki salah satu dari permissions yang diberikan
     */
    public function hasAnyPermission(array $permissionSlugs)
    {
        if ($this->isAdmin()) {
            return true;
        }

        $hasPermission = $this->roles()->whereHas('permissions', function ($query) use ($permissionSlugs) {
            $query->whereIn('slug', $permissionSlugs);
        })->exists();

        if ($hasPermission) {
            return true;
        }
        return false;
    }

    /**
     * Check apakah user memiliki permission group atau salah satu permission granular
     * Helper untuk check menu dengan backward compatibility
     * 
     * @param string $groupPermission Permission group (contoh: 'view-absensi')
     * @param array $granularPermissions Array permission granular (contoh: ['view-izin-keluar', 'view-tidak-masuk'])
     * @return bool
     */
    public function hasMenuPermission($groupPermission, array $granularPermissions = [])
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Check permission group dulu (backward compatible)
        if ($this->hasPermission($groupPermission)) {
            return true;
        }

        // Jika tidak punya group, check permission granular
        if (!empty($granularPermissions)) {
            return $this->hasAnyPermission($granularPermissions);
        }

        return false;
    }

    /**
     * Check apakah user adalah admin (backward compatibility)
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check apakah user adalah HR (backward compatibility)
     */
    public function isHR()
    {
        return $this->hasRole('hr');
    }

    /**
     * Check apakah user adalah manager (backward compatibility)
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    /**
     * Get dashboard route berdasarkan default_dashboard user atau permission
     * 
     * @return string Route name untuk dashboard
     */
    public function getDashboardRoute()
    {
        // Jika user punya default_dashboard yang di-set, gunakan itu
        if ($this->default_dashboard) {
            // Validasi apakah user punya permission untuk dashboard tersebut
            switch ($this->default_dashboard) {
                case 'group':
                    if ($this->hasPermission('view-dashboard-group')) {
                        return 'dashboard.group';
                    }
                    break;
                case 'bu':
                    if ($this->hasPermission('view-dashboard-bu')) {
                        return 'dashboard.bu';
                    }
                    break;
                case 'employee':
                    if ($this->hasPermission('view-dashboard-employee')) {
                        return 'dashboard.employee';
                    }
                    break;
                case 'default':
                default:
                    return 'dashboard';
            }
        }

        // Fallback: Cek permission untuk menentukan dashboard
        // Prioritas: group > bu > employee > default
        if ($this->hasPermission('view-dashboard-group')) {
            return 'dashboard.group';
        }
        
        if ($this->hasPermission('view-dashboard-bu')) {
            return 'dashboard.bu';
        }
        
        if ($this->hasPermission('view-dashboard-employee')) {
            return 'dashboard.employee';
        }

        // Default: dashboard biasa
        return 'dashboard';
    }
}
