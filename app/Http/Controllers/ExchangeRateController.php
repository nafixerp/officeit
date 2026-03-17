<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Currency;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $exchangeRates = ExchangeRate::with(['fromCurrency', 'toCurrency'])
            ->orderBy('effective_date', 'desc')
            ->paginate(20);

        return view('exchange-rates.index', compact('exchangeRates'));
    }

    public function create()
    {
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('exchange-rates.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'rate' => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
            'is_auto' => 'boolean',
        ]);

        ExchangeRate::create($validated);

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate created successfully.');
    }

    public function show(ExchangeRate $exchangeRate)
    {
        $exchangeRate->load(['fromCurrency', 'toCurrency']);

        return view('exchange-rates.show', compact('exchangeRate'));
    }

    public function edit(ExchangeRate $exchangeRate)
    {
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('exchange-rates.edit', compact('exchangeRate', 'currencies'));
    }

    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $validated = $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'rate' => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
            'is_auto' => 'boolean',
        ]);

        $exchangeRate->update($validated);

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate updated successfully.');
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate deleted successfully.');
    }
}
