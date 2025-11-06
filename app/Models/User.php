<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'pin_code',
        'role',
        'is_active',
        'password',
        'permissions',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function activeShift()
    {
        return $this->hasOne(Shift::class)->active();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Methods
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasAnyRole($roles)
    {
        return in_array($this->role, (array)$roles);
    }

    public function hasPermission($permission)
    {
        // Check if user has specific permission
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        // Fallback to role-based permissions
        return $this->hasRolePermission($permission);
    }

    public function hasRolePermission($permission)
    {
        $rolePermissions = config('permissions.roles.' . $this->role, []);
        return in_array($permission, $rolePermissions);
    }

    public function canAccessModule($module)
    {
        return $this->hasPermission("access_{$module}");
    }

    public function canPerformAction($module, $action)
    {
        return $this->hasPermission("{$action}_{$module}");
    }

    public function getAssignedPermissionsAttribute()
    {
        $rolePermissions = config('permissions.roles.' . $this->role, []);
        $customPermissions = $this->permissions ?: [];
        
        return array_unique(array_merge($rolePermissions, $customPermissions));
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isCashier()
    {
        return $this->role === 'cashier';
    }

    public function isAccountant()
    {
        return $this->role === 'accountant';
    }

    public function hasActiveShift()
    {
        return $this->activeShift()->exists();
    }

    public function getTodayShiftsAttribute()
    {
        return $this->shifts()->today()->get();
    }

    public function hasBranch()
    {
        return !is_null($this->branch_id);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'is_active', 'branch_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}");
    }
}