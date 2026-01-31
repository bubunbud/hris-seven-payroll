<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait Loggable
{
    /**
     * Boot the Loggable trait
     */
    public static function bootLoggable()
    {
        static::created(function ($model) {
            ActivityLogService::logCreate($model);
        });

        static::updated(function ($model) {
            ActivityLogService::logUpdate($model);
        });

        static::deleted(function ($model) {
            ActivityLogService::logDelete($model);
        });
    }

    /**
     * Get identifier for logging
     * Override di model untuk custom identifier
     */
    public function getLogIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get description for logging
     * Override di model untuk custom description
     */
    public function getLogDescription()
    {
        $identifier = $this->getLogIdentifier();
        $modelName = class_basename($this);
        
        // Coba ambil field yang umum untuk description
        if (isset($this->Nama)) {
            return "{$modelName}: {$this->Nama}";
        } elseif (isset($this->name)) {
            return "{$modelName}: {$this->name}";
        } elseif (isset($this->Nik)) {
            return "{$modelName} NIK: {$this->Nik}";
        }
        
        return "{$modelName} #{$identifier}";
    }

    /**
     * Get loggable attributes (exclude sensitive fields)
     */
    public function getLoggableAttributes()
    {
        $attributes = $this->getAttributes();
        
        // Exclude sensitive fields
        $excluded = ['password', 'remember_token', 'api_token', 'token', 'secret'];
        
        return array_diff_key($attributes, array_flip($excluded));
    }
}




namespace App\Traits;

use App\Services\ActivityLogService;

trait Loggable
{
    /**
     * Boot the Loggable trait
     */
    public static function bootLoggable()
    {
        static::created(function ($model) {
            ActivityLogService::logCreate($model);
        });

        static::updated(function ($model) {
            ActivityLogService::logUpdate($model);
        });

        static::deleted(function ($model) {
            ActivityLogService::logDelete($model);
        });
    }

    /**
     * Get identifier for logging
     * Override di model untuk custom identifier
     */
    public function getLogIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get description for logging
     * Override di model untuk custom description
     */
    public function getLogDescription()
    {
        $identifier = $this->getLogIdentifier();
        $modelName = class_basename($this);
        
        // Coba ambil field yang umum untuk description
        if (isset($this->Nama)) {
            return "{$modelName}: {$this->Nama}";
        } elseif (isset($this->name)) {
            return "{$modelName}: {$this->name}";
        } elseif (isset($this->Nik)) {
            return "{$modelName} NIK: {$this->Nik}";
        }
        
        return "{$modelName} #{$identifier}";
    }

    /**
     * Get loggable attributes (exclude sensitive fields)
     */
    public function getLoggableAttributes()
    {
        $attributes = $this->getAttributes();
        
        // Exclude sensitive fields
        $excluded = ['password', 'remember_token', 'api_token', 'token', 'secret'];
        
        return array_diff_key($attributes, array_flip($excluded));
    }
}


