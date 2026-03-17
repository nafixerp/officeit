<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\HrLetter;
use App\Models\Recruitment;
use Illuminate\Http\Request;

class HrLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $letters = HrLetter::where(function ($q) use ($companyId) {
                $q->whereHas('employee', fn ($q) => $q->where('company_id', $companyId))
                  ->orWhereHas('candidate.recruitment', fn ($q) => $q->where('company_id', $companyId));
            })
            ->with(['employee', 'candidate'])
            ->latest()
            ->paginate(25);

        return view('hr-letters.index', compact('letters'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->get();
        $candidates = Candidate::whereHas('recruitment', fn ($q) => $q->where('company_id', $companyId))->get();

        $letterTypes = [
            'OFFER', 'APPOINTMENT', 'CONFIRMATION', 'INCREMENT',
            'PROMOTION', 'WARNING', 'EXPERIENCE', 'RELIEVING',
        ];

        return view('hr-letters.create', compact('employees', 'candidates', 'letterTypes'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'candidate_id' => 'nullable|exists:candidates,id',
            'letter_type' => 'required|in:OFFER,APPOINTMENT,CONFIRMATION,INCREMENT,PROMOTION,WARNING,EXPERIENCE,RELIEVING',
            'content' => 'nullable|string',
            'generated_date' => 'required|date',
            'status' => 'nullable|in:DRAFT,SENT,ACCEPTED,DECLINED',
        ]);

        // Verify employee belongs to company if provided
        if (!empty($validated['employee_id'])) {
            Employee::where('id', $validated['employee_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Generate letter content from template if not provided
        if (empty($validated['content'])) {
            $validated['content'] = $this->generateLetterContent(
                $validated['letter_type'],
                $validated['employee_id'] ?? null,
                $validated['candidate_id'] ?? null
            );
        }

        $validated['status'] = $validated['status'] ?? 'DRAFT';
        $validated['created_by'] = auth()->id();

        HrLetter::create($validated);

        return redirect()->route('hr-letters.index')->with('success', 'HR letter created successfully.');
    }

    public function show(HrLetter $hrLetter)
    {
        $this->authorizeCompany($hrLetter);

        $hrLetter->load(['employee', 'candidate']);

        return view('hr-letters.show', compact('hrLetter'));
    }

    public function edit(HrLetter $hrLetter)
    {
        $this->authorizeCompany($hrLetter);

        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->get();
        $candidates = Candidate::whereHas('recruitment', fn ($q) => $q->where('company_id', $companyId))->get();

        $letterTypes = [
            'OFFER', 'APPOINTMENT', 'CONFIRMATION', 'INCREMENT',
            'PROMOTION', 'WARNING', 'EXPERIENCE', 'RELIEVING',
        ];

        return view('hr-letters.edit', compact('hrLetter', 'employees', 'candidates', 'letterTypes'));
    }

    public function update(Request $request, HrLetter $hrLetter)
    {
        $this->authorizeCompany($hrLetter);

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'candidate_id' => 'nullable|exists:candidates,id',
            'letter_type' => 'required|in:OFFER,APPOINTMENT,CONFIRMATION,INCREMENT,PROMOTION,WARNING,EXPERIENCE,RELIEVING',
            'content' => 'nullable|string',
            'generated_date' => 'required|date',
            'status' => 'required|in:DRAFT,SENT,ACCEPTED,DECLINED',
        ]);

        $hrLetter->update($validated);

        return redirect()->route('hr-letters.index')->with('success', 'HR letter updated successfully.');
    }

    public function destroy(HrLetter $hrLetter)
    {
        $this->authorizeCompany($hrLetter);

        $hrLetter->delete();

        return redirect()->route('hr-letters.index')->with('success', 'HR letter deleted successfully.');
    }

    public function print(HrLetter $hrLetter)
    {
        $this->authorizeCompany($hrLetter);

        $hrLetter->load(['employee.department', 'employee.designation', 'candidate']);

        return view('hr-letters.print', compact('hrLetter'));
    }

    protected function generateLetterContent(string $letterType, ?int $employeeId, ?int $candidateId): string
    {
        $recipientName = 'N/A';
        $designation = '';
        $department = '';
        $joiningDate = '';
        $salary = '';

        if ($employeeId) {
            $employee = Employee::with(['designation', 'department'])->find($employeeId);
            if ($employee) {
                $recipientName = $employee->full_name;
                $designation = $employee->designation->name ?? '';
                $department = $employee->department->name ?? '';
                $joiningDate = $employee->joining_date?->format('d M Y') ?? '';
                $salary = number_format($employee->basic_salary, 2);
            }
        } elseif ($candidateId) {
            $candidate = Candidate::find($candidateId);
            if ($candidate) {
                $recipientName = $candidate->name;
                $salary = $candidate->offer_salary ? number_format($candidate->offer_salary, 2) : '';
                $joiningDate = $candidate->joining_date?->format('d M Y') ?? '';
            }
        }

        $companyName = auth()->user()->company->name ?? 'The Company';
        $date = now()->format('d M Y');

        return match ($letterType) {
            'OFFER' => "Date: {$date}\n\nDear {$recipientName},\n\nWe are pleased to offer you the position of {$designation} in the {$department} department at {$companyName}.\n\nYour proposed start date is {$joiningDate} with a monthly salary of {$salary}.\n\nPlease confirm your acceptance within 7 days.\n\nRegards,\n{$companyName}",
            'APPOINTMENT' => "Date: {$date}\n\nDear {$recipientName},\n\nWith reference to your application and subsequent interview, we are pleased to appoint you as {$designation} in the {$department} department effective {$joiningDate}.\n\nYour basic monthly salary will be {$salary}.\n\nWe look forward to your contributions.\n\nRegards,\n{$companyName}",
            'CONFIRMATION' => "Date: {$date}\n\nDear {$recipientName},\n\nWe are pleased to confirm your appointment as a permanent employee of {$companyName} effective from today.\n\nYour designation is {$designation} in the {$department} department.\n\nCongratulations and best wishes.\n\nRegards,\n{$companyName}",
            'INCREMENT' => "Date: {$date}\n\nDear {$recipientName},\n\nWe are pleased to inform you that your salary has been revised. Your new basic salary will be {$salary} per month effective from today.\n\nWe appreciate your dedication and hard work.\n\nRegards,\n{$companyName}",
            'PROMOTION' => "Date: {$date}\n\nDear {$recipientName},\n\nWe are pleased to inform you of your promotion to the position of {$designation} in the {$department} department effective from today.\n\nYour revised salary will be {$salary} per month.\n\nCongratulations!\n\nRegards,\n{$companyName}",
            'WARNING' => "Date: {$date}\n\nDear {$recipientName},\n\nThis letter serves as a formal warning regarding [reason for warning].\n\nPlease take immediate corrective action. Failure to improve may result in further disciplinary action.\n\nRegards,\n{$companyName}",
            'EXPERIENCE' => "Date: {$date}\n\nTo Whom It May Concern,\n\nThis is to certify that {$recipientName} was employed with {$companyName} as {$designation} in the {$department} department from {$joiningDate} to {$date}.\n\nDuring their tenure, they performed their duties satisfactorily.\n\nWe wish them all the best in their future endeavors.\n\nRegards,\n{$companyName}",
            'RELIEVING' => "Date: {$date}\n\nDear {$recipientName},\n\nThis is to confirm that you have been relieved from your duties as {$designation} in the {$department} department at {$companyName} effective {$date}.\n\nAll dues have been settled as per company policy.\n\nWe wish you the best.\n\nRegards,\n{$companyName}",
            default => "Date: {$date}\n\nDear {$recipientName},\n\n[Letter content]\n\nRegards,\n{$companyName}",
        };
    }

    protected function authorizeCompany(HrLetter $letter): void
    {
        $letter->loadMissing(['employee', 'candidate.recruitment']);

        $authorized = false;

        if ($letter->employee && $letter->employee->company_id === auth()->user()->company_id) {
            $authorized = true;
        }

        if ($letter->candidate && $letter->candidate->recruitment
            && $letter->candidate->recruitment->company_id === auth()->user()->company_id) {
            $authorized = true;
        }

        if (!$authorized) {
            abort(403, 'Unauthorized access.');
        }
    }
}
