<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $categories = ItemCategory::where('company_id', $companyId)
            ->with('parent')
            ->orderBy('name')
            ->paginate(20);

        return view('item-categories.index', compact('categories'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $parentCategories = ItemCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('item-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        ItemCategory::create($validated);

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category created successfully.');
    }

    public function show(ItemCategory $itemCategory)
    {
        $this->authorizeCompany($itemCategory);
        $itemCategory->load('parent');

        return view('item-categories.show', compact('itemCategory'));
    }

    public function edit(ItemCategory $itemCategory)
    {
        $this->authorizeCompany($itemCategory);
        $companyId = auth()->user()->company_id;

        $parentCategories = ItemCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('id', '!=', $itemCategory->id)
            ->orderBy('name')
            ->get();

        return view('item-categories.edit', compact('itemCategory', 'parentCategories'));
    }

    public function update(Request $request, ItemCategory $itemCategory)
    {
        $this->authorizeCompany($itemCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id',
            'is_active' => 'boolean',
        ]);

        $itemCategory->update($validated);

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category updated successfully.');
    }

    public function destroy(ItemCategory $itemCategory)
    {
        $this->authorizeCompany($itemCategory);
        $itemCategory->delete();

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
