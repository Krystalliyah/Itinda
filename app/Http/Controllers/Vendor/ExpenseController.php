<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::orderBy('date', 'desc')->get()->map(fn($e) => [
            'id'       => $e->id,
            'title'    => $e->title,
            'category' => $e->category,
            'amount'   => $e->amount,
            'date'     => $e->date->toDateString(),
            'method'   => $e->method,
            'status'   => $e->status,
            'note'     => $e->note ?? '',
        ]);

        // Monthly spend — last 6 months
        $monthlySpend = collect(range(5, 0))->map(function (int $offset) {
            $month = now()->subMonths($offset);
            $total = Expense::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');

            return [
                'label'  => $month->format('M'),
                'amount' => (float) $total,
            ];
        })->values();

        return Inertia::render('vendor/Expenses', [
            'expenses'     => $expenses,
            'monthlySpend' => $monthlySpend,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:0',
            'date'     => 'required|date',
            'method'   => 'required|string|max:100',
            'status'   => 'required|in:Paid,Pending,Scheduled',
            'note'     => 'nullable|string|max:500',
        ]);

        Expense::create($validated);

        return back()->with('success', 'Expense added.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:0',
            'date'     => 'required|date',
            'method'   => 'required|string|max:100',
            'status'   => 'required|in:Paid,Pending,Scheduled',
            'note'     => 'nullable|string|max:500',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
