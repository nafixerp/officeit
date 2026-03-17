<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $payrolls = Payroll::where('company_id', $companyId)
            ->when($request->month, fn ($q, $v) => $q->where('month', $v))
            ->when($request->year, fn ($q, $v) => $q->where('year', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with('employee')
            ->latest()
            ->paginate(25);

        return view('payroll.index', compact('payrolls'));
    }

    public function create()
    {
        return view('payroll.create');
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2099',
        ]);

        $employee = Employee::where('id', $validated['employee_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $payroll = $this->generateEmployeePayroll($employee, $validated['month'], $validated['year']);

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll generated successfully.');
    }

    public function show(Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        $payroll->load(['employee', 'items']);

        return view('payroll.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        $payroll->load(['employee', 'items']);

        return view('payroll.edit', compact('payroll'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        $validated = $request->validate([
            'bonus' => 'nullable|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'leave_deduction' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $payroll->update($validated);

        // Recalculate net salary
        $netSalary = $payroll->basic_salary
            + $payroll->total_earnings
            + ($payroll->overtime_amount ?? 0)
            + ($payroll->bonus ?? 0)
            - $payroll->total_deductions
            - ($payroll->loan_deduction ?? 0)
            - ($payroll->leave_deduction ?? 0);

        $payroll->update(['net_salary' => $netSalary]);

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll updated successfully.');
    }

    public function destroy(Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        if ($payroll->status !== 'DRAFT') {
            return back()->with('error', 'Only draft payroll records can be deleted.');
        }

        $payroll->items()->delete();
        $payroll->delete();

        return redirect()->route('payroll.index')->with('success', 'Payroll deleted successfully.');
    }

    public function generate(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2099',
        ]);

        $month = $validated['month'];
        $year = $validated['year'];

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->get();

        $generated = 0;

        DB::transaction(function () use ($employees, $month, $year, &$generated) {
            foreach ($employees as $employee) {
                // Skip if payroll already exists for this employee/month/year
                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->exists();

                if (!$exists) {
                    $this->generateEmployeePayroll($employee, $month, $year);
                    $generated++;
                }
            }
        });

        return redirect()->route('payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "{$generated} payroll records generated.");
    }

    public function approve(Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        if ($payroll->status !== 'DRAFT') {
            return back()->with('error', 'Only draft payroll can be approved.');
        }

        DB::transaction(function () use ($payroll) {
            $payroll->update(['status' => 'APPROVED']);

            $companyId = auth()->user()->company_id;

            // Find salary expense and salary payable accounts
            $salaryExpenseAccount = ChartOfAccount::where('company_id', $companyId)
                ->where('type', 'EXPENSE')
                ->where(function ($q) {
                    $q->where('name', 'like', '%Salary%')
                      ->orWhere('name', 'like', '%salary%')
                      ->orWhere('sub_type', 'salary_expense');
                })
                ->first();

            $salaryPayableAccount = ChartOfAccount::where('company_id', $companyId)
                ->where('type', 'LIABILITY')
                ->where(function ($q) {
                    $q->where('name', 'like', '%Salary Payable%')
                      ->orWhere('name', 'like', '%salary payable%')
                      ->orWhere('sub_type', 'salary_payable');
                })
                ->first();

            if ($salaryExpenseAccount && $salaryPayableAccount) {
                $journalNumber = 'JV-PAY-' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '-' . $payroll->id;

                $journalEntry = JournalEntry::create([
                    'company_id' => $companyId,
                    'journal_number' => $journalNumber,
                    'date' => now()->toDateString(),
                    'total_amount' => $payroll->net_salary,
                    'narration' => "Payroll for {$payroll->employee->full_name} - {$payroll->month}/{$payroll->year}",
                    'type' => 'MANUAL',
                    'status' => 'APPROVED',
                    'is_auto_generated' => true,
                    'source_type' => 'Payroll',
                    'source_id' => $payroll->id,
                    'created_by' => auth()->id(),
                ]);

                // Debit: Salary Expense
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $salaryExpenseAccount->id,
                    'debit_amount' => $payroll->net_salary,
                    'credit_amount' => 0,
                    'narration' => "Salary expense - {$payroll->employee->full_name}",
                ]);

                // Credit: Salary Payable
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $salaryPayableAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $payroll->net_salary,
                    'narration' => "Salary payable - {$payroll->employee->full_name}",
                ]);
            }
        });

        return back()->with('success', 'Payroll approved and journal entry created.');
    }

    public function payslip(Payroll $payroll)
    {
        $this->authorizeCompany($payroll);

        $payroll->load(['employee.department', 'employee.designation', 'employee.branch', 'items']);

        return view('payroll.payslip', compact('payroll'));
    }

    protected function generateEmployeePayroll(Employee $employee, int $month, int $year): Payroll
    {
        $companyId = auth()->user()->company_id;

        // Get salary structure
        $salaryComponents = SalaryStructure::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();

        $totalEarnings = $salaryComponents->where('type', 'EARNING')->sum('amount');
        $totalDeductions = $salaryComponents->where('type', 'DEDUCTION')->sum('amount');

        // Calculate overtime from attendance
        $overtimeHours = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('overtime_hours');

        // Assume overtime rate is basic_salary / 30 / 8 * 1.5
        $overtimeRate = $employee->basic_salary > 0
            ? ($employee->basic_salary / 30 / 8) * 1.5
            : 0;
        $overtimeAmount = round($overtimeHours * $overtimeRate, 2);

        // Get active loan deductions
        $loanDeduction = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'ACTIVE')
            ->where('balance', '>', 0)
            ->sum('monthly_deduction');

        $netSalary = $employee->basic_salary
            + $totalEarnings
            + $overtimeAmount
            - $totalDeductions
            - $loanDeduction;

        $payroll = Payroll::create([
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'basic_salary' => $employee->basic_salary,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'overtime_amount' => $overtimeAmount,
            'bonus' => 0,
            'loan_deduction' => $loanDeduction,
            'leave_deduction' => 0,
            'net_salary' => $netSalary,
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
        ]);

        // Create payroll items from salary structure
        foreach ($salaryComponents as $component) {
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component' => $component->component,
                'type' => $component->type,
                'amount' => $component->amount,
            ]);
        }

        // Add overtime as an earning item if applicable
        if ($overtimeAmount > 0) {
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component' => 'Overtime',
                'type' => 'EARNING',
                'amount' => $overtimeAmount,
            ]);
        }

        // Add loan deduction item if applicable
        if ($loanDeduction > 0) {
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component' => 'Loan Deduction',
                'type' => 'DEDUCTION',
                'amount' => $loanDeduction,
            ]);

            // Update loan balances
            $activeLoans = EmployeeLoan::where('employee_id', $employee->id)
                ->where('status', 'ACTIVE')
                ->where('balance', '>', 0)
                ->get();

            foreach ($activeLoans as $loan) {
                $newBalance = max(0, $loan->balance - $loan->monthly_deduction);
                $loan->update([
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'COMPLETED' : 'ACTIVE',
                ]);
            }
        }

        return $payroll;
    }

    protected function authorizeCompany(Payroll $payroll): void
    {
        if ($payroll->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
