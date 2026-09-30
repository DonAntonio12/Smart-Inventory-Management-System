<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->with(['users' => function ($query) {
                $query->take(5);
            }])
            ->orderBy('name')
            ->paginate(12);

        return view('roles.index', [
            'roles' => $roles,
            'totalRoles' => Role::query()->count(),
            'totalAssignedUsers' => \App\Models\User::has('roles')->count(),
            'unassignedUsers' => \App\Models\User::doesntHave('roles')->count(),
        ]);
    }

    public function create(): View
    {
        return view('roles.form', [
            'role' => new Role,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Role::create([
            'name' => trim($validated['name']),
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('roles.index')->with('status', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('roles.form', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $role->update([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('roles.index')->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array(strtolower($role->name), ['admin', 'administrator'], true)) {
            return back()->withErrors([
                'role' => 'The Admin role is a protected system role and cannot be deleted.',
            ]);
        }

        if ($role->users()->exists()) {
            return back()->withErrors([
                'role' => 'This role is currently assigned to '.$role->users()->count().' user(s) and cannot be deleted.',
            ]);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role removed successfully.');
    }
}
