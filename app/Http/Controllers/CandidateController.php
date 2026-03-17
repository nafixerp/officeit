<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $candidates = Candidate::whereHas('recruitment', fn ($q) => $q->where('company_id', $companyId))
            ->with('recruitment')
            ->latest()
            ->paginate(25);

        return view('candidates.index', compact('candidates'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $recruitments = Recruitment::where('company_id', $companyId)
            ->where('status', 'OPEN')
            ->get();

        return view('candidates.create', compact('recruitments'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'recruitment_id' => 'nullable|exists:recruitment,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'cv' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
            'status' => 'nullable|in:APPLIED,SHORTLISTED,INTERVIEW,SELECTED,REJECTED,JOINED',
            'interview_date' => 'nullable|date',
            'evaluation_notes' => 'nullable|string',
            'offer_salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
        ]);

        // Verify recruitment belongs to company if provided
        if (!empty($validated['recruitment_id'])) {
            Recruitment::where('id', $validated['recruitment_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('candidates/cv', 'public');
        }

        Candidate::create([
            'recruitment_id' => $validated['recruitment_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'cv_path' => $cvPath,
            'status' => $validated['status'] ?? 'APPLIED',
            'interview_date' => $validated['interview_date'] ?? null,
            'evaluation_notes' => $validated['evaluation_notes'] ?? null,
            'offer_salary' => $validated['offer_salary'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
        ]);

        return redirect()->route('candidates.index')->with('success', 'Candidate created successfully.');
    }

    public function show(Candidate $candidate)
    {
        $this->authorizeCompany($candidate);

        $candidate->load('recruitment');

        return view('candidates.show', compact('candidate'));
    }

    public function edit(Candidate $candidate)
    {
        $this->authorizeCompany($candidate);

        $companyId = auth()->user()->company_id;

        $recruitments = Recruitment::where('company_id', $companyId)->get();

        return view('candidates.edit', compact('candidate', 'recruitments'));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $this->authorizeCompany($candidate);

        $validated = $request->validate([
            'recruitment_id' => 'nullable|exists:recruitment,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'cv' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
            'status' => 'required|in:APPLIED,SHORTLISTED,INTERVIEW,SELECTED,REJECTED,JOINED',
            'interview_date' => 'nullable|date',
            'evaluation_notes' => 'nullable|string',
            'offer_salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
        ]);

        if ($request->hasFile('cv')) {
            if ($candidate->cv_path) {
                Storage::disk('public')->delete($candidate->cv_path);
            }
            $validated['cv_path'] = $request->file('cv')->store('candidates/cv', 'public');
        }
        unset($validated['cv']);

        $candidate->update($validated);

        return redirect()->route('candidates.index')->with('success', 'Candidate updated successfully.');
    }

    public function destroy(Candidate $candidate)
    {
        $this->authorizeCompany($candidate);

        if ($candidate->cv_path) {
            Storage::disk('public')->delete($candidate->cv_path);
        }

        $candidate->delete();

        return redirect()->route('candidates.index')->with('success', 'Candidate deleted successfully.');
    }

    protected function authorizeCompany(Candidate $candidate): void
    {
        $candidate->loadMissing('recruitment');

        if ($candidate->recruitment && $candidate->recruitment->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
