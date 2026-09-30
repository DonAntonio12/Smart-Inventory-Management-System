<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $roleId = $request->query('role');

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(filled($roleId), function (Builder $query) use ($roleId): void {
                if ($roleId === '__unassigned') {
                    $query->doesntHave('roles');

                    return;
                }
                $query->whereHas('roles', function (Builder $query) use ($roleId): void {
                    $query->where('roles.id', $roleId);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'selectedRole' => $roleId,
            'totalUsers' => User::query()->count(),
            'assignedUsers' => User::has('roles')->count(),
            'totalRoles' => Role::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('users.index')->with('status', 'User created and role assignments saved.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->rules($user));

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
        ]);

        if (filled($validated['password'] ?? null)) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('users.index')->with('status', 'User details updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User account removed.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}