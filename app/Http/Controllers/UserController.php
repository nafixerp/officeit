<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function roles(): array
    {
        return [
            'SUPER_ADMIN',
            'ADMIN',
            'ACCOUNTS_MANAGER',
            'ACCOUNTANT',
            'SALES_MANAGER',
            'SALES_EXECUTIVE',
            'PURCHASE_MANAGER',
            'PURCHASE_OFFICER',
            'HR_MANAGER',
            'PAYROLL_OFFICER',
            'BRANCH_MANAGER',
            'AUDITOR',
            'USER',
        ];
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $users = User::where('company_id', $companyId)
            ->with(['branch', 'department'])
            ->latest()
            ->paginate(25);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $roles = $this->roles();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();

        return view('users.create', compact('roles', 'branches', 'departments'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . implode(',', $this->roles()),
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->authorizeCompany($user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorizeCompany($user);

        $companyId = auth()->user()->company_id;

        $roles = $this->roles();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();

        return view('users.edit', compact('user', 'roles', 'branches', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeCompany($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . implode(',', $this->roles()),
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeCompany($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    protected function authorizeCompany(User $user): void
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
