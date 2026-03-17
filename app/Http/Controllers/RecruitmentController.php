<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Recruitment;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $recruitments = Recruitment::where('company_id', $companyId)
            ->with(['department', 'designation'])
            ->latest()
            ->paginate(25);

        return view('recruitment.index', compact('recruitments'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();
        $designations = Designation::where('company_id', $companyId)->where('is_active', true)->get();

        return view('recruitment.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vacancy_title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'positions' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'status' => 'nullable|in:OPEN,CLOSED,ON_HOLD',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:posted_date',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = $validated['status'] ?? 'OPEN';

        Recruitment::create($validated);

        return redirect()->route('recruitment.index')->with('success', 'Recruitment created successfully.');
    }

    public function show(Recruitment $recruitment)
    {
        $this->authorizeCompany($recruitment);

        $recruitment->load(['department', 'designation', 'candidates']);

        return view('recruitment.show', compact('recruitment'));
    }

    public function edit(Recruitment $recruitment)
    {
        $this->authorizeCompany($recruitment);

        $companyId = auth()->user()->company_id;

        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();
        $designations = Designation::where('company_id', $companyId)->where('is_active', true)->get();

        return view('recruitment.edit', compact('recruitment', 'departments', 'designations'));
    }

    public function update(Request $request, Recruitment $recruitment)
    {
        $this->authorizeCompany($recruitment);

        $validated = $request->validate([
            'vacancy_title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'positions' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'status' => 'required|in:OPEN,CLOSED,ON_HOLD',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:posted_date',
        ]);

        $recruitment->update($validated);

        return redirect()->route('recruitment.index')->with('success', 'Recruitment updated successfully.');
    }

    public function destroy(Recruitment $recruitment)
    {
        $this->authorizeCompany($recruitment);

        $recruitment->delete();

        return redirect()->route('recruitment.index')->with('success', 'Recruitment deleted successfully.');
    }

    protected function authorizeCompany(Recruitment $recruitment): void
    {
        if ($recruitment->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
