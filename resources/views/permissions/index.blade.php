@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Permissions Matrix</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('permissions.store') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Module</th>
                            @php
                                $roles = ['SUPER_ADMIN', 'ADMIN', 'ACCOUNTS_MANAGER', 'ACCOUNTANT', 'SALES_MANAGER', 'SALES_EXECUTIVE', 'PURCHASE_MANAGER', 'PURCHASE_OFFICER', 'HR_MANAGER', 'PAYROLL_OFFICER', 'BRANCH_MANAGER', 'AUDITOR', 'USER'];
                            @endphp
                            @foreach($roles as $role)
                                <th class="text-center" style="font-size: 0.7rem; min-width: 90px;">{{ str_replace('_', ' ', $role) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $modules = $modules ?? ['Sales', 'Purchase', 'Finance', 'Inventory', 'HR', 'Payroll', 'Reports', 'Settings', 'Masters'];
                        @endphp
                        @foreach($modules as $module)
                            <tr>
                                <td class="fw-semibold">{{ $module }}</td>
                                @foreach($roles as $role)
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[{{ $module }}][{{ $role }}]"
                                               value="1"
                                               {{ isset($permissions[$module][$role]) && $permissions[$module][$role] ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Permissions</button>
        </form>
    </div>
</div>
@endsection
