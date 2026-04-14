<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $datePreset = $request->get('date_preset', 'this_month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate === '') {
            $startDate = null;
        }
        if ($endDate === '') {
            $endDate = null;
        }

        // On first load (no params), default to this month
        if (! $request->hasAny(['date_preset', 'start_date', 'end_date'])) {
            $datePreset = 'this_month';
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->toDateString();
        }

        $baseQuery = Expense::query();

        if ($startDate) {
            $baseQuery->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $baseQuery->where('date', '<=', $endDate);
        }

        $expenses = $baseQuery->orderBy('date', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'category' => $e->category,
                'amount' => (float) $e->amount,
                'date' => $e->date->toDateString(),
                'method' => $e->method,
                'status' => $e->status,
                'note' => $e->note ?? '',
                'supplier_id' => $e->supplier_id,
                'reference_number' => $e->reference_number,
            ]);

        $monthlySpend = collect(range(5, 0))->map(function (int $offset) {
            $month = now()->subMonths($offset);
            $total = Expense::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');

            return ['label' => $month->format('M'), 'amount' => (float) $total];
        })->values();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return Inertia::render('vendor/Expenses', [
            'expenses' => $expenses,
            'monthlySpend' => $monthlySpend,
            'inventoryStats' => [
                'total_spend' => 0, 'total_transactions' => 0,
                'average_per_transaction' => 0, 'largest_purchase' => 0,
            ],
            'expensesBySupplier' => [],
            'dailyBreakdown' => [],
            'suppliers' => $suppliers,
            'filters' => [
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
                'category_filter' => $request->get('category_filter', 'all'),
                'date_preset' => $datePreset,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'method' => 'required|string|max:100',
            'status' => 'required|in:Paid,Pending,Scheduled',
            'note' => 'nullable|string|max:500',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string|max:255',
        ]);

        // Add user_id and tenant_id if your table has these columns
        $validated['user_id'] = auth()->id();
        $validated['tenant_id'] = tenant()?->id;

        Expense::create($validated);

        return back()->with('success', 'Expense added successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'method' => 'required|string|max:100',
            'status' => 'required|in:Paid,Pending,Scheduled',
            'note' => 'nullable|string|max:500',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }

    /**
     * API endpoint to get inventory spend data for charts
     */
    public function getInventorySpendData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $expenses = Expense::where('category', 'inventory')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('supplier')
            ->get();

        return response()->json([
            'total_spend' => $expenses->sum('amount'),
            'total_transactions' => $expenses->count(),
            'by_supplier' => $expenses->groupBy('supplier_id')->map(function ($expenses) {
                $supplier = $expenses->first()->supplier;

                return [
                    'supplier_name' => $supplier?->name ?? 'Unknown',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })->values(),
            'daily' => $expenses->groupBy(fn ($e) => $e->date->format('Y-m-d'))->map(function ($expenses, $date) {
                return [
                    'date' => $date,
                    'total' => $expenses->sum('amount'),
                ];
            })->values(),
        ]);
    }
}
