<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Verify that the currently authenticated user is an admin.
     * Called at the top of every action method instead of constructor middleware
     * (Laravel 12 removed $this->middleware() from controllers).
     */
    /**
     * Verify that the currently authenticated user is an admin or manager.
     */
    private function requireManager(): void
    {
        if (!Auth::user()?->isManager()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    /**
     * Check if the authenticated user has access to manage the given user.
     */
    private function ensureUserAccess(User $user): void
    {
        $authUser = Auth::user();
        if (!$authUser->isAdmin()) {
            $authUserUnitIds = $authUser->units()->pluck('units.id')->toArray();
            $targetUserUnitIds = $user->units()->pluck('units.id')->toArray();
            if (empty(array_intersect($authUserUnitIds, $targetUserUnitIds))) {
                abort(403, 'Anda tidak memiliki akses untuk mengelola pengguna ini.');
            }
        }
    }

    public function index(Request $request)
    {
        $this->requireManager();

        $query = User::query();

        if (!Auth::user()->isAdmin()) {
            $unitIds = Auth::user()->units()->pluck('units.id');
            $query->whereHas('units', function ($q) use ($unitIds) {
                $q->whereIn('units.id', $unitIds);
            });
        }

        if ($s = $request->search) {
            $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%")
                  ->orWhere('department', 'like', "%{$s}%");
            });
        }

        if ($role = $request->role) {
            $query->where('role', $role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->requireManager();
        $unitsQuery = Unit::orderBy('name');
        if (!Auth::user()->isAdmin()) {
            $unitsQuery->whereIn('id', Auth::user()->units()->pluck('units.id'));
        }
        $units = $unitsQuery->get();
        return view('users.create', compact('units'));
    }

    public function store(Request $request)
    {
        $this->requireManager();

        $request->validate([
            'name'       => 'required|string|max:150',
            'nip'        => 'nullable|string|max:30|unique:users',
            'email'      => 'required|email|max:150|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'department' => 'nullable|string|max:100',
            'position'   => 'nullable|string|max:100',
            'role'       => 'required|in:admin,manager,staff',
            'is_active'  => 'boolean',
            'units'      => 'required|array|min:1|max:3',
            'units.*'    => 'exists:units,id',
        ]);

        $requestedUnits = $request->input('units', []);
        if (!Auth::user()->isAdmin()) {
            $allowedUnitIds = Auth::user()->units()->pluck('units.id')->toArray();
            foreach ($requestedUnits as $unitId) {
                if (!in_array($unitId, $allowedUnitIds)) {
                    abort(403, 'Anda tidak dapat menetapkan unit yang bukan merupakan wewenang Anda.');
                }
            }
        }

        $validated = $request->only(['name','nip','email','department','position','role','is_active']);
        $validated['password']  = Hash::make($request->password);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);
        $user->units()->sync($requestedUnits);

        return redirect()->route('administrasi.users.show', $user)
            ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        $this->requireManager();
        $this->ensureUserAccess($user);

        $user->load('createdMails', 'assignedMails', 'receivedDispositions', 'sentDispositions');
        $stats = [
            'created_mails'  => $user->createdMails()->count(),
            'assigned_mails' => $user->assignedMails()->count(),
            'received_disp'  => $user->receivedDispositions()->count(),
            'pending_disp'   => $user->receivedDispositions()->where('status', 'pending')->count(),
        ];
        return view('users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        $this->requireManager();
        $this->ensureUserAccess($user);

        $unitsQuery = Unit::orderBy('name');
        if (!Auth::user()->isAdmin()) {
            $unitsQuery->whereIn('id', Auth::user()->units()->pluck('units.id'));
        }
        $units = $unitsQuery->get();
        $user->load('units');
        return view('users.edit', compact('user', 'units'));
    }

    public function update(Request $request, User $user)
    {
        $this->requireManager();
        $this->ensureUserAccess($user);

        $request->validate([
            'name'       => 'required|string|max:150',
            'nip'        => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'email'      => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password'   => 'nullable|string|min:8|confirmed',
            'department' => 'nullable|string|max:100',
            'position'   => 'nullable|string|max:100',
            'role'       => 'required|in:admin,manager,staff',
            'is_active'  => 'boolean',
            'units'      => 'required|array|min:1|max:3',
            'units.*'    => 'exists:units,id',
        ]);

        $requestedUnits = $request->input('units', []);
        if (!Auth::user()->isAdmin()) {
            $allowedUnitIds = Auth::user()->units()->pluck('units.id')->toArray();
            foreach ($requestedUnits as $unitId) {
                if (!in_array($unitId, $allowedUnitIds)) {
                    abort(403, 'Anda tidak dapat menetapkan unit yang bukan merupakan wewenang Anda.');
                }
            }
        }

        $data = $request->only(['name','nip','email','department','position','role','is_active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);
        
        // Only sync units the manager is allowed to manage, leave other units intact if any (optional).
        // For strictness based on the prompt, we just sync the requested units.
        // If a user had units outside the manager's scope, they will be removed if we just sync,
        // but ensureUserAccess guarantees they share at least one unit.
        // Best approach is to sync all requested units, assuming they fully define the user's new units.
        if (!Auth::user()->isAdmin()) {
            $existingOtherUnits = $user->units()->whereNotIn('units.id', $allowedUnitIds)->pluck('units.id')->toArray();
            $user->units()->sync(array_merge($requestedUnits, $existingOtherUnits));
        } else {
            $user->units()->sync($requestedUnits);
        }

        return redirect()->route('administrasi.users.show', $user)
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->requireManager();
        $this->ensureUserAccess($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        $user->delete();
        return redirect()->route('administrasi.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $this->requireManager();
        $this->ensureUserAccess($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pengguna {$user->name} berhasil {$status}.");
    }
}
