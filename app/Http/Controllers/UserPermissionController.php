<?php
// app/Http/Controllers/UserPermissionController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_permissions');
    }

    public function edit(User $user)
    {
        $allPermissions = $this->getAllPermissions();
        $userPermissions = $user->assigned_permissions;

        return view('users.permissions', compact('user', 'allPermissions', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', $this->getAllPermissionKeys()),
        ]);

        $user->update([
            'permissions' => $request->permissions ?: [],
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User permissions updated successfully!');
    }

    private function getAllPermissions()
    {
        $permissions = [];
        $rolePermissions = config('permissions.roles', []);
        
        foreach ($rolePermissions as $role => $perms) {
            foreach ($perms as $permission) {
                $permissions[$permission] = $this->getPermissionLabel($permission);
            }
        }
        
        ksort($permissions);
        return $permissions;
    }

    private function getAllPermissionKeys()
    {
        $keys = [];
        $rolePermissions = config('permissions.roles', []);
        
        foreach ($rolePermissions as $perms) {
            $keys = array_merge($keys, $perms);
        }
        
        return array_unique($keys);
    }

    private function getPermissionLabel($permission)
    {
        $parts = explode('_', $permission);
        $action = $parts[0];
        $module = implode('_', array_slice($parts, 1));
        
        $actionLabel = config("permissions.actions.{$action}", ucfirst($action));
        $moduleLabel = config("permissions.modules.{$module}", ucfirst(str_replace('_', ' ', $module)));
        
        return "{$actionLabel} {$moduleLabel}";
    }
}