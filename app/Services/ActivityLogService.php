<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Fields yang tidak perlu di-log (sensitive data)
     */
    protected static $excludedFields = [
        'password',
        'remember_token',
        'api_token',
        'token',
        'secret',
        'password_confirmation',
    ];

    /**
     * Log create action
     */
    public static function logCreate($model, $description = null)
    {
        $log = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? null,
            'user_email' => Auth::user()->email ?? null,
            'action' => 'create',
            'model' => class_basename($model),
            'model_id' => self::getModelIdentifier($model),
            'description' => $description ?? self::getModelDescription($model),
            'new_values' => self::getLoggableAttributes(
                method_exists($model, 'getLoggableAttributes') 
                    ? $model->getLoggableAttributes() 
                    : $model->getAttributes()
            ),
            'module' => self::detectModule($model),
            'submodule' => self::detectSubmodule($model),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'route' => Request::route()?->getName(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
        ];

        ActivityLog::create($log);
    }

    /**
     * Log update action
     * 
     * @param mixed $model Model yang di-update
     * @param string|null $description Deskripsi custom (opsional)
     * @param array|null $oldValues Data lama (jika tidak disediakan, akan coba ambil dari getOriginal)
     */
    public static function logUpdate($model, $description = null, $oldValues = null)
    {
        // Jika oldValues tidak disediakan, coba ambil dari getOriginal
        // Tapi biasanya getOriginal() sudah tidak valid setelah update, jadi lebih baik pass dari controller
        if ($oldValues === null) {
            // Coba ambil dari getOriginal (mungkin masih ada jika dipanggil sebelum update)
            $oldValues = $model->getOriginal();
            
            // Jika getOriginal() kosong atau sama dengan attributes, berarti sudah ter-update
            // Dalam kasus ini, kita tidak bisa mendapatkan old values yang benar
            if (empty($oldValues) || $oldValues === $model->getAttributes()) {
                // Fallback: gunakan attributes saat ini sebagai old values (tidak ideal tapi lebih aman)
                $oldValues = $model->getAttributes();
            }
        }
        
        // Refresh model untuk mendapatkan data terbaru dari database setelah update
        // Ini memastikan new values adalah data yang benar-benar tersimpan di database
        $model->refresh();
        $newValues = $model->getAttributes();
        
        // Filter hanya field yang berubah
        $changedFields = [];
        foreach ($newValues as $key => $value) {
            if (!in_array($key, self::$excludedFields) && 
                (!isset($oldValues[$key]) || $oldValues[$key] != $value)) {
                $changedFields[] = $key;
            }
        }

        // Jika tidak ada perubahan, skip logging
        if (empty($changedFields)) {
            return;
        }

        $log = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? null,
            'user_email' => Auth::user()->email ?? null,
            'action' => 'update',
            'model' => class_basename($model),
            'model_id' => self::getModelIdentifier($model),
            'description' => $description ?? self::getModelDescription($model),
            'old_values' => self::getLoggableAttributes($oldValues),
            'new_values' => self::getLoggableAttributes($newValues),
            'changed_fields' => implode(', ', $changedFields),
            'module' => self::detectModule($model),
            'submodule' => self::detectSubmodule($model),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'route' => Request::route()?->getName(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
        ];

        ActivityLog::create($log);
    }

    /**
     * Log delete action
     */
    public static function logDelete($model, $description = null)
    {
        $log = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? null,
            'user_email' => Auth::user()->email ?? null,
            'action' => 'delete',
            'model' => class_basename($model),
            'model_id' => self::getModelIdentifier($model),
            'description' => $description ?? self::getModelDescription($model),
            'old_values' => self::getLoggableAttributes(
                method_exists($model, 'getLoggableAttributes') 
                    ? $model->getLoggableAttributes() 
                    : $model->getAttributes()
            ),
            'module' => self::detectModule($model),
            'submodule' => self::detectSubmodule($model),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'route' => Request::route()?->getName(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
        ];

        ActivityLog::create($log);
    }

    /**
     * Log custom action
     */
    public static function log($action, $description, $data = [])
    {
        $log = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? null,
            'user_email' => Auth::user()->email ?? null,
            'action' => $action,
            'model' => $data['model'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'description' => $description,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'changed_fields' => $data['changed_fields'] ?? null,
            'module' => $data['module'] ?? null,
            'submodule' => $data['submodule'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'route' => Request::route()?->getName(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
        ];

        ActivityLog::create($log);
    }

    /**
     * Get loggable attributes (exclude sensitive fields)
     */
    protected static function getLoggableAttributes($attributes)
    {
        if (!is_array($attributes)) {
            return null;
        }

        return array_diff_key($attributes, array_flip(self::$excludedFields));
    }

    /**
     * Detect module dari model
     */
    protected static function detectModule($model)
    {
        $modelName = class_basename($model);
        
        $moduleMap = [
            'Karyawan' => 'master-data',
            'Divisi' => 'master-data',
            'Departemen' => 'master-data',
            'Bagian' => 'master-data',
            'Seksi' => 'master-data',
            'Golongan' => 'master-data',
            'Shift' => 'master-data',
            'Jabatan' => 'master-data',
            'HariLibur' => 'master-data',
            'Absen' => 'absensi',
            'TidakMasuk' => 'absensi',
            'Izin' => 'absensi',
            'SaldoCuti' => 'absensi',
            'JadwalShiftSecurity' => 'absensi',
            'Closing' => 'proses-gaji',
            'Gapok' => 'proses-gaji',
            'PeriodeGaji' => 'proses-gaji',
            'HutangPiutang' => 'proses-gaji',
            'User' => 'settings',
            'Role' => 'settings',
            'Permission' => 'settings',
        ];

        return $moduleMap[$modelName] ?? null;
    }

    /**
     * Detect submodule dari model
     */
    protected static function detectSubmodule($model)
    {
        $modelName = class_basename($model);
        
        // Convert PascalCase ke kebab-case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $modelName));
    }

    /**
     * Get model identifier (with fallback)
     */
    protected static function getModelIdentifier($model)
    {
        if (method_exists($model, 'getLogIdentifier')) {
            return $model->getLogIdentifier();
        }
        
        // Fallback: gunakan primary key
        $key = $model->getKey();
        
        // Jika model punya field Nik, gunakan Nik sebagai identifier
        if (isset($model->Nik)) {
            return $model->Nik;
        }
        
        return $key;
    }

    /**
     * Get model description (with fallback)
     */
    protected static function getModelDescription($model)
    {
        if (method_exists($model, 'getLogDescription')) {
            return $model->getLogDescription();
        }
        
        // Fallback: generate description dari model
        $modelName = class_basename($model);
        $identifier = self::getModelIdentifier($model);
        
        // Coba ambil field yang umum untuk description
        if (isset($model->Nama)) {
            return "{$modelName}: {$model->Nama}";
        } elseif (isset($model->name)) {
            return "{$modelName}: {$model->name}";
        } elseif (isset($model->Nik)) {
            return "{$modelName} NIK: {$model->Nik}";
        }
        
        return "{$modelName} #{$identifier}";
    }
}

