<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\CostCenter;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Project::where('company_id', $companyId)
            ->with('costCenter');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $costCenters = CostCenter::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('projects.create', compact('costCenters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,ON_HOLD,COMPLETED,CANCELLED',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorizeCompany($project);
        $project->load('costCenter');

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorizeCompany($project);
        $companyId = auth()->user()->company_id;

        $costCenters = CostCenter::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('projects.edit', compact('project', 'costCenters'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeCompany($project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,ON_HOLD,COMPLETED,CANCELLED',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeCompany($project);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
