<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Employee $employee)
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'document_type' => 'nullable|string|max:255',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('documents/employees', 'local');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_name'],
            'file_path' => $path,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Employee $employee, EmployeeDocument $document)
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        if ($document->employee_id !== $employee->id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
