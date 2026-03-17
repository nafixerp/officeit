<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $currencies = Currency::orderBy('name')->paginate(20);

        return view('currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('currencies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'decimal_places' => 'nullable|integer|min:0|max:6',
            'is_base_currency' => 'boolean',
            'is_active' => 'boolean',
        ]);

        Currency::create($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    public function show(Currency $currency)
    {
        return view('currencies.show', compact('currency'));
    }

    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'decimal_places' => 'nullable|integer|min:0|max:6',
            'is_base_currency' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $currency->update($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully.');
    }
}
